<?php

namespace DevsFort\LaravelWhatsappChat\Events;

use DevsFort\LaravelWhatsappChat\Models\WhatsAppMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhatsAppMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $conversationId;

    /**
     * Create a new event instance.
     */
    public function __construct(WhatsAppMessage $message)
    {
        $this->message = $message;
        $this->conversationId = $message->from;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channelPrefix = config('whatsapp-chat.broadcasting.channel_prefix', 'whatsapp');

        return [
            new PrivateChannel($channelPrefix . '.chat.' . $this->conversationId),
            new PrivateChannel($channelPrefix . '.conversations'),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'from' => $this->message->from,
            'body' => $this->message->body,
            'direction' => $this->message->direction,
            'type' => $this->message->type,
            'created_at' => $this->message->created_at->toISOString(),
            'user_id' => $this->message->user_id,
            'user_name' => $this->message->user?->name ?? null,
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}
