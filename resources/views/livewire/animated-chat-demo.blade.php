<div class="">
    <div
    x-data="{
        run() {
            let loop = () => {
                // Ask backend to advance conversation
                $wire.advanceConversation().then(() => {
                    // Figure out next delay
                    let delay = $wire.isTyping
                        ? 1500
                        : ($wire.conversation?.messages[$wire.nextMessageIndex]?.delay_ms ?? 2500);

                    // Schedule next call
                    setTimeout(loop, delay);
                });
            };
            loop();
        },
        scrollToBottom() {
            this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight;
        }
    }"
    x-init="run()"
    @message-added.window="setTimeout(() => scrollToBottom(), 100)"
    class="w-full max-w-lg mx-auto bg-white rounded-3xl shadow-sm overflow-hidden border border-gray-100 font-sans"
>
    <!-- Header -->
    <header class="bg-linear-to-r from-emerald-600 to-emerald-700 text-white p-4 flex items-center justify-between shadow-md">
        <div class="flex items-center space-x-3">
            <img src="https://ui-avatars.com/api/?name=Sarah+Chen&background=E0F2F1&color=0D9488"
                 alt="Dr. Sarah Chen"
                 class="w-10 h-10 rounded-full border-2 border-emerald-200">
            <div>
                <h3 class="font-semibold text-sm sm:text-base leading-tight">Uju Monyei</h3>
                <p class="text-xs opacity-90 mt-0.5">Your Personal Pharmacist • Online</p>
            </div>
        </div>
        <div class="flex space-x-3 items-center">
            <a
                href="tel:+2348147578314"
                aria-label="Call +234 814 757 8314"
                title="Call +234 814 757 8314"
                class="p-1 rounded-full hover:bg-emerald-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500"
            >
                <x-heroicon-o-phone class="size-4 sm:size-5 opacity-90" />
                <span class="sr-only">Call +234 814 757 8314</span>
            </a>
        </div>
    </header>

    <!-- Messages Container -->
    <main
        x-ref="messages"
        class="h-96 overflow-y-auto p-4 space-y-4 bg-gray-50 scrollbar-thumb-rounded scrollbar-track-rounded scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100"
    >
        @foreach($visibleMessages as $message)
            <div wire:key="msg-{{ $loop->index }}" class="flex {{ $message['sender'] === 'patient' ? 'justify-end' : 'justify-start' }} animate-fade-in-up">
                <div class="max-w-[85%] px-4 py-2 rounded-xl {{ $message['sender'] === 'patient' ? 'bg-emerald-500 text-white' : 'bg-white text-gray-800 shadow-sm' }}">
                    <p class="text-sm leading-relaxed">{{ $message['text'] }}</p>
                </div>
            </div>
        @endforeach

        @if($isTyping)
            <div class="flex justify-start animate-fade-in">
                <div class="bg-white px-4 py-3 rounded-2xl shadow-sm rounded-bl-none border border-gray-200">
                    <div class="flex space-x-1 items-center">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bubble-bounce" style="animation-delay: 0s;"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bubble-bounce" style="animation-delay: 0.1s;"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bubble-bounce" style="animation-delay: 0.2s;"></div>
                    </div>
                </div>
            </div>
        @endif
    </main>

    <!-- Input Area (Real WhatsApp Forwarder) -->
    <footer class="p-4 bg-white border-t border-gray-200 shadow-inner">
        <div class="flex space-x-3 items-center">
            <input
                id="whatsappMessage"
                type="text"
                placeholder="Type a message..."
                class="flex-1 rounded-full border-gray-300 bg-gray-50 py-2 px-4
                    text-xs sm:text-sm text-gray-700
                    focus:outline-none focus:ring-1 focus:ring-emerald-400
                    placeholder-gray-400">

            <button
                type="button"
                onclick="
                    let msg = document.getElementById('whatsappMessage').value;
                    if(msg.trim() !== '') {
                        let phone = '2348147578314'; // <-- replace with your WhatsApp number (no leading + or 0)
                        let url = `https://wa.me/${phone}?text=${encodeURIComponent(msg)}`;
                        window.open(url, '_blank');
                    }
                "
                class="rounded-full bg-emerald-600 text-white p-2.5
                    hover:bg-emerald-700 transition-colors duration-200
                    focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2">
                <x-heroicon-o-paper-airplane class="w-4 h-4 sm:w-5 sm:h-5" />
            </button>
        </div>
    </footer>

</div>

<style>
    /* Custom scrollbar */
    .scrollbar-thin::-webkit-scrollbar { width: 8px; }
    .scrollbar-thumb-gray-300::-webkit-scrollbar-thumb { background-color: #d1d5db; }
    .scrollbar-track-gray-100::-webkit-scrollbar-track { background-color: #f3f4f6; }
    .scrollbar-thumb-rounded::-webkit-scrollbar-thumb { border-radius: 9999px; }
    .scrollbar-track-rounded::-webkit-scrollbar-track { border-radius: 9999px; }

    @keyframes fade-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fade-in 0.3s ease-out forwards; }

    @keyframes fade-in-up { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in-up { animation: fade-in-up 0.4s ease-out forwards; }

    @keyframes bubble-bounce { 0%, 80%, 100% { transform: scale(0); } 40% { transform: scale(1); } }
    .animate-bubble-bounce { animation: bubble-bounce 1.4s infinite ease-in-out both; }
</style>

</div>
