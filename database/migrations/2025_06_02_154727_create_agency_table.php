<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agency', function (Blueprint $table) {
            $table->id('agencyId');
            $table->string('agency_name', 50);
            $table->string('agencyPassword', 15);
            $table->unsignedBigInteger('mcmcId')->nullable();
            $table->timestamps();
            $table->string('agencyUsername', 20)->nullable();

            // Add foreign key constraint
            $table->foreign('mcmcId')->references('mcmcId')->on('mcmc');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agency');
    }
};
