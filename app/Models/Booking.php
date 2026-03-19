<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'event_id',
        'seats_booked',
        'booking_status',
        'booking_date',
    ];

    protected $casts = [
        'seats_booked' => 'integer',
        'booking_date' => 'datetime',
    ];

    const STATUS_BOOKED    = 'booked';
    const STATUS_CANCELLED = 'cancelled';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function isBooked(): bool
    {
        return $this->booking_status === self::STATUS_BOOKED;
    }

    public function isCancelled(): bool
    {
        return $this->booking_status === self::STATUS_CANCELLED;
    }
}
