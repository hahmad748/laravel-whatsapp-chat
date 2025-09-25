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
        Schema::table('whats_app_messages', function (Blueprint $table) {
            $table->foreignId('deal_id')->nullable()->constrained()->onDelete('cascade');
            $table->index(['deal_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whats_app_messages', function (Blueprint $table) {
            $table->dropForeign(['deal_id']);
            $table->dropIndex(['deal_id', 'created_at']);
            $table->dropColumn('deal_id');
        });
    }
};
