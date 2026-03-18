<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'          => ['sometimes', 'required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'location'       => ['sometimes', 'required', 'string', 'max:255'],
            'event_datetime' => ['sometimes', 'required', 'date', 'after:now'],
            'total_seats'    => ['sometimes', 'required', 'integer', 'min:1', 'max:100000'],
        ];
    }

    public function messages(): array
    {
        return [
            'event_datetime.after' => 'The event date & time must be a future date.',
        ];
    }
}