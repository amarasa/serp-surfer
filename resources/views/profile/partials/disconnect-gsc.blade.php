<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Google Search Console Connected') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Your account is connected to Google Search Console.') }}
        </p>
        <form method="POST" action="{{ route('auth.google.disconnect') }}">
            @csrf
            <button type="submit" class="mt-4 inline-flex items-center px-4 py-2 bg-red-600 dark:bg-red-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 dark:hover:bg-red-500 focus:bg-red-500 dark:focus:bg-red-500 active:bg-red-700 dark:active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                Disconnect
            </button>
        </form>
    </header>
</section>