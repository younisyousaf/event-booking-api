<?php

namespace App\Http\Requests\Booking;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_id'     => ['required', 'integer', 'exists:events,id'],
            'seats_booked' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'event_id.exists'  => 'The selected event does not exist.',
            'seats_booked.min' => 'You must book at least 1 seat.',
        ];
    }

    /**
     * Additional business-rule validation after base rules pass.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) {
                return;
            }

            $event = Event::find($this->event_id);

            if (! $event) {
                return;
            }

            // Ensure the event hasn't already passed
            if ($event->event_datetime->isPast()) {
                $validator->errors()->add(
                    'event_id',
                    'Bookings cannot be made for past events.'
                );
            }

            // Ensure requested seats don't exceed available seats
            if ($this->seats_booked > $event->available_seats) {
                $validator->errors()->add(
                    'seats_booked',
                    "Only {$event->available_seats} seat(s) are available for this event."
                );
            }
        });
    }
}