<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mcmcstaff', function (Blueprint $table) {
            $table->increments('mcmcId');
            $table->string('mcmcName', 50);
            $table->string('mcmcEmail', 50);
            $table->string('mcmcUsername', 20);
            $table->string('mcmcPassword', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mcmcstaff');
    }
};