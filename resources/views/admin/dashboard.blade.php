<x-layouts::app.sidebar :title="__('Admin')">
    <div class="p-6 lg:p-8">
        <div class="mb-8">
            <h1 class="font-display text-3xl font-medium text-[var(--ink)]">Admin Dashboard</h1>
            <p class="mt-1 font-body text-sm text-[var(--ink-muted)]">Ventura of Scent — Operations</p>
        </div>

        @php
            $lowStock = \App\Models\Material::where('stock_saat_ini', '>', 0)
                ->whereColumn('stock_saat_ini', '<', 'threshold_stock')
                ->count();
            $stats = [
                ['label' => 'Materials', 'count' => \App\Models\Material::count(), 'color' => 'text-[var(--ink)]'],
                ['label' => 'Active Orders', 'count' => \App\Models\Order::whereIn('status', ['pending', 'paid', 'production'])->count(), 'color' => 'text-[var(--amber)]'],
                ['label' => 'Active Batches', 'count' => \App\Models\BatchProduction::where('status', 'in_progress')->count(), 'color' => 'text-[var(--sage)]'],
                ['label' => 'Low Stock', 'count' => $lowStock, 'color' => 'text-[var(--terracotta)]'],
            ];
        @endphp

        <div class="mb-8 grid gap-4 grid-cols-2 lg:grid-cols-4">
            @foreach($stats as $s)
                <div class="card">
                    <p class="micro-label mb-1">{{ $s['label'] }}</p>
                    <p class="font-display text-2xl font-medium {{ $s['color'] }} tabular-nums">{{ $s['count'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="card">
                <p class="micro-label mb-4">Quick Links</p>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('admin.inventory.materials') }}" class="btn-ghost text-sm no-underline text-center">Inventory</a>
                    <a href="{{ route('admin.crm.orders') }}" class="btn-ghost text-sm no-underline text-center">Orders</a>
                    <a href="{{ route('admin.settings.roles') }}" class="btn-ghost text-sm no-underline text-center">Roles</a>
                </div>
            </div>
            <div class="card">
                <p class="micro-label mb-4">Role-based Access</p>
                <p class="font-body text-sm text-[var(--ink-muted)]">
                    @auth
                        Your roles: {{ auth()->user()->roles->pluck('name')->implode(', ') ?: 'Customer' }}
                    @endauth
                </p>
                <div class="mt-3 space-y-1">
                    <p class="font-body text-xs text-[var(--ink-muted)]">Owner: Full access to all modules</p>
                    <p class="font-body text-xs text-[var(--ink-muted)]">Produksi: Formula, Inventory, Production</p>
                    <p class="font-body text-xs text-[var(--ink-muted)]">CS: Orders, Customers, Abandoned Carts</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app.sidebar>
