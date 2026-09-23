<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\AuthService;

class AuthController extends BaseController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * Display the login page
     */
    public function showLogin(Request $request): Response
    {
        return $this->render('auth/login', [
            'pageTitle' => 'Sign In - WACM',
        ], 'layouts/auth');
    }

    /**
     * Process authentication credentials
     */
    public function login(Request $request): Response
    {
        $validated = $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $result = $this->authService->attempt(
            $validated['email'],
            $validated['password'],
            $request->ip(),
            $request->userAgent()
        );

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            Session::setOldInput(['email' => $validated['email']]);
            return $this->redirect('/login');
        }

        Session::flash('success', "Welcome back, {$result['user']['name']}!");
        return $this->redirect('/');
    }

    /**
     * Terminate user session
     */
    public function logout(Request $request): Response
    {
        $this->authService->logout();
        Session::flash('info', 'You have been signed out successfully.');
        return $this->redirect('/login');
    }

    /**
     * View user profile and security settings
     */
    public function profile(Request $request): Response
    {
        $sessionUser = Session::get('user');
        $user = User::find($sessionUser['id'] ?? 0);

        if (!$user) {
            Session::flash('error', 'User record not found.');
            return $this->redirect('/login');
        }

        // Fetch recent security activity for current user
        $recentActivities = ActivityLog::where('user_id', $user->id);
        usort($recentActivities, fn($a, $b) => strcmp($b->created_at, $a->created_at));
        $recentActivities = array_slice($recentActivities, 0, 10);

        return $this->render('auth/profile', [
            'pageTitle' => 'User Profile & Security - WACM',
            'user' => $user,
            'recentActivities' => $recentActivities,
        ], 'layouts/main');
    }

    /**
     * Update user profile information
     */
    public function updateProfile(Request $request): Response
    {
        $sessionUser = Session::get('user');
        $userId = (int) ($sessionUser['id'] ?? 0);

        $validated = $this->validate($request, [
            'name' => 'required|min:2|max:150',
            'email' => 'required|email',
        ]);

        $result = $this->authService->updateProfile($userId, $validated['name'], $validated['email']);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            return $this->redirect('/profile');
        }

        Session::flash('success', 'Profile updated successfully.');
        return $this->redirect('/profile');
    }

    /**
     * Update user password
     */
    public function changePassword(Request $request): Response
    {
        $sessionUser = Session::get('user');
        $userId = (int) ($sessionUser['id'] ?? 0);

        $validated = $this->validate($request, [
            'current_password' => 'required',
            'new_password' => 'required|min:8',
            'confirm_password' => 'required',
        ]);

        if ($validated['new_password'] !== $validated['confirm_password']) {
            Session::flash('error', 'New password and confirmation do not match.');
            return $this->redirect('/profile');
        }

        $result = $this->authService->changePassword($userId, $validated['current_password'], $validated['new_password']);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            return $this->redirect('/profile');
        }

        Session::flash('success', 'Password updated successfully. Please use your new password next time.');
        return $this->redirect('/profile');
    }
}
