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
        Schema::create('stock_opname_header', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('kode_stock_opname');
            $table->date('periode');
            $table->text('created_by')->nullable();
            $table->dateTime('created_at');
            $table->text('updated_by')->nullable();
            $table->dateTime('updated_at');
            $table->text('approve_by')->nullable();
            $table->dateTime('approve_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_opname_header');
    }
};
