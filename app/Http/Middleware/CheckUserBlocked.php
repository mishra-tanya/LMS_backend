<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\Block;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ApiResponse;

class CheckUserBlocked
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user) {
            $block = Block::where('user_id', $user->id)->first();
            if ($block) {
                return ApiResponse::unauthorized(
                    "Your account is blocked: " . $block->reason
                );
            }
        }

        return $next($request);
    }
}
