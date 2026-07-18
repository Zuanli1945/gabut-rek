<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Roles — owner only
    Route::middleware('role:owner')->group(function () {
        Route::livewire('/settings/roles', 'pages::admin.roles-index')->name('settings.roles');
    });

    // Production & Inventory — owner & produksi
    Route::middleware('role:owner,produksi')->group(function () {
        Route::livewire('/inventory/materials', 'pages::admin.inventory-materials')->name('inventory.materials');
    });

    // CRM — owner & cs
    Route::middleware('role:owner,cs')->group(function () {
        Route::livewire('/crm/orders', 'pages::admin.crm-orders')->name('crm.orders');
    });
});
