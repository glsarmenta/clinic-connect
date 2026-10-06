<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->string('logo_url')->nullable()->after('name');
            $table->string('tagline')->nullable()->after('name');
            $table->text('about')->nullable()->after('tagline');
            $table->string('emergency_phone', 50)->nullable()->after('phone');
            $table->text('google_map_embed_url')->nullable()->after('google_map_url');
            $table->string('operating_days', 100)->nullable()->after('close_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn([
                'logo_url',
                'tagline',
                'about',
                'emergency_phone',
                'google_map_embed_url',
                'operating_days',
            ]);
        });
    }
};
