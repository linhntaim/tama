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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('name')->nullable();
            $table->string('mime')->nullable();
            $table->string('extension')->nullable();
            $table->string('size')->default('0');
            $table->string('storage');
            $table->longText('options')->nullable();
            $table->longText('file');
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
        Schema::dropIfExists('files');
    }
};
