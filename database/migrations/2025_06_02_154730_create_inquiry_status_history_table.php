<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquirystatushistory', function (Blueprint $table) {
            $table->id('status_id');
            $table->unsignedBigInteger('inquiryId');
            $table->unsignedBigInteger('agencyId');
            $table->enum('status', ['Under Investigation', 'True', 'Fake', 'Rejected']);
            $table->text('status_comment')->nullable();

            // Add foreign key constraints
            $table->foreign('inquiryId')->references('inquiryId')->on('inquiry');
            $table->foreign('agencyId')->references('agencyId')->on('agency');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquirystatushistory');
    }
};
