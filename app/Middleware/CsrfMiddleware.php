<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Security;
use App\Core\Session;

class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): bool
    {
        // CSRF protection only applies to state-changing HTTP methods
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            return true;
        }

        $token = $request->input('_csrf_token') ?? 
                 $request->header('X_CSRF_TOKEN') ?? 
                 $request->header('X_XSRF_TOKEN');

        if (!Security::verifyCsrfToken($token)) {
            if ($request->isAjax()) {
                Response::json([
                    'success' => false,
                    'message' => 'CSRF token mismatch or expired. Please refresh the page.',
                ], 419)->send();
            } else {
                Session::flash('error', 'Security token expired or invalid. Please try again.');
                Response::redirect($_SERVER['HTTP_REFERER'] ?? '/')->send();
            }
            return false;
        }

        return true;
    }
}
