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
            $table->unsignedBigInteger('agencyId');
            $table->string('agencyStaffName', 50);
            $table->string('agencyStaffEmail', 50);
            $table->timestamps();

            // Add index and foreign key
            $table->index('agencyId', 'idx_agencystaff_agency');
            $table->foreign('agencyId')->references('agencyId')->on('agency');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agencystaff');
    }
};
