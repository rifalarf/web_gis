<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('segments', function (Blueprint $table) {
            $table->enum('kondisi', ['baik', 'sedang', 'rusak ringan', 'rusak berat'])
                  ->nullable()
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('segments', function (Blueprint $table) {
            $table->enum('kondisi', ['baik', 'sedang', 'rusak_ringan', 'rusak_berat'])
                  ->nullable()
                  ->change();
        });
    }
};
