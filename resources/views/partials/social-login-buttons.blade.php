<div class="relative my-4">
    <div class="absolute inset-0 flex items-center" aria-hidden="true">
        <div class="w-full border-t border-gray-300"></div>
    </div>
    <div class="relative flex justify-center text-xs">
        <span class="bg-white px-2 text-gray-500">Or continue with</span>
    </div>
</div>

<div>
    <a href="{{ route('auth.social.redirect', ['provider' => 'google', 'panel' => $panel]) }}"
       class="inline-flex w-full items-center justify-center gap-2 rounded bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-offset-0">

        <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24">
            <path d="M12.0003 4.75C13.7703 4.75 15.2403 5.37 16.3603 6.45L19.6303 3.18C17.5103 1.2 14.9303 0 12.0003 0C7.3103 0 3.4403 2.73 1.4303 6.58L5.0703 9.42C6.0103 6.77 8.7603 4.75 12.0003 4.75Z" fill="#EA4335"></path>
            <path d="M12.0003 24C14.9303 24 17.5103 22.8 19.6303 20.82L16.3603 17.55C15.2403 18.63 13.7703 19.25 12.0003 19.25C8.7603 19.25 6.0103 17.23 5.0703 14.58L1.4303 17.42C3.4403 21.27 7.3103 24 12.0003 24Z" fill="#34A853"></path>
            <path d="M5.0703 14.58C4.8203 13.86 4.6803 13.09 4.6803 12.3C4.6803 11.51 4.8203 10.74 5.0703 10.02L1.4303 7.18C0.5403 8.94 0.0003 10.99 0.0003 13.12C0.0003 15.25 0.5403 17.3 1.4303 19.06L5.0703 14.58Z" fill="#FBBC05"></path>
            <path d="M12.0003 9.25C13.1003 9.25 13.9603 9.63 14.6103 10.24L17.4903 7.38C15.8203 5.86 14.0503 5 12.0003 5C8.7603 5 6.0103 7.02 5.0703 9.67L8.7103 12.51C9.6503 10.86 10.6603 9.25 12.0003 9.25Z" fill="#4285F4"></path>
        </svg>

        <span>Sign in with Google</span>
    </a>
</div>

