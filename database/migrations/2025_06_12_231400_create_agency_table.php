<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agency', function (Blueprint $table) {
            $table->increments('agencyId');
            $table->string('agency_name', 50);
            $table->string('agencyPassword', 255);
            $table->integer('mcmcId')->unsigned()->nullable();
            $table->string('agencyUsername', 20)->nullable();
            $table->timestamps();

            $table->foreign('mcmcId')->references('mcmcId')->on('mcmcstaff');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agency');
    }
};