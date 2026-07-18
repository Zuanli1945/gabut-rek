<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('total_spent', 12, 2)->default(0);
            $table->unsignedInteger('total_orders')->default(0);
            $table->timestamp('last_order_at')->nullable();
            $table->json('preferred_scent_families')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('abandoned_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('cart_data');
            $table->decimal('total', 12, 2);
            $table->string('status')->default('pending')->comment('pending/recovered/lost');
            $table->timestamp('abandoned_at');
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamp('wa_sent_at')->nullable();
            $table->timestamp('recovered_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abandoned_carts');
        Schema::dropIfExists('customer_profiles');
    }
};
