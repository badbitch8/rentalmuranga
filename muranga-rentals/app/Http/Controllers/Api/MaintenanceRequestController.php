<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Booking;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MaintenanceRequestController extends Controller
{
    /**
     * Get all maintenance requests
     * Tenants see their requests
     * Landlords see requests for their properties
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = MaintenanceRequest::with(['property', 'tenant', 'booking']);

        if ($user->role === 'landlord') {
            // Get requests for landlord's properties
            $query->whereHas('property', function ($q) use ($user) {
                $q->where('landlord_id', $user->id);
            });
        } elseif ($user->role === 'tenant') {
            // Get tenant's requests
            $query->where('tenant_id', $user->id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by property
        if ($request->has('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'recent');
        switch ($sortBy) {
            case 'priority':
                $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')");
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $requests = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $requests
        ]);
    }

    /**
     * Get a single maintenance request
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $maintenanceRequest = MaintenanceRequest::with(['property', 'tenant', 'booking'])->find($id);

        if (!$maintenanceRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Maintenance request not found'
            ], 404);
        }

        // Check authorization
        if ($user->role === 'landlord') {
            if ($maintenanceRequest->property->landlord_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
        } elseif ($maintenanceRequest->tenant_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $maintenanceRequest
        ]);
    }

    /**
     * Create a new maintenance request
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'booking_id' => 'required|exists:bookings,id',
            'category' => 'required|in:plumbing,electrical,appliance,structural,pest_control,hvac,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'title' => 'required|string|max:200',
            'description' => 'required|string|min:20|max:1000',
            'preferred_date' => 'nullable|date|after:today',
            'preferred_time' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120'
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
                'message' => 'You can only create maintenance requests for your bookings'
            ], 403);
        }

        // Verify booking is active
        if (!in_array($booking->status, ['confirmed', 'active'])) {
            return response()->json([
                'success' => false,
                'message' => 'Maintenance requests can only be created for active bookings'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Generate request number
            $requestNumber = 'MR-' . date('Ymd') . '-' . str_pad(MaintenanceRequest::count() + 1, 4, '0', STR_PAD_LEFT);

            $maintenanceRequest = MaintenanceRequest::create([
                'property_id' => $request->property_id,
                'booking_id' => $request->booking_id,
                'tenant_id' => $user->id,
                'request_number' => $requestNumber,
                'category' => $request->category,
                'priority' => $request->priority,
                'title' => $request->title,
                'description' => $request->description,
                'preferred_date' => $request->preferred_date,
                'preferred_time' => $request->preferred_time,
                'status' => 'pending'
            ]);

            // Handle image uploads
            if ($request->hasFile('images')) {
                $images = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('maintenance-requests', 'public');
                    $images[] = $path;
                }
                $maintenanceRequest->update(['images' => json_encode($images)]);
            }

            // Notify landlord
            Notification::create([
                'user_id' => $booking->property->landlord_id,
                'type' => 'maintenance_request',
                'title' => 'New Maintenance Request',
                'message' => "New {$request->priority} priority maintenance request for {$booking->property->name}: {$request->title}",
                'data' => json_encode([
                    'maintenance_request_id' => $maintenanceRequest->id,
                    'property_id' => $booking->property->id,
                    'priority' => $request->priority
                ])
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Maintenance request created successfully',
                'data' => $maintenanceRequest->load(['property', 'tenant'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create maintenance request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update maintenance request status (landlord only)
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'notes' => 'nullable|string|max:500',
            'estimated_cost' => 'nullable|numeric|min:0',
            'scheduled_date' => 'nullable|date',
            'contractor_name' => 'nullable|string|max:100',
            'contractor_phone' => 'nullable|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $maintenanceRequest = MaintenanceRequest::with('property')->find($id);

        if (!$maintenanceRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Maintenance request not found'
            ], 404);
        }

        // Verify landlord owns the property
        if ($maintenanceRequest->property->landlord_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only the property landlord can update maintenance requests'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $updateData = ['status' => $request->status];

            if ($request->has('notes')) {
                $updateData['landlord_notes'] = $request->notes;
            }
            if ($request->has('estimated_cost')) {
                $updateData['estimated_cost'] = $request->estimated_cost;
            }
            if ($request->has('scheduled_date')) {
                $updateData['scheduled_date'] = $request->scheduled_date;
            }
            if ($request->has('contractor_name')) {
                $updateData['contractor_name'] = $request->contractor_name;
            }
            if ($request->has('contractor_phone')) {
                $updateData['contractor_phone'] = $request->contractor_phone;
            }

            // Set completion date if status is completed
            if ($request->status === 'completed' && !$maintenanceRequest->completed_at) {
                $updateData['completed_at'] = now();
            }

            $maintenanceRequest->update($updateData);

            // Notify tenant
            $statusMessages = [
                'in_progress' => 'Your maintenance request is now in progress',
                'completed' => 'Your maintenance request has been completed',
                'cancelled' => 'Your maintenance request has been cancelled'
            ];

            if (isset($statusMessages[$request->status])) {
                Notification::create([
                    'user_id' => $maintenanceRequest->tenant_id,
                    'type' => 'maintenance_update',
                    'title' => 'Maintenance Request Updated',
                    'message' => $statusMessages[$request->status] . ": {$maintenanceRequest->title}",
                    'data' => json_encode([
                        'maintenance_request_id' => $maintenanceRequest->id,
                        'status' => $request->status
                    ])
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Maintenance request updated successfully',
                'data' => $maintenanceRequest->fresh(['property', 'tenant'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update maintenance request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add actual cost after completion
     */
    public function addActualCost(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'actual_cost' => 'required|numeric|min:0',
            'cost_breakdown' => 'nullable|string|max:1000',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,pdf|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $maintenanceRequest = MaintenanceRequest::with('property')->find($id);

        if (!$maintenanceRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Maintenance request not found'
            ], 404);
        }

        // Verify landlord owns the property
        if ($maintenanceRequest->property->landlord_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($maintenanceRequest->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Can only add costs to completed requests'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $updateData = [
                'actual_cost' => $request->actual_cost,
                'cost_breakdown' => $request->cost_breakdown
            ];

            // Handle receipt upload
            if ($request->hasFile('receipt_image')) {
                $path = $request->file('receipt_image')->store('maintenance-receipts', 'public');
                $updateData['receipt_image'] = $path;
            }

            $maintenanceRequest->update($updateData);

            // Notify tenant about cost
            Notification::create([
                'user_id' => $maintenanceRequest->tenant_id,
                'type' => 'maintenance_cost',
                'title' => 'Maintenance Cost Added',
                'message' => "Cost of KES {$request->actual_cost} added for: {$maintenanceRequest->title}",
                'data' => json_encode([
                    'maintenance_request_id' => $maintenanceRequest->id,
                    'actual_cost' => $request->actual_cost
                ])
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cost added successfully',
                'data' => $maintenanceRequest->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add cost',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get maintenance statistics
     */
    public function getStatistics(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'tenant') {
            $query = MaintenanceRequest::where('tenant_id', $user->id);
        } elseif ($user->role === 'landlord') {
            $query = MaintenanceRequest::whereHas('property', function ($q) use ($user) {
                $q->where('landlord_id', $user->id);
            });
        } else {
            $query = MaintenanceRequest::query();
        }

        $stats = [
            'total_requests' => $query->count(),
            'pending' => $query->where('status', 'pending')->count(),
            'in_progress' => $query->where('status', 'in_progress')->count(),
            'completed' => $query->where('status', 'completed')->count(),
            'cancelled' => $query->where('status', 'cancelled')->count(),
            'by_priority' => [
                'urgent' => $query->where('priority', 'urgent')->count(),
                'high' => $query->where('priority', 'high')->count(),
                'medium' => $query->where('priority', 'medium')->count(),
                'low' => $query->where('priority', 'low')->count()
            ],
            'by_category' => $query->select('category', DB::raw('count(*) as count'))
                ->groupBy('category')
                ->get()
                ->pluck('count', 'category'),
            'average_completion_time' => $query->where('status', 'completed')
                ->whereNotNull('completed_at')
                ->get()
                ->avg(function ($request) {
                    return $request->created_at->diffInDays($request->completed_at);
                }),
            'total_cost' => $query->where('status', 'completed')->sum('actual_cost')
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Cancel maintenance request (tenant only)
     */
    public function cancel(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $maintenanceRequest = MaintenanceRequest::find($id);

        if (!$maintenanceRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Maintenance request not found'
            ], 404);
        }

        // Verify ownership
        if ($maintenanceRequest->tenant_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Can only cancel pending or in_progress requests
        if (!in_array($maintenanceRequest->status, ['pending', 'in_progress'])) {
            return response()->json([
                'success' => false,
                'message' => 'Can only cancel pending or in-progress requests'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $maintenanceRequest->update([
                'status' => 'cancelled',
                'cancellation_reason' => $request->reason
            ]);

            // Notify landlord
            Notification::create([
                'user_id' => $maintenanceRequest->property->landlord_id,
                'type' => 'maintenance_cancelled',
                'title' => 'Maintenance Request Cancelled',
                'message' => "Tenant cancelled maintenance request: {$maintenanceRequest->title}",
                'data' => json_encode([
                    'maintenance_request_id' => $maintenanceRequest->id,
                    'reason' => $request->reason
                ])
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Maintenance request cancelled successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel request',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

// Made with Bob
