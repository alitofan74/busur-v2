<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('tipe_input', 30)->default('manual');
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('terkirim')->default(0);
            $table->unsignedInteger('gagal')->default(0);
            $table->string('status', 30)->default('draft');
            $table->json('settings')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('last_processed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('tipe_input');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
