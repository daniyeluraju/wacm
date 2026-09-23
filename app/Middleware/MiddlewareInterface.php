<?php

namespace App\Middleware;

use App\Core\Request;

interface MiddlewareInterface
{
    /**
     * Handle an incoming request.
     * Return true to proceed, or return false / throw exception / send response to abort.
     */
    public function handle(Request $request): bool;
}
