<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'description'     => $this->description,
            'location'        => $this->location,
            'event_datetime'  => $this->event_datetime->toIso8601String(),
            'total_seats'     => $this->total_seats,
            'available_seats' => $this->available_seats,
            'is_sold_out'     => $this->available_seats === 0,
            'created_by' => $this->creator ? [
                'id'   => $this->creator->id,
                'name' => $this->creator->name,
            ] : null,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}