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
        Schema::create('master_barang', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('kode_barang', 100);
            $table->text('nama_barang');
            $table->string('kode_jenis_barang', 100);
            $table->string('kode_brand', 100);
            $table->string('kode_satuan', 100);
            $table->string('created_by', 100)->nullable();
            $table->dateTime('created_at');
            $table->string('updated_by', 100)->nullable();
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
        Schema::dropIfExists('master_barang');
    }
};
