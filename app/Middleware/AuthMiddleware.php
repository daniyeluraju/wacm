<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): bool
    {
        $user = Session::get('user');

        if (!$user) {
            if ($request->isAjax()) {
                Response::json([
                    'success' => false,
                    'message' => 'Unauthorized. Please sign in.',
                ], 401)->send();
            } else {
                Session::flash('warning', 'Please sign in to access this page.');
                Response::redirect('/login')->send();
            }
            return false;
        }

        return true;
    }
}
