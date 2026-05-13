<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Get all payments for the authenticated user
     * Landlords see payments for their properties
     * Tenants see their own payments
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Payment::with(['booking.property', 'user']);

        if ($user->role === 'landlord') {
            // Get payments for landlord's properties
            $query->whereHas('booking.property', function ($q) use ($user) {
                $q->where('landlord_id', $user->id);
            });
        } else {
            // Get tenant's payments
            $query->where('user_id', $user->id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment method
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * Get a single payment
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $payment = Payment::with(['booking.property', 'user'])->find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        // Check authorization
        if ($user->role === 'landlord') {
            if ($payment->booking->property->landlord_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
        } elseif ($payment->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }

    /**
     * Initiate M-Pesa STK Push payment
     */
    public function initiateMpesaPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'phone_number' => 'required|regex:/^254[0-9]{9}$/',
            'amount' => 'required|numeric|min:1',
            'payment_type' => 'required|in:deposit,rent,full_payment'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $booking = Booking::with('property')->find($request->booking_id);

        // Verify booking belongs to user
        if ($booking->tenant_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Verify booking is confirmed
        if ($booking->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Booking must be confirmed before payment'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Create payment record
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'amount' => $request->amount,
                'payment_method' => 'mpesa',
                'payment_type' => $request->payment_type,
                'status' => 'pending',
                'transaction_id' => 'MPX' . time() . rand(1000, 9999),
                'phone_number' => $request->phone_number,
                'metadata' => json_encode([
                    'property_id' => $booking->property_id,
                    'property_name' => $booking->property->name,
                    'initiated_at' => now()
                ])
            ]);

            // In production, integrate with M-Pesa Daraja API here
            // For now, we'll simulate the STK push
            $stkPushResponse = $this->simulateMpesaStkPush($payment);

            if ($stkPushResponse['success']) {
                $payment->update([
                    'mpesa_checkout_request_id' => $stkPushResponse['checkout_request_id']
                ]);

                // Create notification
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'payment_initiated',
                    'title' => 'Payment Initiated',
                    'message' => "M-Pesa payment of KES {$request->amount} initiated. Check your phone.",
                    'data' => json_encode(['payment_id' => $payment->id])
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'STK push sent to your phone. Please enter your M-Pesa PIN.',
                    'data' => [
                        'payment_id' => $payment->id,
                        'checkout_request_id' => $stkPushResponse['checkout_request_id'],
                        'amount' => $payment->amount,
                        'phone_number' => $payment->phone_number
                    ]
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to initiate M-Pesa payment',
                    'error' => $stkPushResponse['error']
                ], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('M-Pesa payment initiation failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Payment initiation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * M-Pesa callback handler
     * This endpoint receives callbacks from M-Pesa
     */
    public function mpesaCallback(Request $request)
    {
        Log::info('M-Pesa Callback Received', $request->all());

        $callbackData = $request->all();
        
        // Extract callback data (structure depends on M-Pesa API)
        $resultCode = $callbackData['Body']['stkCallback']['ResultCode'] ?? null;
        $checkoutRequestId = $callbackData['Body']['stkCallback']['CheckoutRequestID'] ?? null;

        if (!$checkoutRequestId) {
            return response()->json(['success' => false, 'message' => 'Invalid callback data']);
        }

        // Find payment by checkout request ID
        $payment = Payment::where('mpesa_checkout_request_id', $checkoutRequestId)->first();

        if (!$payment) {
            Log::error('Payment not found for checkout request: ' . $checkoutRequestId);
            return response()->json(['success' => false, 'message' => 'Payment not found']);
        }

        DB::beginTransaction();
        try {
            if ($resultCode == 0) {
                // Payment successful
                $callbackMetadata = $callbackData['Body']['stkCallback']['CallbackMetadata']['Item'] ?? [];
                $mpesaReceiptNumber = null;
                $transactionDate = null;

                foreach ($callbackMetadata as $item) {
                    if ($item['Name'] === 'MpesaReceiptNumber') {
                        $mpesaReceiptNumber = $item['Value'];
                    }
                    if ($item['Name'] === 'TransactionDate') {
                        $transactionDate = $item['Value'];
                    }
                }

                $payment->update([
                    'status' => 'completed',
                    'mpesa_receipt_number' => $mpesaReceiptNumber,
                    'transaction_date' => $transactionDate ? now() : null,
                    'metadata' => json_encode(array_merge(
                        json_decode($payment->metadata, true),
                        ['callback_data' => $callbackData]
                    ))
                ]);

                // Update booking payment status
                $booking = $payment->booking;
                if ($payment->payment_type === 'full_payment') {
                    $booking->update(['payment_status' => 'paid']);
                } elseif ($payment->payment_type === 'deposit') {
                    $booking->update(['payment_status' => 'deposit_paid']);
                }

                // Notify tenant
                Notification::create([
                    'user_id' => $payment->user_id,
                    'type' => 'payment_success',
                    'title' => 'Payment Successful',
                    'message' => "Your payment of KES {$payment->amount} was successful. Receipt: {$mpesaReceiptNumber}",
                    'data' => json_encode(['payment_id' => $payment->id])
                ]);

                // Notify landlord
                Notification::create([
                    'user_id' => $booking->property->landlord_id,
                    'type' => 'payment_received',
                    'title' => 'Payment Received',
                    'message' => "Payment of KES {$payment->amount} received for {$booking->property->name}",
                    'data' => json_encode(['payment_id' => $payment->id, 'booking_id' => $booking->id])
                ]);

            } else {
                // Payment failed
                $payment->update([
                    'status' => 'failed',
                    'metadata' => json_encode(array_merge(
                        json_decode($payment->metadata, true),
                        ['callback_data' => $callbackData, 'error_code' => $resultCode]
                    ))
                ]);

                // Notify tenant
                Notification::create([
                    'user_id' => $payment->user_id,
                    'type' => 'payment_failed',
                    'title' => 'Payment Failed',
                    'message' => "Your payment of KES {$payment->amount} failed. Please try again.",
                    'data' => json_encode(['payment_id' => $payment->id])
                ]);
            }

            DB::commit();

            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' => 'Success'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('M-Pesa callback processing failed: ' . $e->getMessage());
            
            return response()->json([
                'ResultCode' => 1,
                'ResultDesc' => 'Failed'
            ]);
        }
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(Request $request, $id)
    {
        $user = $request->user();
        $payment = Payment::with(['booking.property'])->find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        // Check authorization
        if ($payment->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'payment_id' => $payment->id,
                'status' => $payment->status,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'transaction_id' => $payment->transaction_id,
                'mpesa_receipt_number' => $payment->mpesa_receipt_number,
                'created_at' => $payment->created_at,
                'updated_at' => $payment->updated_at
            ]
        ]);
    }

    /**
     * Get payment statistics for landlord
     */
    public function getPaymentStats(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'landlord' && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $query = Payment::whereHas('booking.property', function ($q) use ($user) {
            if ($user->role === 'landlord') {
                $q->where('landlord_id', $user->id);
            }
        });

        $stats = [
            'total_payments' => $query->where('status', 'completed')->count(),
            'total_revenue' => $query->where('status', 'completed')->sum('amount'),
            'pending_payments' => $query->where('status', 'pending')->count(),
            'failed_payments' => $query->where('status', 'failed')->count(),
            'this_month_revenue' => $query->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
            'last_month_revenue' => $query->where('status', 'completed')
                ->whereMonth('created_at', now()->subMonth()->month)
                ->sum('amount'),
            'payment_methods' => $query->where('status', 'completed')
                ->select('payment_method', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
                ->groupBy('payment_method')
                ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Generate payment receipt
     */
    public function generateReceipt(Request $request, $id)
    {
        $user = $request->user();
        $payment = Payment::with(['booking.property', 'user'])->find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        // Check authorization
        if ($payment->user_id !== $user->id && 
            $payment->booking->property->landlord_id !== $user->id && 
            $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($payment->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Receipt can only be generated for completed payments'
            ], 400);
        }

        $receipt = [
            'receipt_number' => 'RCP-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
            'date' => $payment->transaction_date ?? $payment->updated_at,
            'payment_details' => [
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'transaction_id' => $payment->transaction_id,
                'mpesa_receipt' => $payment->mpesa_receipt_number,
                'payment_type' => $payment->payment_type
            ],
            'property_details' => [
                'name' => $payment->booking->property->name,
                'location' => $payment->booking->property->location,
                'type' => $payment->booking->property->type
            ],
            'tenant_details' => [
                'name' => $payment->user->name,
                'email' => $payment->user->email,
                'phone' => $payment->user->phone_number
            ],
            'landlord_details' => [
                'name' => $payment->booking->property->landlord->name,
                'phone' => $payment->booking->property->landlord->phone_number
            ],
            'booking_details' => [
                'check_in' => $payment->booking->check_in_date,
                'check_out' => $payment->booking->check_out_date,
                'duration' => $payment->booking->duration_months . ' months'
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $receipt
        ]);
    }

    /**
     * Simulate M-Pesa STK Push (for development)
     * In production, replace with actual Daraja API integration
     */
    private function simulateMpesaStkPush($payment)
    {
        // This is a simulation. In production, integrate with M-Pesa Daraja API
        // Example: https://developer.safaricom.co.ke/APIs/MpesaExpressSimulate
        
        return [
            'success' => true,
            'checkout_request_id' => 'ws_CO_' . time() . rand(100000, 999999),
            'merchant_request_id' => 'MR_' . time() . rand(100000, 999999),
            'response_code' => '0',
            'response_description' => 'Success. Request accepted for processing',
            'customer_message' => 'Success. Request accepted for processing'
        ];

        // Production implementation would look like:
        /*
        $mpesa = new \Safaricom\Mpesa\Mpesa();
        $response = $mpesa->STKPush([
            'BusinessShortCode' => config('mpesa.shortcode'),
            'Password' => base64_encode(config('mpesa.shortcode') . config('mpesa.passkey') . date('YmdHis')),
            'Timestamp' => date('YmdHis'),
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $payment->amount,
            'PartyA' => $payment->phone_number,
            'PartyB' => config('mpesa.shortcode'),
            'PhoneNumber' => $payment->phone_number,
            'CallBackURL' => route('api.mpesa.callback'),
            'AccountReference' => $payment->transaction_id,
            'TransactionDesc' => 'Rent Payment'
        ]);
        
        return $response;
        */
    }
}

// Made with Bob
