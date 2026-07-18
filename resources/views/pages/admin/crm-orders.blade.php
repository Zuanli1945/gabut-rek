<?php

use App\Models\Order;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component {
    public string $statusFilter = '';

    #[Computed]
    public function orders()
    {
        $q = Order::with('user', 'items.variant.produk');
        if ($this->statusFilter) {
            $q->where('status', $this->statusFilter);
        }
        return $q->orderByDesc('created_at')->get();
    }
};

?>

<div class="p-6 lg:p-8">
    <div class="mb-8">
        <h1 class="font-display text-3xl font-medium text-[var(--ink)]">Orders</h1>
        <p class="mt-1 font-body text-sm text-[var(--ink-muted)]">{{ App\Models\Order::count() }} total orders</p>
    </div>

    <div class="mb-6">
        <select wire:model.live="statusFilter" class="rounded-[2px] border border-[var(--border-hair)] bg-[var(--cream)] px-3 py-2 font-body text-sm text-[var(--ink)] outline-none focus:ring-2 focus:ring-[var(--amber)]">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="paid">Paid</option>
            <option value="production">In Production</option>
            <option value="shipping">Shipping</option>
            <option value="delivered">Delivered</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>

    @php $orders = $this->orders; @endphp
    @if($orders->isEmpty())
        <div class="card flex flex-col items-center justify-center py-16 text-center">
            <p class="font-body text-sm text-[var(--ink-muted)]">No orders found</p>
        </div>
    @else
        <div class="table-wrap">
            <table class="w-full text-left font-body text-sm">
                <thead>
                    <tr class="border-b border-[var(--border-hair)] text-[var(--ink-muted)]">
                        <th class="micro-label py-3 pr-4">Order</th>
                        <th class="micro-label py-3 pr-4">Customer</th>
                        <th class="micro-label py-3 pr-4">Items</th>
                        <th class="micro-label py-3 pr-4 text-right">Total</th>
                        <th class="micro-label py-3 pr-4 text-center">Status</th>
                        <th class="micro-label py-3 pr-4">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $o)
                        <tr class="border-b border-[var(--border-soft)] text-[var(--ink)]">
                            <td class="py-3 pr-4 font-medium">#{{ $o->order_number }}</td>
                            <td class="py-3 pr-4 text-[var(--ink-muted)]">{{ $o->user->name }}</td>
                            <td class="py-3 pr-4 tabular-nums text-[var(--ink-muted)]">{{ $o->items->count() }}</td>
                            <td class="py-3 pr-4 text-right tabular-nums text-[var(--amber)]">Rp{{ number_format($o->total, 0, ',', '.') }}</td>
                            <td class="py-3 pr-4 text-center">
                                <span class="badge-amber text-[10px]">{{ $o->status }}</span>
                            </td>
                            <td class="py-3 pr-4 text-[var(--ink-muted)]">{{ $o->created_at->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
