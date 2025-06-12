<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agencystaff', function (Blueprint $table) {
            $table->id('agencyStaffId');
            $table->foreignId('agencyId')->constrained('agency', 'agencyId')->onDelete('cascade');
            $table->string('agencyStaffName', 50);
            $table->string('agencyStaffEmail', 50);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agencystaff');
    }
};
