<?php

namespace App\Http\Middleware;

use App\Models\Booking;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBookingOwner
{
    /**
     * Ensure the authenticated user owns the booking being accessed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Booking|null $booking */
        $booking = $request->route('booking');

        if (! $booking instanceof Booking) {
            return $next($request);
        }

        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You are not authorised to access this booking.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}