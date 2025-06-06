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
            $table->dropForeign(['order_id']); // drop FK constraint
            $table->dropColumn('order_id');    // drop kolom
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
