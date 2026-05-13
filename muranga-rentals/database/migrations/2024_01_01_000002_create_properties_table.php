<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landlord_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->enum('property_type', [
                'apartment', 'bedsitter', 'single_room', 'double_room', 'studio',
                'one_bedroom', 'two_bedroom', 'three_bedroom', 'four_bedroom',
                'villa', 'townhouse', 'penthouse', 'duplex', 'house'
            ]);
            $table->integer('bedrooms')->default(1);
            $table->integer('bathrooms')->default(1);
            $table->integer('square_feet')->nullable();
            $table->decimal('rent_amount', 10, 2);
            $table->decimal('deposit_amount', 10, 2);
            $table->text('address');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('county', 100)->default('Murang\'a');
            $table->string('sub_county', 100)->nullable();
            $table->string('ward', 100)->nullable();
            $table->decimal('distance_to_mut', 5, 2)->nullable()->comment('Distance in KM');
            $table->json('amenities')->nullable()->comment('wifi, parking, water, electricity, security, etc');
            $table->json('rules')->nullable()->comment('no_pets, no_smoking, etc');
            $table->enum('furnishing_status', ['furnished', 'semi_furnished', 'unfurnished'])->default('unfurnished');
            $table->enum('availability_status', ['available', 'occupied', 'maintenance', 'unlisted'])->default('available');
            $table->date('available_from')->nullable();
            $table->enum('verification_status', ['unverified', 'pending', 'verified', 'rejected'])->default('unverified');
            $table->text('verification_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('inquiries_count')->default(0);
            $table->integer('bookings_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('featured_until')->nullable();
            $table->string('virtual_tour_url')->nullable();
            $table->string('video_url')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('landlord_id');
            $table->index('property_type');
            $table->index('rent_amount');
            $table->index('availability_status');
            $table->index('verification_status');
            $table->index(['latitude', 'longitude']);
            $table->index('distance_to_mut');
            $table->fullText(['title', 'description', 'address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};

// Made with Bob
