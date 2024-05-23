<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsUserVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user;
        if ($user && !$user->is_email_verified)
            return response()->json([
                "status" => false,
                "message" => "فشل العملية",
                "errors" => ["من فضلك قم بتفعيل حسابك اولا"],
                "data" => [],
                "notes" => []
            ]);

        return $next($request);
    }
}
