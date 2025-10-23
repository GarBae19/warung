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
        Schema::create('master_brand', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('kode_brand', 250);
            $table->string('nama_brand', 250);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->string('created_by', 250)->nullable();
            $table->string('updated_by', 250)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_brand');
    }
};
