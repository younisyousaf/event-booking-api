<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'location',
        'event_datetime',
        'total_seats',
        'available_seats',
        'created_by',
    ];

    protected $casts = [
        'event_datetime'  => 'datetime',
        'total_seats'     => 'integer',
        'available_seats' => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeByDate(Builder $query, ?string $date): Builder
    {
        return $query->when($date, fn (Builder $q) =>
            $q->whereDate('event_datetime', $date)
        );
    }

    public function scopeByLocation(Builder $query, ?string $location): Builder
    {
        return $query->when($location, fn (Builder $q) =>
            $q->where('location', 'like', "%{$location}%")
        );
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('event_datetime', '>=', now());
    }
}
