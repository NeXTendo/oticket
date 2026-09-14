# Route additions for the checkout flow

Add to routes/web.php, inside your `auth` middleware group where relevant:

```php
use App\Livewire\TicketSelector;
use App\Livewire\Checkout;
use App\Models\Order;

// Public â€” anyone can view an event and pick tickets
Route::get('/events/{event:slug}', TicketSelector::class)->name('events.show');

// Requires login â€” TicketSelector redirects to /login if needed before
// creating the order, so these live behind auth for defense in depth
Route::middleware('auth')->group(function () {
    Route::get('/checkout/{order:order_number}', Checkout::class)->name('checkout');

    Route::get('/orders/{order:order_number}/confirmation', function (Order $order) {
        abort_if($order->user_id !== auth()->id(), 403);
        abort_unless($order->status === 'paid', 404);

        return view('orders.confirmation', compact('order'));
    })->name('orders.confirmation');
});
```

Note the route-model binding uses `slug` for events and `order_number` for
orders (not the numeric `id`) â€” add this to `Event.php` and `Order.php`
if you haven't already:

```php
// In app/Models/Event.php
public function getRouteKeyName(): string
{
    return 'slug';
}

// In app/Models/Order.php
public function getRouteKeyName(): string
{
    return 'order_number';
}
```

This keeps ticket/order URLs from leaking sequential database IDs.

