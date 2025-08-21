<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('product_name'); // snapshot
            $table->string('variant_name')->nullable(); // snapshot
            $table->string('product_sku'); // snapshot
            $table->decimal('price', 10, 2);
            $table->integer('quantity');
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();

            $table->index(['order_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
    }
};
