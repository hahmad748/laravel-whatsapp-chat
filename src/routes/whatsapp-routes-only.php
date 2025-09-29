// WhatsApp Chat Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/chat', [DevsFort\LaravelWhatsappChat\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/conversations', [DevsFort\LaravelWhatsappChat\Http\Controllers\ChatController::class, 'getConversations'])->name('chat.conversations');
    Route::get('/chat/messages/{conversationId}', [DevsFort\LaravelWhatsappChat\Http\Controllers\ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/chat/send', [DevsFort\LaravelWhatsappChat\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.send');
    Route::post('/chat/mark-read/{conversationId}', [DevsFort\LaravelWhatsappChat\Http\Controllers\ChatController::class, 'markAsRead'])->name('chat.mark-read');
    Route::post('/chat/assign-number', [DevsFort\LaravelWhatsappChat\Http\Controllers\ChatController::class, 'assignNumberToUser'])->name('chat.assign-number');
});

// WhatsApp Verification Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile/whatsapp-verification', [DevsFort\LaravelWhatsappChat\Http\Controllers\WhatsAppVerificationController::class, 'show'])->name('whatsapp.verification.show');
    Route::post('/profile/whatsapp-verification/send', [DevsFort\LaravelWhatsappChat\Http\Controllers\WhatsAppVerificationController::class, 'sendVerificationCode'])->name('whatsapp.verification.send');
    Route::post('/profile/whatsapp-verification/verify', [DevsFort\LaravelWhatsappChat\Http\Controllers\WhatsAppVerificationController::class, 'verify'])->name('whatsapp.verification.verify');
    Route::post('/profile/whatsapp-verification/remove', [DevsFort\LaravelWhatsappChat\Http\Controllers\WhatsAppVerificationController::class, 'remove'])->name('whatsapp.verification.remove');
});

// WhatsApp Webhook Routes (no auth required)
Route::get('/webhook/whatsapp', [DevsFort\LaravelWhatsappChat\Http\Controllers\WhatsAppWebhookController::class, 'verify'])->name('whatsapp.verify');
Route::post('/webhook/whatsapp', [DevsFort\LaravelWhatsappChat\Http\Controllers\WhatsAppWebhookController::class, 'webhook'])->name('whatsapp.webhook');
