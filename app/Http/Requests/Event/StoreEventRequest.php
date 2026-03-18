<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'location'       => ['required', 'string', 'max:255'],
            'event_datetime' => ['required', 'date', 'after:now'],
            'total_seats'    => ['required', 'integer', 'min:1', 'max:100000'],
        ];
    }

    public function messages(): array
    {
        return [
            'event_datetime.after' => 'The event date & time must be a future date.',
            'total_seats.min'      => 'There must be at least 1 seat.',
        ];
    }
}