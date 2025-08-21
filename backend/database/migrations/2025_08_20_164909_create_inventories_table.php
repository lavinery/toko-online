<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0); // untuk mencegah oversell
            $table->integer('minimum_stock')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'product_variant_id']);
            $table->index(['product_id', 'quantity']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventories');
    }
};
