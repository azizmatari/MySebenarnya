<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publicuser', function (Blueprint $table) {
            $table->increments('userId');
            $table->string('userName', 50);
            $table->string('userEmail', 50);
            $table->string('userPassword', 255);
            $table->string('userContact_number', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publicuser');
    }
};