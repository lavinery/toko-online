<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // INV-20240820-001
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'expired', 'refunded'])->default('pending');
            $table->enum('shipping_status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->string('payment_gateway')->nullable(); // midtrans, xendit
            $table->string('payment_reference')->nullable(); // snap_token, external_id
            $table->json('payment_data')->nullable(); // response dari gateway
            $table->text('shipping_address');
            $table->text('notes')->nullable();
            $table->string('idempotency_key')->unique()->nullable();
            $table->datetime('paid_at')->nullable();
            $table->datetime('shipped_at')->nullable();
            $table->datetime('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['payment_status']);
            $table->index(['shipping_status']);
            $table->index(['code']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};