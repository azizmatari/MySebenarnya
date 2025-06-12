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
            $table->foreignId('mcmcId')->nullable()->constrained('mcmc', 'mcmcId')->onDelete('set null');
            $table->string('agencyUsername', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agency');
    }
};
