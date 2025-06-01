<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderHistoryItemsTable extends Migration
{
    public function up()
    {
        Schema::create('order_history_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_history_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->integer('quantity')->default(1);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);
            $table->timestamps();

            // Foreign keys
            $table->foreign('order_history_id')->references('id')->on('order_histories')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_history_items');
    }
}
