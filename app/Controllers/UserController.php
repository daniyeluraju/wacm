<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\User;

class UserController extends BaseController
{
    /**
     * Display list of users
     */
    public function index(Request $request): Response
    {
        $users = User::all('id', 'ASC');

        return $this->render('users/index', [
            'pageTitle' => 'User Management & Access Control - WACM',
            'users' => $users,
        ], 'layouts/main');
    }

    /**
     * Render Create User form
     */
    public function create(Request $request): Response
    {
        return $this->render('users/create', [
            'pageTitle' => 'Add New User - WACM',
        ], 'layouts/main');
    }

    /**
     * Store new user
     */
    public function store(Request $request): Response
    {
        $validated = $this->validate($request, [
            'name' => 'required|min:2|max:150',
            'email' => 'required|email',
            'role' => 'required',
            'password' => 'required|min:8',
        ]);

        $email = strtolower(trim($validated['email']));

        if (User::findByEmail($email)) {
            Session::flash('error', 'A user with this email address already exists.');
            Session::setOldInput($request->input());
            return $this->redirect('/users/create');
        }

        $allowedRoles = ['super_admin', 'admin', 'viewer'];
        if (!in_array($validated['role'], $allowedRoles, true)) {
            Session::flash('error', 'Invalid role selected.');
            return $this->redirect('/users/create');
        }

        $user = User::create([
            'name' => trim($validated['name']),
            'email' => $email,
            'password_hash' => password_hash($validated['password'], PASSWORD_BCRYPT),
            'role' => $validated['role'],
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        AuditLog::log('User Created', 'User', $user->id, null, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        Session::flash('success', "User '{$user->name}' created successfully.");
        return $this->redirect('/users');
    }

    /**
     * Render Edit User form
     */
    public function edit(Request $request, string $id): Response
    {
        $user = User::find((int) $id);

        if (!$user) {
            Session::flash('error', 'User not found.');
            return $this->redirect('/users');
        }

        return $this->render('users/edit', [
            'pageTitle' => "Edit User: {$user->name} - WACM",
            'user' => $user,
        ], 'layouts/main');
    }

    /**
     * Update user details
     */
    public function update(Request $request, string $id): Response
    {
        $user = User::find((int) $id);

        if (!$user) {
            Session::flash('error', 'User not found.');
            return $this->redirect('/users');
        }

        $validated = $this->validate($request, [
            'name' => 'required|min:2|max:150',
            'email' => 'required|email',
            'role' => 'required',
            'status' => 'required',
        ]);

        $email = strtolower(trim($validated['email']));
        $existing = User::findByEmail($email);
        if ($existing && (int)$existing->id !== (int)$user->id) {
            Session::flash('error', 'This email is already taken by another account.');
            return $this->redirect("/users/{$id}/edit");
        }

        $updateData = [
            'name' => trim($validated['name']),
            'email' => $email,
            'role' => $validated['role'],
            'status' => $validated['status'],
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Optional password reset
        $newPassword = $request->input('new_password');
        if (!empty($newPassword)) {
            if (strlen($newPassword) < 8) {
                Session::flash('error', 'Password must be at least 8 characters long.');
                return $this->redirect("/users/{$id}/edit");
            }
            $updateData['password_hash'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        $oldValues = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
        ];

        User::update($user->id, $updateData);

        AuditLog::log('User Updated', 'User', $user->id, $oldValues, $updateData);

        Session::flash('success', "User '{$user->name}' updated successfully.");
        return $this->redirect('/users');
    }

    /**
     * Delete user
     */
    public function delete(Request $request, string $id): Response
    {
        $currentUser = Session::get('user');
        if ((int)$currentUser['id'] === (int)$id) {
            Session::flash('error', 'Security Violation: You cannot delete your own active administrator account.');
            return $this->redirect('/users');
        }

        $user = User::find((int) $id);
        if (!$user) {
            Session::flash('error', 'User not found.');
            return $this->redirect('/users');
        }

        $userName = $user->name;
        User::delete($user->id);

        AuditLog::log('User Deleted', 'User', $id, ['name' => $userName, 'email' => $user->email]);

        Session::flash('success', "User '{$userName}' deleted successfully.");
        return $this->redirect('/users');
    }
}
