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
        Schema::table('reports', function (Blueprint $table) {
            $table->json('narrative')->nullable()->after('answers_snapshot');
            $table->text('narrative_error')->nullable()->after('narrative');
            $table->longText('html_content')->nullable()->after('narrative_error');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['narrative', 'narrative_error', 'html_content']);
        });
    }
};
