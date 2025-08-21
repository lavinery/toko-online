<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('compare_price', 10, 2)->nullable();
            $table->integer('weight')->default(0); // gram
            $table->string('dimensions')->nullable(); // PxLxT dalam cm
            $table->enum('status', ['active', 'inactive', 'draft'])->default('active');
            $table->boolean('is_featured')->default(false);
            $table->json('meta_data')->nullable(); // SEO, specs, dll
            $table->timestamps();

            $table->index(['status', 'is_featured']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
