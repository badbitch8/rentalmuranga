<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('referred_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('referral_code', 50)->unique();
            $table->string('referred_email')->nullable();
            $table->string('referred_phone', 20)->nullable();
            $table->enum('status', ['pending', 'registered', 'completed'])->default('pending');
            $table->decimal('reward_amount', 10, 2)->default(0.00);
            $table->boolean('reward_paid')->default(false);
            $table->timestamp('reward_paid_at')->nullable();
            $table->timestamps();

            $table->index('referrer_id');
            $table->index('referral_code');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};

// Made with Bob
