<?php

namespace App\Http\Controllers;

use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class BookingController extends Controller
{
    /**
     * List the authenticated user's bookings.
     *
     * Query params:
     *   - booking_status : booked | cancelled
     *   - per_page       : integer (default 15)
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'booking_status' => ['nullable', 'string', 'in:booked,cancelled'],
            'per_page'       => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $bookings = $request->user()
            ->bookings()
            ->with('event.creator:id,name')
            ->when(
                $request->booking_status,
                fn ($q) => $q->where('booking_status', $request->booking_status)
            )
            ->orderByDesc('booking_date')
            ->paginate($request->integer('per_page', 15));

        return BookingResource::collection($bookings);
    }

    /**
     * Create a new booking.
     *
     * Uses a DB transaction + pessimistic lock to prevent race conditions
     * when multiple users try to book the last available seats simultaneously.
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $booking = DB::transaction(function () use ($request) {

            /** @var Event $event */
            $event = Event::lockForUpdate()->findOrFail($request->event_id);

            // Re-validate inside the transaction as a race condition guard
            if ($request->seats_booked > $event->available_seats) {
                abort(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    "Only {$event->available_seats} seat(s) are available."
                );
            }

            if ($event->event_datetime->isPast()) {
                abort(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    'Bookings cannot be made for past events.'
                );
            }

            // Decrement available seats atomically
            $event->decrement('available_seats', $request->seats_booked);

            return Booking::create([
                'user_id'        => $request->user()->id,
                'event_id'       => $event->id,
                'seats_booked'   => $request->seats_booked,
                'booking_status' => Booking::STATUS_BOOKED,
                'booking_date'   => now(),
            ]);
        });

        $booking->load('event.creator:id,name', 'user');

        return response()->json([
            'message' => 'Booking confirmed successfully.',
            'booking' => new BookingResource($booking),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show a single booking belonging to the authenticated user.
     */
    public function show(Request $request, Booking $booking): JsonResponse
    {
        $booking->load('event.creator:id,name', 'user');

        return response()->json([
            'booking' => new BookingResource($booking),
        ]);
    }

    /**
     * Cancel a booking and restore the event's available seats.
     */
    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->isCancelled()) {
            return response()->json([
                'message' => 'This booking has already been cancelled.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::transaction(function () use ($booking) {
            $seatsToRestore = $booking->seats_booked;

            $booking->update(['booking_status' => Booking::STATUS_CANCELLED]);

            // Restore available seats back to the event
            $booking->event()->lockForUpdate()->first()
                ->increment('available_seats', $seatsToRestore);
        });

        $booking->load('event.creator:id,name', 'user');

        return response()->json([
            'message' => 'Booking cancelled successfully.',
            'booking' => new BookingResource($booking),
        ]);
    }
}