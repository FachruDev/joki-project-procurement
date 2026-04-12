<?php

namespace App\Http\Middleware;

use App\VendorStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApprovedVendor
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(401);
        }

        $vendor = $user->vendor;

        if ($vendor === null || $vendor->status !== VendorStatus::Approved) {
            abort(403, 'Vendor account is not approved yet.');
        }

        return $next($request);
    }
}
