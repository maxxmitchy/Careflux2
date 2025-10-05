<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class ToastNotifier extends Component
{
    public bool $show = false;

    public string $message = '';

    public string $type = 'success';

    // This listener catches the global 'toast' event
    #[On('toast')]
    public function showToast(string $message, string $type = 'success')
    {
        $this->message = $message;
        $this->type = $type;
        $this->show = true;

        // We can use a dispatch to close the toast after a few seconds
        // This is a more robust way than using setTimeout in the component itself
        $this->dispatch('close-toast-after-delay');
    }

    public function render()
    {
        return view('livewire.toast-notifier');
    }
}
