<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kartu_rumah_pompas', function (Blueprint $table) {
            if (! Schema::hasColumn('kartu_rumah_pompas', 'inspection_data')) {
                $table->json('inspection_data')->nullable()->after('uji_fungsi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('kartu_rumah_pompas', function (Blueprint $table) {
            if (Schema::hasColumn('kartu_rumah_pompas', 'inspection_data')) {
                $table->dropColumn('inspection_data');
            }
        });
    }
};
