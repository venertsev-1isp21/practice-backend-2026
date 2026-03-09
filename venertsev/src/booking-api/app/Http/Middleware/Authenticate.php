<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        return null;
    }

    protected function unauthenticated($request, array $guards)
    {
        // Всегда отдаём JSON
        abort(response()->json(['error' => 'Unauthenticated'], 401));
    }
}