<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ServiceCategoryController;
use App\Http\Controllers\Api\HandymanController;
use App\Http\Controllers\Api\ServiceRequestController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\CashRegisterController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\VideoCallController;
use App\Http\Controllers\Api\PortalController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ============================================================================
// RUTAS PÚBLICAS DEL PORTAL (SIN AUTENTICACIÓN)
// ============================================================================
Route::prefix('portal')->group(function () {
    Route::get('/categories', [PortalController::class, 'getCategories']);
    Route::post('/check-registration', [PortalController::class, 'checkRegistration']);
    Route::post('/submit-registration', [PortalController::class, 'submitRegistration']);
    Route::get('/lookup-zipcode/{zipcode}', [PortalController::class, 'lookupZipCode']); // ← NUEVA LÍNEA
});

// ============================================================================
// RUTAS PÚBLICAS DE AUTENTICACIÓN
// ============================================================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Categorías públicas
Route::get('/service-categories', [ServiceCategoryController::class, 'index']);

// ============================================================================
// RUTAS PROTEGIDAS (Requieren autenticación)
// ============================================================================
Route::middleware('auth:sanctum')->group(function () {
    
    // ------------------------------------------------------------------------
    // AUTENTICACIÓN
    // ------------------------------------------------------------------------
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // ------------------------------------------------------------------------
    // DASHBOARD
    // ------------------------------------------------------------------------
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // ------------------------------------------------------------------------
    // USUARIOS
    // ------------------------------------------------------------------------
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::post('/users/avatar', [UserController::class, 'updateAvatar']);

    // ------------------------------------------------------------------------
    // ROLES Y PERMISOS (Solo SuperAdmin)
    // ------------------------------------------------------------------------
    Route::middleware(['role:superadmin'])->group(function () {
        Route::get('/roles', [RoleController::class, 'index']);
        Route::post('/roles', [RoleController::class, 'store']);
        Route::get('/roles/{id}', [RoleController::class, 'show']);
        Route::put('/roles/{id}', [RoleController::class, 'update']);
        Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
        Route::get('/permissions', [PermissionController::class, 'index']);
    });

    // ------------------------------------------------------------------------
    // CATEGORÍAS DE SERVICIO
    // ------------------------------------------------------------------------
    Route::get('/service-categories/{id}', [ServiceCategoryController::class, 'show']);
    Route::post('/service-categories', [ServiceCategoryController::class, 'store']);
    Route::post('/service-categories/{id}', [ServiceCategoryController::class, 'update']);
    Route::delete('/service-categories/{id}', [ServiceCategoryController::class, 'destroy']);

    // ------------------------------------------------------------------------
    // HANDYMEN
    // ------------------------------------------------------------------------
    Route::get('/handymen', [HandymanController::class, 'index']);
    Route::post('/handymen', [HandymanController::class, 'store']);
    Route::get('/handymen/{id}', [HandymanController::class, 'show']);
    Route::put('/handymen/{id}', [HandymanController::class, 'update']);
    Route::post('/handymen/{id}/approve', [HandymanController::class, 'approve']);
    Route::post('/handymen/{id}/reject', [HandymanController::class, 'reject']);
    Route::post('/handymen/{id}/availability', [HandymanController::class, 'updateAvailability']);

    // ------------------------------------------------------------------------
    // SOLICITUDES DE SERVICIO
    // ------------------------------------------------------------------------
    Route::get('/service-requests', [ServiceRequestController::class, 'index']);
    Route::post('/service-requests', [ServiceRequestController::class, 'store']);
    Route::get('/service-requests/{id}', [ServiceRequestController::class, 'show']);
    Route::put('/service-requests/{id}', [ServiceRequestController::class, 'update']);
    Route::post('/service-requests/{id}/accept', [ServiceRequestController::class, 'accept']);
    Route::post('/service-requests/{id}/reject', [ServiceRequestController::class, 'reject']);
    Route::post('/service-requests/{id}/start', [ServiceRequestController::class, 'start']);
    Route::post('/service-requests/{id}/complete', [ServiceRequestController::class, 'complete']);
    Route::post('/service-requests/{id}/cancel', [ServiceRequestController::class, 'cancel']);
    Route::post('/service-requests/{id}/arrived', [ServiceRequestController::class, 'arrived']);

    // ------------------------------------------------------------------------
    // CONVERSACIONES - ORDEN IMPORTANTE: específicas ANTES de genéricas
    // ------------------------------------------------------------------------
    Route::get('/conversations/unread/count', [ConversationController::class, 'unreadCount']);
    Route::post('/conversations', [ConversationController::class, 'store']);
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::get('/conversations/{id}', [ConversationController::class, 'show']);

    // ------------------------------------------------------------------------
    // MENSAJES
    // ------------------------------------------------------------------------
    Route::get('/conversations/{conversationId}/messages', [MessageController::class, 'index']);
    Route::post('/conversations/{conversationId}/messages', [MessageController::class, 'store']);
    Route::post('/conversations/{conversationId}/messages/mark-read', [MessageController::class, 'markAsRead']);

    // ------------------------------------------------------------------------
    // NOTIFICACIONES - ORDEN IMPORTANTE: específicas ANTES de genéricas
    // ------------------------------------------------------------------------
    Route::get('/notifications/unread/count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::delete('/notifications', [NotificationController::class, 'destroyAll']);

    // ------------------------------------------------------------------------
    // RESEÑAS
    // ------------------------------------------------------------------------
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/reviews/{id}', [ReviewController::class, 'show']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    Route::get('/reviews/check/{serviceRequestId}', [ReviewController::class, 'checkIfCanReview']);
    Route::get('/handymen/{handymanId}/rating', [ReviewController::class, 'handymanRating']);

    // ------------------------------------------------------------------------
    // PAGOS - ORDEN IMPORTANTE: específicas ANTES de genéricas
    // ------------------------------------------------------------------------
    Route::get('/payments/handyman/earnings', [PaymentController::class, 'earnings']);
    Route::get('/payments/platform/revenue', [PaymentController::class, 'platformRevenue']);
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/payments/{id}', [PaymentController::class, 'show']);

    // ------------------------------------------------------------------------
    // CAJAS REGISTRADORAS - ORDEN IMPORTANTE: específicas ANTES de genéricas
    // ------------------------------------------------------------------------
    Route::get('/cash-registers/current', [CashRegisterController::class, 'getCurrentRegister']);
    Route::post('/cash-registers/open', [CashRegisterController::class, 'open']);
    Route::post('/cash-registers/{id}/close', [CashRegisterController::class, 'close']);
    Route::get('/cash-registers', [CashRegisterController::class, 'index']);

    // ------------------------------------------------------------------------
    // REPORTES
    // ------------------------------------------------------------------------
    Route::get('/reports', [ReportController::class, 'index']);
    Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf']);
    Route::get('/reports/export/excel', [ReportController::class, 'exportExcel']);

    // ------------------------------------------------------------------------
    // UBICACIONES GPS
    // ------------------------------------------------------------------------
    Route::post('/locations', [LocationController::class, 'update']);
    Route::get('/locations', [LocationController::class, 'index']);
    Route::get('/locations/me', [LocationController::class, 'myLocation']);
    Route::get('/locations/service-request/{id}', [LocationController::class, 'show']);
    Route::post('/locations/deactivate', [LocationController::class, 'deactivate']);

    // ------------------------------------------------------------------------
    // VIDEOLLAMADAS
    // ------------------------------------------------------------------------
    Route::post('/video-calls/initiate', [VideoCallController::class, 'initiate']);
    Route::post('/video-calls/{callId}/answer', [VideoCallController::class, 'answer']);
    Route::post('/video-calls/{callId}/end', [VideoCallController::class, 'end']);
    Route::post('/video-calls/{callId}/signal', [VideoCallController::class, 'signal']);

    // ------------------------------------------------------------------------
    // ADMINISTRACIÓN DE REGISTROS DE CLIENTES
    // ------------------------------------------------------------------------
    Route::prefix('client-registrations')->group(function () {
        Route::get('/', [PortalController::class, 'getRegistrations']);
        Route::post('/{id}/approve', [PortalController::class, 'approveRegistration']);
        Route::post('/{id}/reject', [PortalController::class, 'rejectRegistration']);
    });
});