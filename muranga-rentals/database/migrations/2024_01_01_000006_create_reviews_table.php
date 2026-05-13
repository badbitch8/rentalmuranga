<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('landlord_id')->constrained('users')->onDelete('cascade');
            $table->integer('rating')->comment('1-5');
            $table->integer('cleanliness_rating')->nullable()->comment('1-5');
            $table->integer('communication_rating')->nullable()->comment('1-5');
            $table->integer('value_rating')->nullable()->comment('1-5');
            $table->integer('location_rating')->nullable()->comment('1-5');
            $table->text('comment')->nullable();
            $table->text('landlord_response')->nullable();
            $table->timestamp('landlord_responded_at')->nullable();
            $table->boolean('is_verified')->default(false)->comment('Verified stay');
            $table->boolean('is_featured')->default(false);
            $table->integer('helpful_count')->default(0);
            $table->timestamps();

            $table->index('property_id');
            $table->index('tenant_id');
            $table->index('rating');
            $table->unique('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};

// Made with Bob
