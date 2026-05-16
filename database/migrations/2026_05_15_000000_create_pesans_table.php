<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pesans', function (Blueprint $col) {
            $col->id();
            $col->string('nomor');
            $col->text('pesan');
            $col->string('media_path')->nullable();
            $col->string('status')->default('pending'); // pending, sent, failed
            $col->text('error_message')->nullable();
            $col->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pesans');
    }
};
