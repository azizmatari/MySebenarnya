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
            $table->string('agencyPassword', 70);
            $table->unsignedBigInteger('mcmcId')->nullable(); // FIXED: must match mcmc table type
            $table->string('agencyUsername', 20)->nullable();
            $table->string('profile_picture')->nullable();

            // Add foreign key constraint (types must match exactly)
            $table->foreign('mcmcId')->references('mcmcId')->on('mcmc');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agency');
    }
};


