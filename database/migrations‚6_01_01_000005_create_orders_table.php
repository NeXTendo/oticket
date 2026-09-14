<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // e.g. ORD-8F72A91C
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();

            $table->decimal('subtotal', 12, 2);              // sum of ticket prices
            $table->decimal('platform_commission', 12, 2);   // organizer-side commission amount
            $table->decimal('processing_fee', 12, 2);        // Lipila fee passed through
            $table->decimal('total', 12, 2);                 // what the customer actually pays

            // pending_payment | paid | expired | cancelled | refunded
            $table->string('status')->default('pending_payment');

            $table->dateTime('reserved_until')->nullable(); // mirrors the Redis hold expiry
            $table->string('hold_reference')->nullable(); // ties this order to its Redis inventory hold
            $table->timestamps();

            $table->index(['status', 'reserved_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
