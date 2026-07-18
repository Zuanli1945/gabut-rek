<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component {
};

?>

<div class="p-6 lg:p-8">
    <div class="mb-8">
        <h1 class="font-display text-3xl font-medium text-[var(--ink)]">Roles</h1>
        <p class="mt-1 font-body text-sm text-[var(--ink-muted)]">Manage user roles and permissions</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left font-body text-sm">
            <thead>
                <tr class="border-b border-[var(--border-hair)] text-[var(--ink-muted)]">
                    <th class="micro-label py-3 pr-4">Role</th>
                    <th class="micro-label py-3 pr-4">Guard</th>
                    <th class="micro-label py-3 pr-4 text-right">Users</th>
                </tr>
            </thead>
            <tbody>
                @foreach(\App\Models\Role::withCount('users')->get() as $role)
                    <tr class="border-b border-[var(--border-soft)] text-[var(--ink)]">
                        <td class="py-3 pr-4 font-medium">{{ $role->name }}</td>
                        <td class="py-3 pr-4 text-[var(--ink-muted)]">{{ $role->guard_name }}</td>
                        <td class="py-3 pr-4 text-right tabular-nums text-[var(--ink-muted)]">{{ $role->users_count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
