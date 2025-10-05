<?php

namespace App\Livewire;

use Livewire\Component;
use Src\Content\Domain\Models\Conversation;

class AnimatedChatDemo extends Component
{
    public array $visibleMessages = [];

    public int $nextMessageIndex = 0;

    public bool $isTyping = false;

    public bool $isFinished = false;

    public ?Conversation $conversation = null;

    public function mount()
    {
        $this->conversation = Conversation::with('messages')->where('is_active', true)->first();
        if ($this->conversation && $this->conversation->messages->isNotEmpty()) {
            $this->visibleMessages[] = $this->conversation->messages->first()->toArray();
            $this->nextMessageIndex = 1;
        }
    }

    public function advanceConversation()
    {
        if ($this->isFinished || ! $this->conversation || ! isset($this->conversation->messages[$this->nextMessageIndex])) {
            // If finished or no conversation, stop polling
            $this->isFinished = true;

            return;
        }

        $nextMessage = $this->conversation->messages[$this->nextMessageIndex];

        if ($nextMessage->sender === 'pharmacist' && ! $this->isTyping) {
            $this->isTyping = true;
        } else {
            $this->visibleMessages[] = $nextMessage->toArray();
            $this->nextMessageIndex++;
            $this->isTyping = false;
            // Dispatch browser event for Alpine to scroll
            $this->dispatch('message-added');
        }
    }

    public function render()
    {
        // Dont render the component at all if no active conversation is found
        if (! $this->conversation) {
            return '<div></div>';
        }

        return view('livewire.animated-chat-demo');
    }
}
