<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class GuestMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): bool
    {
        if (Session::has('user') && !empty(Session::get('user'))) {
            Response::redirect('/')->send();
            return false;
        }
        return true;
    }
}
