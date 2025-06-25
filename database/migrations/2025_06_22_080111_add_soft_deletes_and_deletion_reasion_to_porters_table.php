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
        Schema::table('porters', function (Blueprint $table) {
            $table->text(column: 'deletion_reason')->nullable()->after('porter_isOnline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('porters', function (Blueprint $table) {
            //
        });
    }
};
