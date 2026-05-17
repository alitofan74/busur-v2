<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesans', function (Blueprint $table) {
            $table->foreignId('campaign_id')
                ->nullable()
                ->after('id')
                ->constrained('campaigns')
                ->nullOnDelete();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('pesans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('campaign_id');
            $table->dropIndex(['status']);
        });
    }
};
