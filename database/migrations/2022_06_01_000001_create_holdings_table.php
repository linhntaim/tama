<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('holdings', function (Blueprint $table) {
            $table->bigInteger('user_id')->unsigned()->primary();
            $table->decimal('initial', 36, 18)->unsigned()->default(0.000000000000000000);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->index('created_at');
        });

        Schema::create('holding_assets', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->string('exchange');
            $table->string('symbol');
            $table->decimal('amount', 36, 18)->unsigned()->default(0.00);
            $table->integer('order')->unsigned();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('holdings')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->unique(['user_id', 'exchange', 'symbol']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('holding_assets');
        Schema::dropIfExists('holdings');
    }
};
