<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->comment('email/wa/both');
            $table->string('trigger_event')->comment('order_placed/cart_abandoned/manual');
            $table->string('status')->default('draft')->comment('draft/active/paused/completed');
            $table->json('segment_filter')->nullable();
            $table->timestamps();
        });

        Schema::create('campaign_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('delay_hours');
            $table->string('channel')->comment('email/wa');
            $table->string('subject')->nullable();
            $table->text('message');
            $table->unsignedTinyInteger('order');
            $table->timestamps();
        });

        Schema::create('referral_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20)->unique();
            $table->decimal('commission_percent', 5, 2)->default(10);
            $table->unsignedInteger('total_clicks')->default(0);
            $table->unsignedInteger('total_conversions')->default(0);
            $table->decimal('total_commission_earned', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('referral_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_link_id')->constrained()->cascadeOnDelete();
            $table->foreignId('referred_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('commission_amount', 12, 2);
            $table->string('status')->default('pending')->comment('pending/paid');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_conversions');
        Schema::dropIfExists('referral_links');
        Schema::dropIfExists('campaign_sequences');
        Schema::dropIfExists('campaigns');
    }
};
