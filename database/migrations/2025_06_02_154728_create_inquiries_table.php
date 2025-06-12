<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiry', function (Blueprint $table) {
            $table->id('inquiryId');
            $table->unsignedBigInteger('userId');
            $table->string('title', 30);
            $table->text('description');
            $table->enum('final_status', ['Under Investigation', 'True', 'Fake', 'Rejected'])->nullable();
            $table->date('submission_date');
            $table->string('evidenceUrl', 150)->nullable();
            $table->string('evidenceFileUrl', 150)->nullable();
            $table->timestamps();
            $table->string('evidenceUrl', 150)->nullable();
            $table->string('evidenceFileUrl', 150)->nullable();
            $table->unsignedBigInteger('agencyId')->nullable();

            // Add indexes for better performance
            $table->index('userId', 'idx_inquiry_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiry');
    }
};
