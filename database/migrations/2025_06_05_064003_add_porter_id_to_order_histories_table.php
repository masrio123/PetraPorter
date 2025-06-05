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
        Schema::table('order_histories', function (Blueprint $table) {
            $table->foreignId('porter_id')->after('shipping_cost')->constrained('porters')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_histories', function (Blueprint $table) {
            //
        });
    }
};
