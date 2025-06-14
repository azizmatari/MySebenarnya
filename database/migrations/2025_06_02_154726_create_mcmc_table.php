<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mcmc', function (Blueprint $table) {
            $table->id('mcmcId');
            $table->string('mcmcName', 50);
            $table->string('mcmcEmail', 50);
            $table->string('mcmcUsername', 20);
            $table->string('mcmcPassword', 15);
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mcmc');
    }
};
