<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('name'); // M, L, XL atau Merah, Biru
            $table->string('sku')->unique();
            $table->decimal('price_adjustment', 8, 2)->default(0); // + atau - dari harga dasar
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'sort_order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_variants');
    }
};
