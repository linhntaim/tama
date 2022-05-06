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
        Schema::create('data_exports', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('file_id')->unsigned()->nullable();
            $table->tinyInteger('status')->comment('1 = exported, 2 = exporting, 3 = failed');
            $table->string('name');
            $table->longText('export');
            $table->longText('exception')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->foreign('file_id')->references('id')->on('files')
                ->onUpdate('cascade')->onDelete('set null');

            $table->index('name');
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
        Schema::dropIfExists('data_exports');
    }
};
