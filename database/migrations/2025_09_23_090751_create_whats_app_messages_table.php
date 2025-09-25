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
        Schema::create('whats_app_messages', function (Blueprint $table) {
            $table->id();
            $table->string('from')->index(); // WhatsApp phone number
            $table->text('body'); // Message content
            $table->enum('direction', ['inbound', 'outbound']); // Message direction
            $table->enum('type', ['text', 'image', 'document', 'audio', 'video', 'template', 'location', 'contact']); // Message type
            $table->json('raw_data')->nullable(); // Raw webhook/API data
            $table->timestamp('processed_at')->nullable(); // When message was processed
            $table->timestamps();

            // Indexes for better performance
            $table->index(['from', 'created_at']);
            $table->index(['direction', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whats_app_messages');
    }
};
