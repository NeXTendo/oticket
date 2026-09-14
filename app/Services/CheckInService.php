<?php

namespace App\Services;

use App\Models\CheckIn;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckInService
{
    /**
     * Result shape returned to the scanner UI:
     *  ['ok' => true,  'ticket' => Ticket]
     *  ['ok' => false, 'reason' => 'not_found' | 'already_used' | 'void']
     */
    public function scan(string $ticketCode, ?User $scannedBy = null, ?string $gate = null): array
    {
        return DB::transaction(function () use ($ticketCode, $scannedBy, $gate) {
            /** @var Ticket|null $ticket */
            $ticket = Ticket::where('ticket_code', $ticketCode)
                ->lockForUpdate()
                ->first();

            if (! $ticket) {
                return ['ok' => false, 'reason' => 'not_found'];
            }

            if ($ticket->status === 'used') {
                return ['ok' => false, 'reason' => 'already_used', 'ticket' => $ticket];
            }

            if ($ticket->status !== 'valid') {
                return ['ok' => false, 'reason' => 'void', 'ticket' => $ticket];
            }

            $ticket->update(['status' => 'used']);

            CheckIn::create([
                'ticket_id' => $ticket->id,
                'scanned_by' => $scannedBy?->id,
                'scanned_at' => now(),
                'gate' => $gate,
            ]);

            return ['ok' => true, 'ticket' => $ticket];
        });
    }
}
