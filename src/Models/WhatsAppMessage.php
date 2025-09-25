<?php

namespace DevsFort\LaravelWhatsappChat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WhatsAppMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'from',
        'body',
        'direction',
        'type',
        'raw_data',
        'processed_at',
        'user_id',
        'status',
        'status_updated_at'
    ];

    protected $casts = [
        'raw_data' => 'array',
        'processed_at' => 'datetime',
        'status_updated_at' => 'datetime'
    ];

    /**
     * Scope for inbound messages
     */
    public function scopeInbound($query)
    {
        return $query->where('direction', 'inbound');
    }

    /**
     * Scope for outbound messages
     */
    public function scopeOutbound($query)
    {
        return $query->where('direction', 'outbound');
    }

    /**
     * Scope for messages from specific number
     */
    public function scopeFrom($query, $from)
    {
        return $query->where('from', $from);
    }

    /**
     * Scope for recent messages
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Get formatted phone number
     */
    public function getFormattedFromAttribute()
    {
        // Remove country code and format for display
        $phone = preg_replace('/[^0-9]/', '', $this->from);
        if (strlen($phone) > 10) {
            $phone = substr($phone, -10);
        }
        return '+1 ' . substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6);
    }

    /**
     * Get message type icon
     */
    public function getTypeIconAttribute()
    {
        return match($this->type) {
            'text' => '💬',
            'image' => '🖼️',
            'document' => '📄',
            'audio' => '🎵',
            'video' => '🎥',
            'template' => '📋',
            'location' => '📍',
            'contact' => '👤',
            default => '❓'
        };
    }

    /**
     * Get direction badge class
     */
    public function getDirectionBadgeClassAttribute()
    {
        return match($this->direction) {
            'inbound' => 'bg-green-100 text-green-800',
            'outbound' => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get conversation preview
     */
    public function getPreviewAttribute()
    {
        $preview = $this->body;
        if (strlen($preview) > 50) {
            $preview = substr($preview, 0, 50) . '...';
        }
        return $preview;
    }

    /**
     * Relationship to User (who sent/received the message)
     */
    public function user()
    {
        // Use the configured user model
        $userModel = config('whatsapp-chat.user_model', 'App\\Models\\User');
        return $this->belongsTo($userModel);
    }
}
