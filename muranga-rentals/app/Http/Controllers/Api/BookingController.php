<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * Get all bookings for authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Booking::with(['property.landlord', 'property.primaryImage', 'tenant', 'payments']);

        // Filter based on user role
        if ($user->isTenant()) {
            $query->where('tenant_id', $user->id);
        } elseif ($user->isLandlord()) {
            $query->whereHas('property', function($q) use ($user) {
                $q->where('landlord_id', $user->id);
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $bookings
        ], 200);
    }

    /**
     * Get single booking details
     */
    public function show(Request $request, $id)
    {
        $booking = Booking::with([
            'property.landlord',
            'property.images',
            'tenant',
            'payments'
        ])->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        $user = $request->user();

        // Check authorization
        if ($booking->tenant_id !== $user->id && 
            $booking->property->landlord_id !== $user->id && 
            !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this booking'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'booking' => $booking,
                'total_paid' => $booking->totalPaid(),
                'remaining_balance' => $booking->remainingBalance(),
                'is_fully_paid' => $booking->isFullyPaid(),
                'duration_months' => $booking->getDurationInMonths(),
            ]
        ], 200);
    }

    /**
     * Create new booking
     */
    public function store(Request $request)
    {
        if (!$request->user()->isTenant()) {
            return response()->json([
                'success' => false,
                'message' => 'Only tenants can create bookings'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'property_id' => ['required', 'exists:properties,id'],
            'start_date' => ['required', 'date', 'after:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $property = Property::find($request->property_id);

        if (!$property->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'Property is not available for booking'
            ], 400);
        }

        // Check for overlapping bookings
        $overlapping = Booking::where('property_id', $property->id)
            ->where('status', '!=', 'cancelled')
            ->where(function($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                    ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                    ->orWhere(function($q) use ($request) {
                        $q->where('start_date', '<=', $request->start_date)
                          ->where('end_date', '>=', $request->end_date);
                    });
            })
            ->exists();

        if ($overlapping) {
            return response()->json([
                'success' => false,
                'message' => 'Property is already booked for the selected dates'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Calculate total amount
            $startDate = \Carbon\Carbon::parse($request->start_date);
            $endDate = \Carbon\Carbon::parse($request->end_date);
            $months = $startDate->diffInMonths($endDate);
            
            $totalRent = $property->rent_amount * max(1, $months);
            $totalAmount = $totalRent + $property->deposit_amount;

            $booking = Booking::create([
                'property_id' => $property->id,
                'tenant_id' => $request->user()->id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'monthly_rent' => $property->rent_amount,
                'deposit_amount' => $property->deposit_amount,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            // Create notification for landlord
            Notification::create([
                'user_id' => $property->landlord_id,
                'type' => 'new_booking',
                'title' => 'New Booking Request',
                'message' => "New booking request for {$property->title} from {$request->user()->name}",
                'action_url' => "/bookings/{$booking->id}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => $booking->load(['property', 'tenant'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update booking
     */
    public function update(Request $request, $id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        // Only tenant can update their own booking
        if ($booking->tenant_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this booking'
            ], 403);
        }

        // Can only update pending bookings
        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Can only update pending bookings'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => ['sometimes', 'date', 'after:today'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $booking->update($request->only(['start_date', 'end_date', 'notes']));

            return response()->json([
                'success' => true,
                'message' => 'Booking updated successfully',
                'data' => $booking
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm booking (landlord only)
     */
    public function confirm(Request $request, $id)
    {
        $booking = Booking::with('property')->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        // Only landlord can confirm
        if ($booking->property->landlord_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to confirm this booking'
            ], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Can only confirm pending bookings'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $booking->update(['status' => 'confirmed']);

            // Create notification for tenant
            Notification::create([
                'user_id' => $booking->tenant_id,
                'type' => 'booking_confirmed',
                'title' => 'Booking Confirmed',
                'message' => "Your booking for {$booking->property->title} has been confirmed",
                'action_url' => "/bookings/{$booking->id}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Booking confirmed successfully',
                'data' => $booking
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel booking
     */
    public function cancel(Request $request, $id)
    {
        $booking = Booking::with('property')->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        $user = $request->user();

        // Check authorization
        if ($booking->tenant_id !== $user->id && 
            $booking->property->landlord_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to cancel this booking'
            ], 403);
        }

        if ($booking->status === 'cancelled' || $booking->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel this booking'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'cancellation_reason' => ['required', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $booking->update([
                'status' => 'cancelled',
                'cancellation_reason' => $request->cancellation_reason,
                'cancelled_by' => $user->id,
                'cancelled_at' => now(),
            ]);

            // Notify the other party
            $notifyUserId = ($user->id === $booking->tenant_id) 
                ? $booking->property->landlord_id 
                : $booking->tenant_id;

            Notification::create([
                'user_id' => $notifyUserId,
                'type' => 'booking_cancelled',
                'title' => 'Booking Cancelled',
                'message' => "Booking for {$booking->property->title} has been cancelled",
                'action_url' => "/bookings/{$booking->id}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully',
                'data' => $booking
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sign contract (digital signature)
     */
    public function sign(Request $request, $id)
    {
        $booking = Booking::with('property')->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        if ($booking->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Can only sign confirmed bookings'
            ], 400);
        }

        $user = $request->user();
        $isTenant = $booking->tenant_id === $user->id;
        $isLandlord = $booking->property->landlord_id === $user->id;

        if (!$isTenant && !$isLandlord) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to sign this contract'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'signature' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            if ($isTenant) {
                $booking->tenant_signature = $request->signature;
            } else {
                $booking->landlord_signature = $request->signature;
            }

            // If both parties have signed, mark as active
            if ($booking->tenant_signature && $booking->landlord_signature) {
                $booking->status = 'active';
                $booking->signed_at = now();
                $booking->move_in_date = $booking->start_date;
            }

            $booking->save();

            // Notify if contract is fully signed
            if ($booking->status === 'active') {
                $notifyUserId = $isTenant ? $booking->property->landlord_id : $booking->tenant_id;
                
                Notification::create([
                    'user_id' => $notifyUserId,
                    'type' => 'contract_signed',
                    'title' => 'Contract Fully Signed',
                    'message' => "Contract for {$booking->property->title} is now active",
                    'action_url' => "/bookings/{$booking->id}",
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $booking->status === 'active' 
                    ? 'Contract fully signed and activated' 
                    : 'Signature recorded. Waiting for other party to sign',
                'data' => $booking
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to sign contract',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

// Made with Bob
