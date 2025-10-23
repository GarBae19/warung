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
        Schema::create('master_jenis', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('kode_jenis', 100);
            $table->text('nama_jenis');
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
        Schema::dropIfExists('master_jenis');
    }
};
