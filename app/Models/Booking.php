<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'seats_booked',
        'status',
        'booking_date',
    ];

    protected $casts = [
        'seats_booked' => 'integer',
        'booking_date' => 'datetime',
    ];

    // Constants
    const STATUS_BOOKED = 'booked';

    const STATUS_CANCELLED = 'cancelled';

    /**
     * Relationships
    */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Helpers
    */
    public function isBooked(): bool
    {
        return $this->status === self::STATUS_BOOKED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }
}
