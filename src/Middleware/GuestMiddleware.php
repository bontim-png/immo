<?php
namespace App\Middleware;

use App\Http\Request;
use App\Http\Response;

class GuestMiddleware
{
    public function handle(Request $request, callable $next)
    {
        // Redirect if already logged in
        if (isset($_SESSION['user']) && $_SESSION['user']) {
            if ($request->isAjax()) {
                return Response::make()->json(['error' => trans('auth.already_logged_in')], 400);
            }
            
            return Response::make()->redirect(route('dashboard'));
        }

        return $next();
    }
}
