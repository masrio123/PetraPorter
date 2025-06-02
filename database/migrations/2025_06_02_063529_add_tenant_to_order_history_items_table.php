<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('order_history_items', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('product_id');
            $table->string('tenant_name')->nullable()->after('tenant_id');
        });
    }

    public function down()
    {
        Schema::table('order_history_items', function (Blueprint $table) {
            $table->dropColumn(['tenant_id', 'tenant_name']);
        });
    }
};
