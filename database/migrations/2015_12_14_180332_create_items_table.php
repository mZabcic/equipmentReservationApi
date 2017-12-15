<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('identifier');
            $table->string('description');
            $table->binary('picture');
            $table->integer('kit_id')->references('id')->on('kits');
            $table->integer('type_id')->references('id')->on('types');
            $table->integer('subtype_id')->references('id')->on('subtypes');
            $table->integer('device_type_id')->references('id')->on('device_types');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
    }
}
