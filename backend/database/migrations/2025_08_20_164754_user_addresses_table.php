<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('label')->default('rumah'); // rumah, kantor, dll
            $table->string('name');
            $table->string('phone');
            $table->text('address');
            $table->string('province');
            $table->string('city');
            $table->string('subdistrict');
            $table->string('postal_code');
            $table->integer('province_id')->nullable(); // untuk RajaOngkir
            $table->integer('city_id')->nullable();
            $table->integer('subdistrict_id')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_addresses');
    }
};
