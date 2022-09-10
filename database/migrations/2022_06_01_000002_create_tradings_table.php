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
        Schema::create('tradings', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('bot');
            $table->string('exchange');
            $table->string('ticker');
            $table->string('base_symbol');
            $table->string('quote_symbol');
            $table->string('ticker');
            $table->string('interval');
            $table->json('options')->nullable();
            $table->timestamps();

            $table->index('bot');
            $table->index('exchange');
            $table->index('ticker');
            $table->index('base_symbol');
            $table->index('quote_symbol');
            $table->index('interval');
            $table->index('created_at');
        });

        Schema::create('trading_subscribers', function (Blueprint $table) {
            $table->bigInteger('trading_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->timestamp('subscribed_at')->nullable();

            $table->foreign('trading_id')->references('id')->on('tradings')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['trading_id', 'user_id']);
        });

        Schema::create('trading_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('trading_id')->unsigned();
            $table->tinyInteger('status')->default(2)
                ->comment('1=done|2=doing|3=failed');
            $table->longText('indication');
            $table->timestamp('time')->nullable();
            $table->timestamps();

            $table->foreign('trading_id')->references('id')->on('tradings')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->unique(['trading_id', 'time']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trading_broadcasts');
        Schema::dropIfExists('trading_subscribers');
        Schema::dropIfExists('tradings');
    }
};
