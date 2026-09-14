<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->get('category');

        $events = Event::query()
            ->published()
            ->upcoming()
            ->with(['organizer', 'ticketTypes'])
            ->when($category && $category !== 'all', fn ($q) => $q->where('category', $category))
            ->orderBy('starts_at')
            ->paginate(12);

        $featuredEvent = Event::query()
            ->published()
            ->upcoming()
            ->with(['organizer', 'ticketTypes'])
            ->first();

        return view('welcome', [
            'events' => $events,
            'featuredEvent' => $featuredEvent,
            'activeCategory' => $category ?? 'all',
        ]);
    }

    public function myTickets()
    {
        $tickets = Ticket::query()
            ->where('user_id', auth()->id())
            ->with(['ticketType', 'event'])
            ->orderByDesc('created_at')
            ->get();

        return view('my-tickets', compact('tickets'));
    }

    public function orderConfirmation(Order $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);
        abort_unless($order->status === 'paid', 404);

        return view('orders.confirmation', compact('order'));
    }

    public function showEvent(Event $event)
    {
        abort_unless($event->isPublished(), 404);

        $event->load(['organizer', 'ticketTypes' => fn ($q) => $q->where('is_active', true)->orderBy('price')]);

        return view('events.show', compact('event'));
    }
}
