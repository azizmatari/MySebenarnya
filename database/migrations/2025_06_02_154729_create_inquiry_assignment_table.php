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
            $table->foreignId('inquiryId')->constrained('inquiry', 'inquiryId')->onDelete('cascade');
            $table->foreignId('agencyId')->constrained('agency', 'agencyId')->onDelete('cascade');
            $table->text('comments')->nullable();
            $table->boolean('isRejected');
            $table->text('rejectedReason')->nullable();
            $table->foreignId('mcmcId')->nullable()->constrained('mcmc', 'mcmcId')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiryassignment');
    }
};
