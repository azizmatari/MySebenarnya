<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquirystatushistory', function (Blueprint $table) {
            $table->increments('status_id');
            $table->integer('inquiryId')->unsigned();
            $table->integer('agencyId')->unsigned();
            $table->enum('status', ['Under Investigation','True','Fake','Rejected']);
            $table->text('status_comment')->nullable();

            $table->foreign('inquiryId')->references('inquiryId')->on('inquiry');
            $table->foreign('agencyId')->references('agencyId')->on('agency');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquirystatushistory');
    }
};