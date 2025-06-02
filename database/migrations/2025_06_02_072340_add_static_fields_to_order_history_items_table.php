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
            $table->string('product_name')->nullable();
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
        });
    }

    public function down()
    {
        Schema::table('order_history_items', function (Blueprint $table) {
            $table->dropColumn(['product_name', 'tenant_name', 'shipping_cost', 'grand_total']);
        });
    }
};
