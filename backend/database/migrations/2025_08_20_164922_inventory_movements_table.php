<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['in', 'out', 'adjustment']);
            $table->integer('quantity'); // bisa negatif untuk 'out'
            $table->integer('previous_quantity');
            $table->string('reason');
            $table->string('reference_type')->nullable(); // order, adjustment, return
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['inventory_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_movements');
    }
};
