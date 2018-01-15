<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtendTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('extend', function (Blueprint $table) {
            $table->increments('id');
            $table->binary('reservation_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('status')->nullable();
            $table->string('reason')->nullable();
            $table->date('new_date_to')->nullable();
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
        Schema::dropIfExists('extend');
    }
}
