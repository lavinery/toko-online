<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('source'); // midtrans, xendit, shipper
            $table->string('event_type');
            $table->json('payload');
            $table->enum('status', ['pending', 'processing', 'success', 'failed'])->default('pending');
            $table->integer('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->datetime('processed_at')->nullable();
            $table->timestamps();

            $table->index(['source', 'status']);
            $table->index(['created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('webhooks');
    }
};
