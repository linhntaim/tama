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
        Schema::create('trading_strategies', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('buy_trading_id')->unsigned();
            $table->bigInteger('sell_trading_id')->unsigned();
            $table->integer('type')
                ->comment('1=real|2=fake');
            $table->integer('status')
                ->comment('1=active|2=paused');
            $table->decimal('buy_risk', 5, 4)->unsigned()->default(1.0000)
                ->comment('0.0000->1.0000');
            $table->decimal('sell_risk', 5, 4)->unsigned()->default(1.0000)
                ->comment('0.0000->1.0000');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('buy_trading_id')->references('id')->on('tradings')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('sell_trading_id')->references('id')->on('tradings')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->index('type');
            $table->index('status');
        });

        Schema::create('trading_swaps', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('trading_strategy_id')->unsigned();
            $table->bigInteger('trading_broadcast_id')->unsigned()->nullable();
            $table->decimal('price', 36, 18)->default(1.000000000000000000);
            $table->decimal('base_amount', 36, 18)->default(0.000000000000000000);
            $table->decimal('quote_amount', 36, 18)->default(0.000000000000000000);
            $table->longText('exchange_order')->nullable();
            $table->timestamp('time')->nullable();
            $table->timestamps();

            $table->foreign('trading_strategy_id')->references('id')->on('trading_strategies')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('trading_broadcast_id')->references('id')->on('trading_broadcasts')
                ->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trading_swaps');
        Schema::dropIfExists('trading_strategies');
    }
};
