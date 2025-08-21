<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed_amount', 'free_shipping']);
            $table->decimal('value', 10, 2); // persen atau nominal
            $table->decimal('minimum_amount', 10, 2)->nullable();
            $table->decimal('maximum_discount', 10, 2)->nullable();
            $table->integer('usage_limit')->nullable(); // total usage
            $table->integer('used_count')->default(0);
            $table->integer('usage_limit_per_customer')->nullable();
            $table->datetime('starts_at');
            $table->datetime('expires_at');
            $table->boolean('is_active')->default(true);
            $table->json('conditions')->nullable(); // kategori, produk, user specific
            $table->timestamps();

            $table->index(['code', 'is_active']);
            $table->index(['starts_at', 'expires_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('vouchers');
    }
};
