<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('stock');
            $table->json('scent_family_tags')->nullable()->after('is_active');
            $table->text('description_html')->nullable()->after('scent_family_tags');
            $table->json('images')->nullable()->after('description_html');
            $table->boolean('is_pre_order')->default(false)->after('images');
            $table->unsignedInteger('pre_order_quota')->nullable()->after('is_pre_order');
            $table->boolean('is_sample_available')->default(false)->after('pre_order_quota');
            $table->decimal('sample_price', 10, 2)->nullable()->after('is_sample_available');
            $table->decimal('sample_volume_ml', 5, 2)->nullable()->after('sample_price');
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained()->cascadeOnDelete();
            $table->string('name')->comment('e.g. 30ml, 50ml, Sample 2ml');
            $table->decimal('volume_ml', 5, 2);
            $table->decimal('price', 12, 2);
            $table->decimal('stock', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->string('status')->default('pending')->comment('pending/paid/production/shipping/delivered/cancelled');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('price_per_unit', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->index('order_id');
        });

        Schema::create('pre_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('slot_number');
            $table->string('status')->default('waiting')->comment('waiting/fulfilled/cancelled');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_orders');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('product_variants');

        Schema::table('produks', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'scent_family_tags', 'description_html', 'images', 'is_pre_order', 'pre_order_quota', 'is_sample_available', 'sample_price', 'sample_volume_ml']);
        });
    }
};
