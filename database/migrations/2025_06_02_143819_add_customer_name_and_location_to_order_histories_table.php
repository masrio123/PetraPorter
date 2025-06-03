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
        Schema::table('order_histories', function (Blueprint $table) {
            $table->string('customer_name')->after('customer_id')->nullable();
            $table->string('tenant_location_name')->after('customer_name')->nullable();
        });
    }

    public function down()
    {
        Schema::table('order_histories', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'tenant_location_name']);
        });
    }
};
