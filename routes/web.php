<?php

use App\Controllers\ActivityController;
use App\Controllers\AnalyticsController;
use App\Controllers\AssistantController;
use App\Controllers\AuthController;
use App\Controllers\CampaignController;
use App\Controllers\CleanupController;
use App\Controllers\ComposerController;
use App\Controllers\ContactController;
use App\Controllers\DashboardController;
use App\Controllers\HomeController;
use App\Controllers\ImportController;
use App\Controllers\SettingsController;
use App\Controllers\UserController;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\RoleMiddleware;

/** @var \App\Core\Router $router */

// Public Health Check Endpoint
$router->get('/health', [HomeController::class, 'ping']);

// -------------------------------------------------------------
// Guest Authentication Routes
// -------------------------------------------------------------
$router->get('/login', [AuthController::class, 'showLogin'], [GuestMiddleware::class]);
$router->post('/login', [AuthController::class, 'login'], [GuestMiddleware::class, CsrfMiddleware::class]);

// -------------------------------------------------------------
// Authenticated Session & Profile
// -------------------------------------------------------------
$router->get('/logout', [AuthController::class, 'logout']);
$router->post('/logout', [AuthController::class, 'logout'], [CsrfMiddleware::class]);

$router->get('/', [DashboardController::class, 'index'], [AuthMiddleware::class]);
$router->get('/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);
$router->get('/system/status', [HomeController::class, 'index'], [AuthMiddleware::class]);

$router->get('/profile', [AuthController::class, 'profile'], [AuthMiddleware::class]);
$router->post('/profile', [AuthController::class, 'updateProfile'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/password/change', [AuthController::class, 'changePassword'], [AuthMiddleware::class, CsrfMiddleware::class]);

// -------------------------------------------------------------
// Super Admin User Management (RBAC: super_admin)
// -------------------------------------------------------------
$superAdminRole = new RoleMiddleware(['super_admin']);

$router->get('/users', [UserController::class, 'index'], [AuthMiddleware::class, $superAdminRole]);
$router->get('/users/create', [UserController::class, 'create'], [AuthMiddleware::class, $superAdminRole]);
$router->post('/users', [UserController::class, 'store'], [AuthMiddleware::class, $superAdminRole, CsrfMiddleware::class]);
$router->get('/users/{id}/edit', [UserController::class, 'edit'], [AuthMiddleware::class, $superAdminRole]);
$router->post('/users/{id}', [UserController::class, 'update'], [AuthMiddleware::class, $superAdminRole, CsrfMiddleware::class]);
$router->post('/users/{id}/delete', [UserController::class, 'delete'], [AuthMiddleware::class, $superAdminRole, CsrfMiddleware::class]);

// -------------------------------------------------------------
// Module 1: Contacts Management
// -------------------------------------------------------------
$router->get('/contacts', [ContactController::class, 'index'], [AuthMiddleware::class]);
$router->get('/contacts/create', [ContactController::class, 'create'], [AuthMiddleware::class]);
$router->post('/contacts', [ContactController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/contacts/export', [ContactController::class, 'export'], [AuthMiddleware::class]);
$router->get('/contacts/{id}/edit', [ContactController::class, 'edit'], [AuthMiddleware::class]);
$router->post('/contacts/{id}', [ContactController::class, 'update'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/contacts/{id}/delete', [ContactController::class, 'delete'], [AuthMiddleware::class, CsrfMiddleware::class]);

// -------------------------------------------------------------
// Module 2: CSV / XLSX Import Wizard
// -------------------------------------------------------------
$router->get('/import', [ImportController::class, 'index'], [AuthMiddleware::class]);
$router->get('/import/sample', [ImportController::class, 'downloadSample'], [AuthMiddleware::class]);
$router->get('/import/errors/{filename}', [ImportController::class, 'downloadErrors'], [AuthMiddleware::class]);
$router->post('/import/upload', [ImportController::class, 'upload'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/import/process', [ImportController::class, 'process'], [AuthMiddleware::class, CsrfMiddleware::class]);

// -------------------------------------------------------------
// Module 3: Message Composer & Drafts
// -------------------------------------------------------------
$router->get('/composer', [ComposerController::class, 'index'], [AuthMiddleware::class]);
$router->get('/composer/create', [ComposerController::class, 'create'], [AuthMiddleware::class]);
$router->post('/composer', [ComposerController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/composer/{id}/edit', [ComposerController::class, 'edit'], [AuthMiddleware::class]);
$router->post('/composer/{id}', [ComposerController::class, 'update'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/composer/{id}/duplicate', [ComposerController::class, 'duplicate'], [AuthMiddleware::class]);
$router->post('/composer/{id}/delete', [ComposerController::class, 'delete'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/composer/{id}/unarchive', [ComposerController::class, 'unarchive'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/attachments/{id}', [ComposerController::class, 'serveAttachment'], [AuthMiddleware::class]);

// -------------------------------------------------------------
// Module 4: Campaign Management
// -------------------------------------------------------------
$router->get('/campaigns', [CampaignController::class, 'index'], [AuthMiddleware::class]);
$router->get('/campaigns/create', [CampaignController::class, 'create'], [AuthMiddleware::class]);
$router->post('/campaigns', [CampaignController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/campaigns/{id}', [CampaignController::class, 'show'], [AuthMiddleware::class]);
$router->get('/campaigns/{id}/start', [CampaignController::class, 'start'], [AuthMiddleware::class]);
$router->post('/campaigns/{id}/direct-send', [CampaignController::class, 'directSend'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/campaigns/{id}/pause', [CampaignController::class, 'pause'], [AuthMiddleware::class]);
$router->get('/campaigns/{id}/cancel', [CampaignController::class, 'cancel'], [AuthMiddleware::class]);
$router->post('/campaigns/{id}/delete', [CampaignController::class, 'destroy'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/campaigns/{id}/refresh-messages', [CampaignController::class, 'refreshMessages'], [AuthMiddleware::class, CsrfMiddleware::class]);


// -------------------------------------------------------------
// Module 5: Manual WhatsApp Web Assistant
// -------------------------------------------------------------
$router->get('/assistant', [AssistantController::class, 'index'], [AuthMiddleware::class]);
$router->get('/assistant/{id}', [AssistantController::class, 'session'], [AuthMiddleware::class]);
$router->post('/assistant/recipient/{id}/opened', [AssistantController::class, 'markOpened'], [AuthMiddleware::class]);
$router->post('/assistant/recipient/{id}/completed', [AssistantController::class, 'markCompleted'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/assistant/recipient/{id}/send-direct', [AssistantController::class, 'sendDirect'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/assistant/recipient/{id}/skip', [AssistantController::class, 'skip'], [AuthMiddleware::class, CsrfMiddleware::class]);

// -------------------------------------------------------------
// Module 6: Activity History & Audit Logs
// -------------------------------------------------------------
$router->get('/activity', [ActivityController::class, 'index'], [AuthMiddleware::class]);
$router->get('/activity/export', [ActivityController::class, 'export'], [AuthMiddleware::class]);

// -------------------------------------------------------------
// Module 7: Analytics & Reports
// -------------------------------------------------------------
$router->get('/analytics', [AnalyticsController::class, 'index'], [AuthMiddleware::class]);

// -------------------------------------------------------------
// Module 8: Storage & Automatic Cleanup
// -------------------------------------------------------------
$router->get('/storage', [CleanupController::class, 'index'], [AuthMiddleware::class]);
$router->post('/storage/cleanup', [CleanupController::class, 'run'], [AuthMiddleware::class, CsrfMiddleware::class]);

// -------------------------------------------------------------
// Module 9: Settings & Safety Controls
// -------------------------------------------------------------
$router->get('/settings', [SettingsController::class, 'index'], [AuthMiddleware::class]);
$router->post('/settings', [SettingsController::class, 'update'], [AuthMiddleware::class, CsrfMiddleware::class]);

// -------------------------------------------------------------
// Module 10: WhatsApp QR Gateway Device Linking
// -------------------------------------------------------------
$router->get('/gateway', [\App\Controllers\GatewayController::class, 'index'], [AuthMiddleware::class]);
$router->get('/gateway/status', [\App\Controllers\GatewayController::class, 'status'], [AuthMiddleware::class]);
$router->post('/gateway/disconnect', [\App\Controllers\GatewayController::class, 'disconnect'], [AuthMiddleware::class, CsrfMiddleware::class]);
