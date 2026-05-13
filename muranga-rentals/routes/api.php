<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\MaintenanceRequestController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('v1')->group(function () {
    
    // Authentication routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Public property routes
    Route::get('/properties', [PropertyController::class, 'index']);
    Route::get('/properties/{id}', [PropertyController::class, 'show']);
    Route::get('/properties/search', [PropertyController::class, 'search']);
    
    // M-Pesa callback (public endpoint for Safaricom)
    Route::post('/payments/mpesa/callback', [PaymentController::class, 'mpesaCallback']);
    
});

// Protected routes
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    // Property routes
    Route::post('/properties', [PropertyController::class, 'store']);
    Route::put('/properties/{id}', [PropertyController::class, 'update']);
    Route::delete('/properties/{id}', [PropertyController::class, 'destroy']);
    Route::post('/properties/{id}/images', [PropertyController::class, 'uploadImages']);
    Route::delete('/properties/{propertyId}/images/{imageId}', [PropertyController::class, 'deleteImage']);
    Route::post('/properties/{id}/favorite', [PropertyController::class, 'toggleFavorite']);
    Route::get('/my-properties', [PropertyController::class, 'myProperties']);
    Route::get('/favorites', [PropertyController::class, 'favorites']);
    
    // Booking routes
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::put('/bookings/{id}', [BookingController::class, 'update']);
    Route::post('/bookings/{id}/confirm', [BookingController::class, 'confirm']);
    Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
    Route::post('/bookings/{id}/sign', [BookingController::class, 'sign']);
    
    // Payment routes
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::get('/payments/{id}', [PaymentController::class, 'show']);
    Route::post('/payments/mpesa/initiate', [PaymentController::class, 'initiateMpesaPayment']);
    Route::get('/payments/{id}/status', [PaymentController::class, 'checkPaymentStatus']);
    Route::get('/payments/stats', [PaymentController::class, 'getPaymentStats']);
    Route::get('/payments/{id}/receipt', [PaymentController::class, 'generateReceipt']);
    
    // Review routes
    Route::get('/properties/{propertyId}/reviews', [ReviewController::class, 'getPropertyReviews']);
    Route::get('/my-reviews', [ReviewController::class, 'getUserReviews']);
    Route::get('/landlord/reviews', [ReviewController::class, 'getLandlordReviews']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    Route::post('/reviews/{id}/respond', [ReviewController::class, 'respondToReview']);
    Route::post('/reviews/{id}/helpful', [ReviewController::class, 'markHelpful']);
    
    // Maintenance request routes
    Route::get('/maintenance-requests', [MaintenanceRequestController::class, 'index']);
    Route::get('/maintenance-requests/{id}', [MaintenanceRequestController::class, 'show']);
    Route::post('/maintenance-requests', [MaintenanceRequestController::class, 'store']);
    Route::put('/maintenance-requests/{id}/status', [MaintenanceRequestController::class, 'updateStatus']);
    Route::post('/maintenance-requests/{id}/cost', [MaintenanceRequestController::class, 'addActualCost']);
    Route::post('/maintenance-requests/{id}/cancel', [MaintenanceRequestController::class, 'cancel']);
    Route::get('/maintenance-requests/stats', [MaintenanceRequestController::class, 'getStatistics']);
    
    // Message routes
    Route::get('/messages', [MessageController::class, 'index']);
    Route::get('/messages/conversation/{userId}', [MessageController::class, 'conversation']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::post('/messages/{id}/read', [MessageController::class, 'markAsRead']);
    Route::post('/messages/{userId}/read-all', [MessageController::class, 'markAllAsRead']);
    Route::delete('/messages/{id}', [MessageController::class, 'destroy']);
    Route::get('/messages/search', [MessageController::class, 'search']);
    Route::get('/messages/stats', [MessageController::class, 'getStatistics']);
    Route::get('/messages/unread-count', [MessageController::class, 'unreadCount']);
    Route::post('/messages/block/{userId}', [MessageController::class, 'blockUser']);
    Route::post('/messages/unblock/{userId}', [MessageController::class, 'unblockUser']);
    Route::get('/messages/blocked-users', [MessageController::class, 'getBlockedUsers']);
    
    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/{id}', [NotificationController::class, 'show']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::delete('/notifications/delete-read', [NotificationController::class, 'deleteAllRead']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::get('/notifications/stats', [NotificationController::class, 'getStatistics']);
    Route::get('/notifications/grouped', [NotificationController::class, 'getGroupedByDate']);
    Route::post('/notifications/{id}/actioned', [NotificationController::class, 'markAsActioned']);
    Route::get('/notifications/preferences', [NotificationController::class, 'getPreferences']);
    Route::put('/notifications/preferences', [NotificationController::class, 'updatePreferences']);
    Route::post('/notifications/test', [NotificationController::class, 'sendTestNotification']);
    Route::get('/notifications/types', [NotificationController::class, 'getNotificationTypes']);
    
});

// Admin routes
Route::prefix('v1/admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    
    // User management
    Route::get('/users', [AdminController::class, 'users']);
    Route::put('/users/{id}/verify', [AdminController::class, 'verifyUser']);
    Route::put('/users/{id}/suspend', [AdminController::class, 'suspendUser']);
    
    // Property management
    Route::get('/properties/pending', [AdminController::class, 'pendingProperties']);
    Route::put('/properties/{id}/approve', [AdminController::class, 'approveProperty']);
    Route::put('/properties/{id}/reject', [AdminController::class, 'rejectProperty']);
    
    // Review management
    Route::post('/reviews/{id}/approve', [ReviewController::class, 'approveReview']);
    Route::post('/reviews/{id}/reject', [ReviewController::class, 'rejectReview']);
    
    // Analytics
    Route::get('/analytics/overview', [AdminController::class, 'analyticsOverview']);
    Route::get('/analytics/revenue', [AdminController::class, 'revenueAnalytics']);
    
});

// Made with Bob
