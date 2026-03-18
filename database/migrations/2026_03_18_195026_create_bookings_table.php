<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('event_id')
                  ->constrained('events')
                  ->cascadeOnDelete();
            $table->unsignedInteger('seats_booked');
            $table->enum('booking_status', ['booked', 'cancelled'])->default('booked');
            $table->dateTime('booking_date');
            $table->timestamps();

            $table->index(['user_id', 'event_id', 'booking_status']);
            $table->index('booking_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
