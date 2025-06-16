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
            $table->string('officer_name',50)->nullable();
            $table->enum('status', ['Under Investigation', 'True', 'Fake', 'Rejected']);
            $table->text('status_comment')->nullable();
            $table->unsignedBigInteger('updated_by_agent_id')->nullable(); // Foreign key to agency table
            $table->timestamps(); // This adds created_at and updated_at fields
            $table->string('supporting_document')->nullable();
            $table->string('message')->nullable();
            // Add foreign key constraints
            $table->foreign('inquiryId')->references('inquiryId')->on('inquiry');
            $table->foreign('agencyId')->references('agencyId')->on('agency');
            $table->foreign('updated_by_agent_id')->references('agencyId')->on('agency');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquirystatushistory');
    }
};