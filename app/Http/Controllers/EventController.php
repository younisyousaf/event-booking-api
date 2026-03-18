<?php

namespace App\Http\Controllers;

use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class EventController extends Controller
{
    /**
     * List all events with optional date/location filters.
     *
     * Query params:
     *   - date      : YYYY-MM-DD
     *   - location  : partial string
     *   - upcoming  : boolean (1 / true) — default true; pass 0 to show all
     *   - per_page  : integer (default 15)
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'date'     => ['nullable', 'date_format:Y-m-d'],
            'location' => ['nullable', 'string', 'max:255'],
            'upcoming' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $events = Event::query()
            ->with('creator:id,name')
            ->byDate($request->date)
            ->byLocation($request->location)
            ->when(
                filter_var($request->input('upcoming', true), FILTER_VALIDATE_BOOLEAN),
                fn ($q) => $q->upcoming()
            )
            ->orderBy('event_datetime')
            ->paginate($request->integer('per_page', 15));

        return EventResource::collection($events);
    }

    /**
     * Create a new event.
     */
    public function store(StoreEventRequest $request): JsonResponse
    {
        $event = Event::create([
            'title'           => $request->title,
            'description'     => $request->description,
            'location'        => $request->location,
            'event_datetime'  => $request->event_datetime,
            'total_seats'     => $request->total_seats,
            'available_seats' => $request->total_seats,
            'created_by'      => $request->user()->id,
        ]);

        $event->load('creator:id,name');

        return response()->json([
            'message' => 'Event created successfully.',
            'event'   => new EventResource($event),
        ], Response::HTTP_CREATED);
    }

    /**
     * Return a single event.
     */
    public function show(Event $event): JsonResponse
    {
        $event->load('creator:id,name');

        return response()->json([
            'event' => new EventResource($event),
        ]);
    }

    /**
     * Update an existing event.
     * Only the event creator is allowed to update.
     */
    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
        if ($event->created_by !== $request->user()->id) {
            return response()->json([
                'message' => 'You are not authorised to update this event.',
            ], Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        // If total_seats is being updated, adjust available_seats by the difference
        if (isset($data['total_seats'])) {
            $seatsBooked          = $event->total_seats - $event->available_seats;
            $data['available_seats'] = max(0, $data['total_seats'] - $seatsBooked);
        }

        $event->update($data);
        $event->load('creator:id,name');

        return response()->json([
            'message' => 'Event updated successfully.',
            'event'   => new EventResource($event),
        ]);
    }

    /**
     * Delete an event.
     * Only the event creator is allowed to delete.
     */
    public function destroy(Request $request, Event $event): JsonResponse
    {
        if ($event->created_by !== $request->user()->id) {
            return response()->json([
                'message' => 'You are not authorised to delete this event.',
            ], Response::HTTP_FORBIDDEN);
        }

        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully.',
        ]);
    }
}