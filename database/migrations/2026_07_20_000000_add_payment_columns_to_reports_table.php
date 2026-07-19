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
            $table->timestamp('unlocked_at')->nullable()->after('html_content');
            $table->string('stream_payment_link_id')->nullable()->after('unlocked_at');
            $table->string('stream_invoice_id')->nullable()->after('stream_payment_link_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['unlocked_at', 'stream_payment_link_id', 'stream_invoice_id']);
        });
    }
};
