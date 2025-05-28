<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('porters', function (Blueprint $table) {
            $table->id();
            $table->string('porter_name');
            $table->string('porter_nrp');
            $table->foreignId('bank_user_id')->constrained('bank_users');
            $table->foreignId('department_id')->constrained('departments');
            $table->string('porter_rating')->default('null');
            $table->boolean('porter_isOnline')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('porters');
    }
};
