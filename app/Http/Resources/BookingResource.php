<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'seats_booked' => $this->seats_booked,
            'status'       => $this->status,
            'booking_date' => $this->booking_date->toIso8601String(),
            'event'        => new EventResource($this->whenLoaded('event')),
            'user'         => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
            ],
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}