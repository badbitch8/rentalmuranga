<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Property;
use App\Models\Booking;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    /**
     * Get all reviews for a property
     */
    public function getPropertyReviews(Request $request, $propertyId)
    {
        $property = Property::find($propertyId);

        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found'
            ], 404);
        }

        $query = Review::with(['user', 'landlordResponse'])
            ->where('property_id', $propertyId)
            ->where('status', 'approved');

        // Filter by rating
        if ($request->has('min_rating')) {
            $query->where('rating', '>=', $request->min_rating);
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'recent');
        switch ($sortBy) {
            case 'highest':
                $query->orderBy('rating', 'desc');
                break;
            case 'lowest':
                $query->orderBy('rating', 'asc');
                break;
            case 'helpful':
                $query->orderBy('helpful_count', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $reviews = $query->paginate(10);

        // Calculate review statistics
        $stats = [
            'average_rating' => $property->reviews()->where('status', 'approved')->avg('rating'),
            'total_reviews' => $property->reviews()->where('status', 'approved')->count(),
            'rating_distribution' => [
                '5_star' => $property->reviews()->where('status', 'approved')->where('rating', 5)->count(),
                '4_star' => $property->reviews()->where('status', 'approved')->where('rating', 4)->count(),
                '3_star' => $property->reviews()->where('status', 'approved')->where('rating', 3)->count(),
                '2_star' => $property->reviews()->where('status', 'approved')->where('rating', 2)->count(),
                '1_star' => $property->reviews()->where('status', 'approved')->where('rating', 1)->count(),
            ],
            'category_averages' => [
                'cleanliness' => $property->reviews()->where('status', 'approved')->avg('cleanliness_rating'),
                'communication' => $property->reviews()->where('status', 'approved')->avg('communication_rating'),
                'location' => $property->reviews()->where('status', 'approved')->avg('location_rating'),
                'value' => $property->reviews()->where('status', 'approved')->avg('value_rating'),
                'amenities' => $property->reviews()->where('status', 'approved')->avg('amenities_rating'),
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'reviews' => $reviews,
                'statistics' => $stats
            ]
        ]);
    }

    /**
     * Get reviews by a specific user
     */
    public function getUserReviews(Request $request)
    {
        $user = $request->user();
        
        $reviews = Review::with(['property', 'landlordResponse'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    /**
     * Get reviews for landlord's properties
     */
    public function getLandlordReviews(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'landlord') {
            return response()->json([
                'success' => false,
                'message' => 'Only landlords can access this endpoint'
            ], 403);
        }

        $query = Review::with(['property', 'user'])
            ->whereHas('property', function ($q) use ($user) {
                $q->where('landlord_id', $user->id);
            });

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by property
        if ($request->has('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(15);

        // Calculate overall statistics
        $stats = [
            'total_reviews' => Review::whereHas('property', function ($q) use ($user) {
                $q->where('landlord_id', $user->id);
            })->where('status', 'approved')->count(),
            'average_rating' => Review::whereHas('property', function ($q) use ($user) {
                $q->where('landlord_id', $user->id);
            })->where('status', 'approved')->avg('rating'),
            'pending_reviews' => Review::whereHas('property', function ($q) use ($user) {
                $q->where('landlord_id', $user->id);
            })->where('status', 'pending')->count(),
            'needs_response' => Review::whereHas('property', function ($q) use ($user) {
                $q->where('landlord_id', $user->id);
            })->where('status', 'approved')->whereNull('landlord_response_id')->count()
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'reviews' => $reviews,
                'statistics' => $stats
            ]
        ]);
    }

    /**
     * Create a new review
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'booking_id' => 'required|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'cleanliness_rating' => 'required|integer|min:1|max:5',
            'communication_rating' => 'required|integer|min:1|max:5',
            'location_rating' => 'required|integer|min:1|max:5',
            'value_rating' => 'required|integer|min:1|max:5',
            'amenities_rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:20|max:1000',
            'pros' => 'nullable|string|max:500',
            'cons' => 'nullable|string|max:500'
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
                'message' => 'You can only review properties you have booked'
            ], 403);
        }

        // Verify booking is completed
        if ($booking->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'You can only review completed bookings'
            ], 400);
        }

        // Check if user already reviewed this booking
        $existingReview = Review::where('booking_id', $booking->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this booking'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $review = Review::create([
                'property_id' => $request->property_id,
                'booking_id' => $request->booking_id,
                'user_id' => $user->id,
                'rating' => $request->rating,
                'cleanliness_rating' => $request->cleanliness_rating,
                'communication_rating' => $request->communication_rating,
                'location_rating' => $request->location_rating,
                'value_rating' => $request->value_rating,
                'amenities_rating' => $request->amenities_rating,
                'comment' => $request->comment,
                'pros' => $request->pros,
                'cons' => $request->cons,
                'status' => 'pending' // Reviews need approval
            ]);

            // Update property average rating
            $property = Property::find($request->property_id);
            $property->updateAverageRating();

            // Notify landlord
            Notification::create([
                'user_id' => $booking->property->landlord_id,
                'type' => 'new_review',
                'title' => 'New Review Received',
                'message' => "{$user->name} left a {$request->rating}-star review for {$property->name}",
                'data' => json_encode([
                    'review_id' => $review->id,
                    'property_id' => $property->id,
                    'rating' => $request->rating
                ])
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully. It will be visible after approval.',
                'data' => $review->load('user')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a review
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'sometimes|integer|min:1|max:5',
            'cleanliness_rating' => 'sometimes|integer|min:1|max:5',
            'communication_rating' => 'sometimes|integer|min:1|max:5',
            'location_rating' => 'sometimes|integer|min:1|max:5',
            'value_rating' => 'sometimes|integer|min:1|max:5',
            'amenities_rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'sometimes|string|min:20|max:1000',
            'pros' => 'nullable|string|max:500',
            'cons' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }

        // Verify ownership
        if ($review->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Can only edit within 7 days
        if ($review->created_at->diffInDays(now()) > 7) {
            return response()->json([
                'success' => false,
                'message' => 'Reviews can only be edited within 7 days of posting'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $review->update($request->only([
                'rating',
                'cleanliness_rating',
                'communication_rating',
                'location_rating',
                'value_rating',
                'amenities_rating',
                'comment',
                'pros',
                'cons'
            ]));

            // Update property average rating
            $review->property->updateAverageRating();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Review updated successfully',
                'data' => $review->load('user')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a review
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }

        // Only review owner or admin can delete
        if ($review->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $propertyId = $review->property_id;
            $review->delete();

            // Update property average rating
            $property = Property::find($propertyId);
            $property->updateAverageRating();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Landlord responds to a review
     */
    public function respondToReview(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'response' => 'required|string|min:10|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $review = Review::with('property')->find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }

        // Verify landlord owns the property
        if ($review->property->landlord_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only respond to reviews for your properties'
            ], 403);
        }

        // Check if already responded
        if ($review->landlord_response_id) {
            return response()->json([
                'success' => false,
                'message' => 'You have already responded to this review'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Create landlord response as a separate review entry
            $response = Review::create([
                'property_id' => $review->property_id,
                'user_id' => $user->id,
                'comment' => $request->response,
                'status' => 'approved',
                'is_landlord_response' => true
            ]);

            // Link response to original review
            $review->update([
                'landlord_response_id' => $response->id
            ]);

            // Notify the reviewer
            Notification::create([
                'user_id' => $review->user_id,
                'type' => 'review_response',
                'title' => 'Landlord Responded to Your Review',
                'message' => "The landlord responded to your review for {$review->property->name}",
                'data' => json_encode([
                    'review_id' => $review->id,
                    'property_id' => $review->property_id
                ])
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Response posted successfully',
                'data' => $review->load('landlordResponse')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to post response',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark review as helpful
     */
    public function markHelpful(Request $request, $id)
    {
        $user = $request->user();
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }

        // Check if user already marked as helpful
        $helpfulVotes = json_decode($review->helpful_votes, true) ?? [];
        
        if (in_array($user->id, $helpfulVotes)) {
            return response()->json([
                'success' => false,
                'message' => 'You have already marked this review as helpful'
            ], 400);
        }

        $helpfulVotes[] = $user->id;
        $review->update([
            'helpful_votes' => json_encode($helpfulVotes),
            'helpful_count' => count($helpfulVotes)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review marked as helpful',
            'data' => [
                'helpful_count' => $review->helpful_count
            ]
        ]);
    }

    /**
     * Admin: Approve a review
     */
    public function approveReview(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can approve reviews'
            ], 403);
        }

        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }

        $review->update(['status' => 'approved']);

        // Update property rating
        $review->property->updateAverageRating();

        return response()->json([
            'success' => true,
            'message' => 'Review approved successfully',
            'data' => $review
        ]);
    }

    /**
     * Admin: Reject a review
     */
    public function rejectReview(Request $request, $id)
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

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can reject reviews'
            ], 403);
        }

        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }

        $review->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason
        ]);

        // Notify user
        Notification::create([
            'user_id' => $review->user_id,
            'type' => 'review_rejected',
            'title' => 'Review Not Approved',
            'message' => "Your review was not approved. Reason: {$request->reason}",
            'data' => json_encode(['review_id' => $review->id])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review rejected',
            'data' => $review
        ]);
    }
}

// Made with Bob
