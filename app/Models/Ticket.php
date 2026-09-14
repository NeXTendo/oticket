<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_code', 'order_item_id', 'event_id', 'ticket_type_id',
        'user_id', 'holder_name', 'pdf_path', 'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket) {
            $ticket->ticket_code ??= 'TKT-'.strtoupper(Str::random(8));
        });
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }

    public function isValid(): bool
    {
        return $this->status === 'valid';
    }

    /**
     * The payload encoded into the QR code. Kept short and signed so the
     * scanner can verify authenticity without a DB round trip first,
     * then confirm/consume it against the DB.
     */
    public function qrPayload(): string
    {
        return $this->ticket_code;
    }
}
