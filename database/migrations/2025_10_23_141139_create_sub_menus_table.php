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
        Schema::create('sub_menus', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('menu_parent_id');
            $table->text('nama_submenu');
            $table->text('link');
            $table->text('icon');
            $table->dateTime('created_at');
            $table->string('created_by', 250);
            $table->dateTime('updated_at');
            $table->string('updated_by', 250);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sub_menus');
    }
};
