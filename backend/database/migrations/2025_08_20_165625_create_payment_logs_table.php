<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('gateway');
            $table->string('event_type');
            $table->json('raw_payload');
            $table->string('signature')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->string('status');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'created_at']);
            $table->index(['gateway', 'event_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_logs');
    }
};