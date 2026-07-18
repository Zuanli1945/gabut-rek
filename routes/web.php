<?php

use Illuminate\Support\Facades\Route;

Route::view("/", "welcome")->name("home");

Route::middleware(["auth", "verified"])->group(function () {
    Route::livewire("dashboard", "pages::dashboard")->name("dashboard");

    Route::livewire("materials", "pages::materials-index")->name(
        "materials.index",
    );
    Route::livewire("materials/create", "pages::materials-create")->name(
        "materials.create",
    );
    Route::livewire("materials/{id}/edit", "pages::materials-edit")->name(
        "materials.edit",
    );

    Route::livewire("formulas", "pages::formulas-index")->name(
        "formulas.index",
    );
    Route::livewire("formulas/create", "pages::formulas-create")->name(
        "formulas.create",
    );
    Route::livewire("formulas/{id}", "pages::formulas-show")->name(
        "formulas.show",
    );
    Route::livewire("formulas/{id}/edit", "pages::formulas-edit")->name(
        "formulas.edit",
    );

    Route::livewire("products", "pages::products-index")->name(
        "products.index",
    );
    Route::livewire("products/create", "pages::products-create")->name(
        "products.create",
    );
    Route::livewire("products/{id}/edit", "pages::products-edit")->name(
        "products.edit",
    );
});

require __DIR__ . "/settings.php";
