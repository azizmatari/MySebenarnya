<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_users', function (Blueprint $table) {
            $table->id('userId'); // match ERD field name
            $table->string('userName');
            $table->string('userEmail')->unique(); // fix typo, make it unique
            $table->string('userUsername')->unique();
            $table->string('userPassword');
            $table->string('userContact_number')->nullable(); // optional during register
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_users');
    }
};
