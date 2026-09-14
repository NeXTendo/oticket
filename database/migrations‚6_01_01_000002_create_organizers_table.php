<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // account that owns/manages this organizer
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo_path')->nullable();      // R2 key
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('status')->default('pending'); // pending | verified | suspended
            $table->decimal('commission_rate', 5, 2)->default(5.00); // % e.g. 5.00 = 5%
            $table->string('tier')->default('starter');   // starter | pro | enterprise
            $table->string('payout_account')->nullable(); // mobile money / bank ref for Lipila disbursement
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizers');
    }
};
