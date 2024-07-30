<div class="max-w-xl">
    @if (session('success'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition.opacity.duration.500ms @click="show = false" class="fixed top-4 right-4 max-w-xs bg-white border border-gray-200 rounded-xl shadow-lg dark:bg-neutral-800 dark:border-neutral-700" role="alert">
        <div class="flex p-4">
            <div class="flex-shrink-0">
                <svg class="flex-shrink-0 size-4 text-teal-500 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"></path>
                </svg>
            </div>
            <div class="ms-3">
                <p class="text-sm text-gray-700 dark:text-neutral-400">
                    {{ session('success') }}
                </p>
            </div>
        </div>
    </div>
    @endif

    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Google Search Console Connected') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                @if(auth()->user()->sitemaps()->count() > 0)
            <form method="POST" action="{{ route('sitemaps.resync') }}">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 dark:bg-blue-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 dark:hover:bg-blue-500 focus:bg-blue-500 dark:focus:bg-blue-500 active:bg-blue-700 dark:active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    Re-sync Sitemaps
                </button>
            </form>
            @else
            <form method="POST" action="{{ route('sitemaps.sync') }}">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 dark:bg-blue-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 dark:hover:bg-blue-500 focus:bg-blue-500 dark:focus:bg-blue-500 active:bg-blue-700 dark:active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    Sync Sitemaps
                </button>
            </form>
            @endif
            </p>
            <div class="sitemaps-table mt-6">
                @if($sitemaps->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Sitemap URL
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Index
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                            @foreach($sitemaps as $sitemap)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $sitemap->url }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ $sitemap->is_index ? 'True' : 'False' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <!-- Add actions if needed -->
                                    <a href="#" class="text-indigo-600 hover:text-indigo-900">View</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                    No sitemaps available.
                </p>
                @endif
            </div>
        </header>

    </section>
</div>