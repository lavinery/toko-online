<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('courier'); // jne, pos, tiki
            $table->string('service'); // REG, OKE, YES
            $table->decimal('cost', 10, 2);
            $table->integer('weight'); // gram
            $table->string('tracking_number')->nullable();
            $table->text('origin_address')->nullable();
            $table->text('destination_address');
            $table->json('shipping_data')->nullable(); // detail dari API kurir
            $table->enum('status', ['pending', 'picked_up', 'in_transit', 'delivered'])->default('pending');
            $table->datetime('shipped_at')->nullable();
            $table->datetime('estimated_delivery')->nullable();
            $table->datetime('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['order_id']);
            $table->index(['tracking_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipments');
    }
};
