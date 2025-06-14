<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiryassignment', function (Blueprint $table) {
            $table->id('assignmentId');
            $table->unsignedBigInteger('inquiryId');
            $table->unsignedBigInteger('agencyId');
            $table->text('comments')->nullable();
            $table->boolean('isRejected');
            $table->text('rejectedReason')->nullable();
            $table->unsignedBigInteger('mcmcId')->nullable();

            // Add indexes for better performance
            $table->index('inquiryId', 'idx_assignment_inquiry');

            // Add foreign key constraints
            $table->foreign('inquiryId')->references('inquiryId')->on('inquiry');
            $table->foreign('agencyId')->references('agencyId')->on('agency');
            $table->foreign('mcmcId')->references('mcmcId')->on('mcmc');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiryassignment');
    }
};
