<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class RoleMiddleware implements MiddlewareInterface
{
    private array $allowedRoles;

    public function __construct(array $allowedRoles = ['super_admin', 'admin'])
    {
        $this->allowedRoles = $allowedRoles;
    }

    public function handle(Request $request): bool
    {
        $user = Session::get('user');

        if (!$user) {
            Response::redirect('/login')->send();
            return false;
        }

        $userRole = $user['role'] ?? 'viewer';

        if (!in_array($userRole, $this->allowedRoles, true)) {
            if ($request->isAjax()) {
                Response::json([
                    'success' => false,
                    'message' => 'Forbidden: You do not have permission to perform this action.',
                ], 403)->send();
            } else {
                Session::flash('error', 'Access denied. You lack the required permissions for this action.');
                Response::redirect('/')->send();
            }
            return false;
        }

        return true;
    }
}
