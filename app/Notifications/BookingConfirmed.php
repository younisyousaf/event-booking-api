<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Booking $booking
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $event = $this->booking->event;
        $user  = $notifiable;

        return (new MailMessage())
            ->subject("Booking Confirmed – {$event->title}")
            ->greeting("Hello {$user->name},")
            ->line("Your booking for **{$event->title}** has been confirmed.")
            ->line("📍 **Location:** {$event->location}")
            ->line("📅 **Date & Time:** {$event->event_datetime->toDayDateTimeString()}")
            ->line("🪑 **Seats Booked:** {$this->booking->seats_booked}")
            ->line("🔖 **Booking Reference:** #{$this->booking->id}")
            ->line('Thank you for using the Event Booking System!')
            ->salutation('Best regards, Event Booking Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'booking_id'   => $this->booking->id,
            'event_title'  => $this->booking->event->title,
            'seats_booked' => $this->booking->seats_booked,
            'booking_date' => $this->booking->booking_date->toIso8601String(),
        ];
    }
}