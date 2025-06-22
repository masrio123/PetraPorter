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
         Schema::table('products', function (Blueprint $table) {
            // 1. Hapus foreign key constraint yang lama terlebih dahulu.
            //    Laravel biasanya menamainya 'products_tenant_id_foreign'.
            $table->dropForeign(['tenant_id']);

            // 2. Tambahkan kembali foreign key dengan aturan baru.
            $table->foreign('tenant_id')
                  ->references('id')->on('tenants') // Merujuk ke kolom 'id' di tabel 'tenants'
                  ->onDelete('cascade');           // <-- INI BAGIAN PALING PENTING
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
