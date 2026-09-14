<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            $table->string('provider')->default('lipila');
            $table->string('provider_reference')->nullable()->index(); // Lipila referenceId
            $table->string('method')->nullable(); // mtn_momo | airtel_money | zamtel_kwacha | card
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('ZMW');

            // initiated | pending | successful | failed | cancelled
            $table->string('status')->default('initiated');

            $table->json('gateway_payload')->nullable();  // raw request sent to Lipila
            $table->json('gateway_response')->nullable(); // raw webhook/callback payload
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
