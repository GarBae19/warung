<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mutasi_barang', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('kode_transaksi');
            $table->text('kode_barang');
            $table->decimal('qty', 11, 0);
            $table->text('keterangan');
            $table->dateTime('tanggal');
            $table->text('created_by')->nullable();
            $table->dateTime('created_at');
            $table->text('updated_by')->nullable();
            $table->dateTime('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mutasi_barang');
    }
};
