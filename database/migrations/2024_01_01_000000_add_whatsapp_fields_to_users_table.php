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
        Schema::table('users', function (Blueprint $table) {
            $table->string('whatsapp_number')->nullable();
            $table->boolean('whatsapp_verified')->default(false);
            $table->timestamp('whatsapp_verified_at')->nullable();
            $table->string('whatsapp_verification_code')->nullable();
            $table->enum('type', ['admin', 'user'])->default('user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_number',
                'whatsapp_verified',
                'whatsapp_verified_at',
                'whatsapp_verification_code',
                'type'
            ]);
        });
    }
};
