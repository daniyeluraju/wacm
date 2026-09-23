<?php

/**
 * WACM Phase 2 Automated Test Suite
 * Run with: php database/test_phase2.php
 */

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();
\App\Core\Autoloader::addNamespace('App', dirname(__DIR__) . '/app');

require_once dirname(__DIR__) . '/app/Helpers/functions.php';
require_once dirname(__DIR__) . '/app/Core/Env.php';
\App\Core\Env::load(dirname(__DIR__) . '/.env');

use App\Core\Database;
use App\Core\Security;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuthService;

echo "=======================================================\n";
echo "  WACM - Phase 2 Authentication & RBAC Test Suite\n";
echo "=======================================================\n\n";

$passed = 0;
$failed = 0;

function assertTest(string $title, bool $condition, string $failReason = '') {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$title}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$title} - {$failReason}\n";
        $failed++;
    }
}

// 1. Test Database connection
$connTest = Database::testConnection();
assertTest('Database is accessible', $connTest['status'] === true);

// 2. Test Super Admin Login
$auth = new AuthService();
$adminLogin = $auth->attempt('admin@wacm.local', 'Admin@123456');
assertTest('Super Admin login with correct password', $adminLogin['success'] === true && $adminLogin['user']['role'] === 'super_admin');

// 3. Test Admin Login
$managerLogin = $auth->attempt('manager@wacm.local', 'Admin@123456');
assertTest('Admin (manager) login with correct password', $managerLogin['success'] === true && $managerLogin['user']['role'] === 'admin');

// 4. Test Viewer Login
$viewerLogin = $auth->attempt('viewer@wacm.local', 'Viewer@123456');
assertTest('Viewer login with correct password', $viewerLogin['success'] === true && $viewerLogin['user']['role'] === 'viewer');

// 5. Test Invalid Password
$invalidLogin = $auth->attempt('admin@wacm.local', 'WrongPassword123');
assertTest('Reject invalid password', $invalidLogin['success'] === false);

// 6. Test Non-existent Email
$nonExistent = $auth->attempt('ghost@wacm.local', 'SomePassword123');
assertTest('Reject non-existent user email', $nonExistent['success'] === false);

// 7. Test Activity Logs Recording
$recentLog = ActivityLog::where('action_type', 'Login');
assertTest('Activity log created on login attempt', count($recentLog) > 0);

// 8. Test CSRF Token Generation and Verification
$token = Security::csrfToken();
assertTest('CSRF token is non-empty 64-char hex string', strlen($token) === 64);
assertTest('CSRF verification succeeds with valid token', Security::verifyCsrfToken($token) === true);
assertTest('CSRF verification rejects invalid token', Security::verifyCsrfToken('fake-token-value') === false);

// 9. Test User Creation, Update, and Deletion via Model
$testEmail = 'test_unit_' . time() . '@wacm.local';
$newUser = User::create([
    'name' => 'Unit Test Operator',
    'email' => $testEmail,
    'password_hash' => password_hash('TestPass@123', PASSWORD_BCRYPT),
    'role' => 'admin',
    'status' => 'active',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
]);
assertTest('Create new user via Model', $newUser !== null && $newUser->email === $testEmail);

// Test Profile Update
$updateRes = $auth->updateProfile((int)$newUser->id, 'Updated Unit Name', $testEmail);
assertTest('Update user profile name', $updateRes['success'] === true);

// Test Password Change
$pwdChangeRes = $auth->changePassword((int)$newUser->id, 'TestPass@123', 'NewSecretPass@456');
assertTest('Change user password with verification', $pwdChangeRes['success'] === true);

// Test Login with new password
$newLoginRes = $auth->attempt($testEmail, 'NewSecretPass@456');
assertTest('Authenticate with changed password', $newLoginRes['success'] === true);

// Clean up unit test user
User::delete($newUser->id);
$deletedCheck = User::find($newUser->id);
assertTest('Clean up unit test user', $deletedCheck === null);

echo "\n=======================================================\n";
echo "  Test Summary: {$passed} Passed, {$failed} Failed\n";
echo "=======================================================\n";
