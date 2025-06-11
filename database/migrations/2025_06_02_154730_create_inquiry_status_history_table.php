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
            $table->foreignId('inquiryId')->constrained('inquiry', 'inquiryId')->onDelete('cascade');
            $table->foreignId('agencyId')->constrained('agency', 'agencyId')->onDelete('cascade');
            $table->enum('status', ['Under Investigation', 'True', 'Fake', 'Rejected']);
            $table->text('status_comment')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquirystatushistory');
    }
};
