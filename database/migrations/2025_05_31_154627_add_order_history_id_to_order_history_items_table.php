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
        Schema::table('order_history_items', function (Blueprint $table) {
            $table->foreignId('order_history_id')->after('grand_total')->constrained('order_histories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_history_items', function (Blueprint $table) {
            //
        });
    }
};
