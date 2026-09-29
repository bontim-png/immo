<?php
namespace App\Middleware;

use App\Http\Request;
use App\Http\Response;

class AuthMiddleware
{
    public function handle(Request $request, callable $next)
    {
        // Check if user is logged in
        if (!isset($_SESSION['user']) || !$_SESSION['user']) {
            // Store intended URL for redirect after login
            $_SESSION['url.intended'] = current_url();
            
            if ($request->isAjax()) {
                return Response::make()->json(['error' => trans('auth.unauthenticated')], 401);
            }
            
            return Response::make()->redirect(route('login'));
        }

        // Optional: Check session validity
        if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== ($request->getUserAgent())) {
            // Session hijacking protection
            $_SESSION = [];
            session_destroy();
            
            if ($request->isAjax()) {
                return Response::make()->json(['error' => trans('auth.session_invalid')], 401);
            }
            
            return Response::make()->redirect(route('login'));
        }

        return $next();
    }
}
