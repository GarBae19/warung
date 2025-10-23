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
        Schema::create('barcode_barang', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('kode_barang', 250);
            $table->text('barcode');
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
        Schema::dropIfExists('barcode_barang');
    }
};
