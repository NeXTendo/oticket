<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category')->nullable(); // concert, sport, theatre, conference...
            $table->string('cover_image_path')->nullable(); // R2 key

            $table->string('venue_name')->nullable();
            $table->string('venue_address')->nullable();
            $table->decimal('venue_lat', 10, 7)->nullable();
            $table->decimal('venue_lng', 10, 7)->nullable();

            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();

            // draft -> published -> ongoing -> completed -> cancelled
            $table->string('status')->default('draft');

            $table->timestamps();

            $table->index(['status', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
