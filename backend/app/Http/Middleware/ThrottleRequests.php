<?php
// app/Http/Middleware/ThrottleRequests.php (Rate limiting)
namespace App\Http\Middleware;

use Illuminate\Routing\Middleware\ThrottleRequests as BaseThrottleRequests;

class ThrottleRequests extends BaseThrottleRequests
{
    protected function resolveRequestSignature($request)
    {
        if ($user = $request->user()) {
            return sha1($user->id);
        }

        return sha1($request->ip());
    }
}
