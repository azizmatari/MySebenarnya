<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidence', function (Blueprint $table) {
            $table->id('evidenceId');
            $table->foreignId('inquiryId')->constrained('inquiry', 'inquiryId')->onDelete('cascade');
            $table->string('evidenceType', 15);
            $table->string('evidenceUrl', 50);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence');
    }
};
