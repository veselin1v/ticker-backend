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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->integer('portfolio_id');
            $table->integer('ticker_id');
            $table->float('quantity')->nullable();
            $table->float('invested')->nullable();
            $table->float('average_price')->nullable();
            $table->float('position_worth')->nullable();
            $table->float('profit')->nullable();
            $table->float('roi')->nullable();
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
        Schema::dropIfExists('assets');
    }
};
