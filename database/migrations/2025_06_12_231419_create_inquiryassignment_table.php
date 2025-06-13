<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiryassignment', function (Blueprint $table) {
            $table->increments('assignmentId');
            $table->integer('inquiryId')->unsigned();
            $table->integer('agencyId')->unsigned();
            $table->boolean('isRejected');
            $table->text('comments')->nullable();
            $table->text('rejectedReason')->nullable();
            $table->integer('mcmcId')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('inquiryId')->references('inquiryId')->on('inquiry');
            $table->foreign('agencyId')->references('agencyId')->on('agency');
            $table->foreign('mcmcId')->references('mcmcId')->on('mcmcstaff');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiryassignment');
    }
};