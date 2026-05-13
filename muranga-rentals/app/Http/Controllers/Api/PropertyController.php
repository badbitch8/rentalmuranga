<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\Favorite;
use App\Models\PropertyView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PropertyController extends Controller
{
    /**
     * Get all properties (public)
     */
    public function index(Request $request)
    {
        $query = Property::with(['landlord', 'primaryImage', 'images'])
            ->approved()
            ->available();

        // Apply filters
        if ($request->has('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        if ($request->has('min_price')) {
            $query->where('rent_amount', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('rent_amount', '<=', $request->max_price);
        }

        if ($request->has('bedrooms')) {
            $query->where('bedrooms', '>=', $request->bedrooms);
        }

        if ($request->has('max_distance')) {
            $query->where('distance_to_mut', '<=', $request->max_distance);
        }

        if ($request->has('furnishing_status')) {
            $query->where('furnishing_status', $request->furnishing_status);
        }

        if ($request->has('pet_friendly')) {
            $query->where('pet_friendly', $request->boolean('pet_friendly'));
        }

        if ($request->has('parking_available')) {
            $query->where('parking_available', $request->boolean('parking_available'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if ($sortBy === 'price') {
            $query->orderBy('rent_amount', $sortOrder);
        } elseif ($sortBy === 'distance') {
            $query->orderBy('distance_to_mut', 'asc');
        } elseif ($sortBy === 'popular') {
            $query->orderBy('views_count', 'desc');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $properties = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $properties
        ], 200);
    }

    /**
     * Get single property details
     */
    public function show($id, Request $request)
    {
        $property = Property::with([
            'landlord',
            'images',
            'reviews.reviewer',
            'reviews' => function($query) {
                $query->verified()->orderBy('created_at', 'desc')->limit(5);
            }
        ])->find($id);

        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found'
            ], 404);
        }

        // Track property view
        PropertyView::create([
            'property_id' => $property->id,
            'user_id' => $request->user() ? $request->user()->id : null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $property->incrementViews();

        // Check if favorited by current user
        $isFavorited = false;
        if ($request->user()) {
            $isFavorited = Favorite::where('user_id', $request->user()->id)
                ->where('property_id', $property->id)
                ->exists();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'property' => $property,
                'is_favorited' => $isFavorited,
                'average_rating' => $property->averageRating(),
                'total_reviews' => $property->totalReviews(),
            ]
        ], 200);
    }

    /**
     * Create new property (landlord only)
     */
    public function store(Request $request)
    {
        if (!$request->user()->isLandlord()) {
            return response()->json([
                'success' => false,
                'message' => 'Only landlords can create properties'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'property_type' => ['required', 'in:apartment,bedsitter,single_room,double_room,studio,one_bedroom,two_bedroom,three_bedroom,house,mansion,hostel,commercial,land,other'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'county' => ['required', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'distance_to_mut' => ['nullable', 'numeric', 'min:0'],
            'bedrooms' => ['required', 'integer', 'min:0'],
            'bathrooms' => ['required', 'integer', 'min:0'],
            'square_feet' => ['nullable', 'integer', 'min:0'],
            'rent_amount' => ['required', 'numeric', 'min:0'],
            'deposit_amount' => ['required', 'numeric', 'min:0'],
            'is_negotiable' => ['boolean'],
            'available_from' => ['required', 'date'],
            'lease_duration' => ['required', 'in:monthly,quarterly,semi_annual,annual,flexible'],
            'furnishing_status' => ['required', 'in:furnished,semi_furnished,unfurnished'],
            'amenities' => ['nullable', 'array'],
            'rules' => ['nullable', 'array'],
            'utilities_included' => ['nullable', 'array'],
            'parking_available' => ['boolean'],
            'pet_friendly' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $property = Property::create([
                'landlord_id' => $request->user()->id,
                'title' => $request->title,
                'description' => $request->description,
                'property_type' => $request->property_type,
                'address' => $request->address,
                'city' => $request->city,
                'county' => $request->county,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'distance_to_mut' => $request->distance_to_mut,
                'bedrooms' => $request->bedrooms,
                'bathrooms' => $request->bathrooms,
                'square_feet' => $request->square_feet,
                'rent_amount' => $request->rent_amount,
                'deposit_amount' => $request->deposit_amount,
                'is_negotiable' => $request->boolean('is_negotiable', false),
                'available_from' => $request->available_from,
                'lease_duration' => $request->lease_duration,
                'furnishing_status' => $request->furnishing_status,
                'amenities' => $request->amenities ?? [],
                'rules' => $request->rules ?? [],
                'utilities_included' => $request->utilities_included ?? [],
                'parking_available' => $request->boolean('parking_available', false),
                'pet_friendly' => $request->boolean('pet_friendly', false),
                'status' => 'pending', // Requires admin approval
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Property created successfully. Pending admin approval.',
                'data' => $property
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create property',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update property
     */
    public function update(Request $request, $id)
    {
        $property = Property::find($id);

        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found'
            ], 404);
        }

        // Check ownership
        if ($property->landlord_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this property'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'rent_amount' => ['sometimes', 'numeric', 'min:0'],
            'deposit_amount' => ['sometimes', 'numeric', 'min:0'],
            'available_from' => ['sometimes', 'date'],
            'amenities' => ['sometimes', 'array'],
            'rules' => ['sometimes', 'array'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $property->update($request->only([
                'title', 'description', 'rent_amount', 'deposit_amount',
                'available_from', 'amenities', 'rules', 'utilities_included',
                'parking_available', 'pet_friendly', 'is_negotiable'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Property updated successfully',
                'data' => $property
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update property',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete property
     */
    public function destroy(Request $request, $id)
    {
        $property = Property::find($id);

        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found'
            ], 404);
        }

        // Check ownership
        if ($property->landlord_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this property'
            ], 403);
        }

        try {
            // Delete associated images from storage
            foreach ($property->images as $image) {
                Storage::disk('public')->delete($image->image_path);
                Storage::disk('public')->delete($image->thumbnail_path);
            }

            $property->delete();

            return response()->json([
                'success' => true,
                'message' => 'Property deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete property',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload property images
     */
    public function uploadImages(Request $request, $id)
    {
        $property = Property::find($id);

        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found'
            ], 404);
        }

        // Check ownership
        if ($property->landlord_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'images' => ['required', 'array', 'min:1', 'max:10'],
            'images.*' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5120'], // 5MB max
            'is_primary' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $uploadedImages = [];
            $existingImagesCount = $property->images()->count();

            foreach ($request->file('images') as $index => $image) {
                // Store original image
                $path = $image->store('properties/' . $property->id, 'public');
                
                // Create thumbnail (you can use intervention/image package for better thumbnails)
                $thumbnailPath = 'properties/' . $property->id . '/thumbnails/' . basename($path);
                Storage::disk('public')->copy($path, $thumbnailPath);

                // Determine if this should be primary
                $isPrimary = ($existingImagesCount === 0 && $index === 0) || 
                            ($request->boolean('is_primary') && $index === 0);

                // If setting as primary, unset other primary images
                if ($isPrimary) {
                    PropertyImage::where('property_id', $property->id)
                        ->update(['is_primary' => false]);
                }

                $propertyImage = PropertyImage::create([
                    'property_id' => $property->id,
                    'image_path' => $path,
                    'thumbnail_path' => $thumbnailPath,
                    'is_primary' => $isPrimary,
                    'display_order' => $existingImagesCount + $index,
                ]);

                $uploadedImages[] = $propertyImage;
            }

            return response()->json([
                'success' => true,
                'message' => 'Images uploaded successfully',
                'data' => $uploadedImages
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload images',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete property image
     */
    public function deleteImage(Request $request, $propertyId, $imageId)
    {
        $property = Property::find($propertyId);

        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found'
            ], 404);
        }

        // Check ownership
        if ($property->landlord_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $image = PropertyImage::where('id', $imageId)
            ->where('property_id', $propertyId)
            ->first();

        if (!$image) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found'
            ], 404);
        }

        try {
            // Delete from storage
            Storage::disk('public')->delete($image->image_path);
            Storage::disk('public')->delete($image->thumbnail_path);

            $image->delete();

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle favorite
     */
    public function toggleFavorite(Request $request, $id)
    {
        $property = Property::find($id);

        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found'
            ], 404);
        }

        $favorite = Favorite::where('user_id', $request->user()->id)
            ->where('property_id', $id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            $property->decrementFavorites();
            $message = 'Property removed from favorites';
            $isFavorited = false;
        } else {
            Favorite::create([
                'user_id' => $request->user()->id,
                'property_id' => $id,
            ]);
            $property->incrementFavorites();
            $message = 'Property added to favorites';
            $isFavorited = true;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'is_favorited' => $isFavorited,
                'favorites_count' => $property->favorites_count
            ]
        ], 200);
    }

    /**
     * Get landlord's properties
     */
    public function myProperties(Request $request)
    {
        $properties = Property::where('landlord_id', $request->user()->id)
            ->with(['images', 'primaryImage'])
            ->withCount(['bookings', 'reviews', 'favorites'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $properties
        ], 200);
    }

    /**
     * Get user's favorite properties
     */
    public function favorites(Request $request)
    {
        $favorites = Favorite::where('user_id', $request->user()->id)
            ->with(['property.landlord', 'property.primaryImage'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $favorites
        ], 200);
    }

    /**
     * Advanced search
     */
    public function search(Request $request)
    {
        $query = Property::with(['landlord', 'primaryImage'])
            ->approved()
            ->available();

        // Keyword search
        if ($request->has('keyword')) {
            $query->search($request->keyword);
        }

        // Location search
        if ($request->has('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        // Price range
        if ($request->has('min_price') && $request->has('max_price')) {
            $query->priceRange($request->min_price, $request->max_price);
        }

        // Bedrooms
        if ($request->has('bedrooms')) {
            $query->bedrooms($request->bedrooms);
        }

        // Distance to MUT
        if ($request->has('max_distance_to_mut')) {
            $query->nearMUT($request->max_distance_to_mut);
        }

        // Property type
        if ($request->has('property_type')) {
            $query->ofType($request->property_type);
        }

        $properties = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $properties
        ], 200);
    }
}

// Made with Bob
