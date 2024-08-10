<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="pt-8 pb-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="relative inline-block w-1/4">
            <form id="domain-form" method="GET" action="{{ route('dashboard') }}">
                <input type="hidden" name="domain" id="selected-domain" value="{{ $selectedDomain }}">
                <div class="relative">
                    <button type="button" id="dropdown-button" class="w-full text-left bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm pl-3 pr-10 py-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <span id="selected-item" class="block truncate">
                            {{ $selectedDomain ?? 'Select Domain' }}
                        </span>

                        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </button>
                </div>
                <div id="dropdown-menu" class="absolute mt-1 w-full rounded-md bg-white dark:bg-gray-800 shadow-lg z-10 hidden">
                    <ul class="max-h-60 rounded-md py-1 text-base overflow-auto focus:outline-none sm:text-sm">
                        @foreach($domains as $domain)
                        <li class="cursor-pointer select-none relative py-2 pl-3 pr-9 text-gray-900 dark:text-gray-100 hover:bg-indigo-600 hover:text-white" data-domain="{{ $domain }}">
                            <span class="font-normal block truncate ml-6">{{ $domain }}</span>
                            <span role="checkmark" class="absolute inset-y-0 left-0 flex items-center pl-2 text-indigo-600 {{ $domain !== $selectedDomain ? 'hidden' : '' }}">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="currentColor">
                                    <path d="M438.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L160 338.7 393.4 105.4c12.5-12.5 32.8-12.5 45.3 0z" />
                                </svg>
                            </span>
                        </li>
                        @endforeach
                    </ul>


                </div>
            </form>
        </div>
    </div>
    <form id="index-form" method="POST" action="{{ route('submit.indexing') }}">

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <input type="checkbox" id="select-all">
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Page
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Index Status
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider flex items-center">
                                        Last Seen
                                        <svg class="ml-2 cursor-help tooltip" data-tippy-content="This was the last time that a URL was seen in the sitemap for this domain." xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="16px" height="16px">
                                            <path fill="#A0A0A0" d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm169.8-90.7c7.9-22.3 29.1-37.3 52.8-37.3l58.3 0c34.9 0 63.1 28.3 63.1 63.1c0 22.6-12.1 43.5-31.7 54.8L280 264.4c-.2 13-10.9 23.6-24 23.6c-13.3 0-24-10.7-24-24l0-13.5c0-8.6 4.6-16.5 12.1-20.8l44.3-25.4c4.7-2.7 7.6-7.7 7.6-13.1c0-8.4-6.8-15.1-15.1-15.1l-58.3 0c-3.4 0-6.4 2.1-7.5 5.3l-.4 1.2c-4.4 12.5-18.2 19-30.6 14.6s-19-18.2-14.6-30.6l.4-1.2zM224 352a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z" />
                                        </svg>
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @if($urls->isEmpty())
                                <tr>
                                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100 text-center">
                                        Please select a domain to view the data.
                                    </td>
                                </tr>
                                @else
                                @foreach($urls as $url)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="select-row {{ $url->inQueue ? 'disabled-checkbox' : '' }}" name="urls[]" value="{{ $url->page_url }}" {{ $url->inQueue ? 'disabled' : '' }}>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        <div class="table-cell-title tooltip-slow" data-tippy-content="{{ $url->page_title ?? 'No Title' }}">
                                            {{ $url->page_title ?? 'No Title' }}
                                        </div>
                                        <div class="text-[10px] text-gray-500 dark:text-gray-400">{{ $url->page_url }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                        <div class="flex justify-center">
                                            {!! $url->index_status ?
                                            '<svg class="w-6 h-6 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                                <path fill="#157f1f" d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-111 111-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L369 209z" />
                                            </svg>' :
                                            ($url->inQueue ?
                                            '<svg class="w-6 h-6 text-amber-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                                <path fill="#fcd34d" d="M75 75L41 41C25.9 25.9 0 36.6 0 57.9L0 168c0 13.3 10.7 24 24 24l110.1 0c21.4 0 32.1-25.9 17-41l-30.8-30.8C155 85.5 203 64 256 64c106 0 192 86 192 192s-86 192-192 192c-40.8 0-78.6-12.7-109.7-34.4c-14.5-10.1-34.4-6.6-44.6 7.9s-6.6 34.4 7.9 44.6C151.2 495 201.7 512 256 512c141.4 0 256-114.6 256-256S397.4 0 256 0C185.3 0 121.3 28.7 75 75zm181 53c-13.3 0-24 10.7-24 24l0 104c0 6.4 2.5 12.5 7 17l72 72c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-65-65 0-94.1c0-13.3-10.7-24-24-24z" />
                                            </svg>' :
                                            '<svg class="w-6 h-6 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                                <path fill="#EC0B43" d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c-9.4 9.4-9.4 24.6 0 33.9l47 47-47 47c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l47-47 47 47c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-47-47 47-47c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-47 47-47-47c-9.4-9.4-24.6-9.4-33.9 0z" />
                                            </svg>')
                                            !!}

                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                        @if ($url->urlList)
                                        @php
                                        $lastSeen = \Carbon\Carbon::parse($url->urlList->last_seen);
                                        $now = \Carbon\Carbon::now();
                                        $hoursDiff = $lastSeen ? floor($lastSeen->diffInHours($now)) : null;
                                        $indicatorClass = '';

                                        if ($hoursDiff !== null) {
                                        if ($hoursDiff <= 24) { $indicatorClass='bg-green-500' ; } elseif ($hoursDiff <=48) { $indicatorClass='bg-yellow-500' ; } else { $indicatorClass='bg-red-500' ; } } @endphp <!-- Debugging output -->

                                            @if ($hoursDiff !== null)
                                            <span class="inline-block tooltip w-3 h-3 rounded-full blink {{ $indicatorClass }}" data-tippy-content="{{ $lastSeen->diffForHumans() }}"></span>

                                            @else
                                            No Data
                                            @endif
                                            @else
                                            No Data
                                            @endif
                                    </td>


                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="https://google.com/search?q=site:{{ $url->page_url }}" target="_blank" class="inline-block text-blue-600 hover:text-blue-800">
                                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 488 512">
                                                <path d="M488 261.8C488 403.3 391.1 504 248 504 110.8 504 0 393.2 0 256S110.8 8 248 8c66.8 0 123 24.5 166.3 64.9l-67.5 64.9C258.5 52.6 94.3 116.6 94.3 256c0 86.5 69.1 156.6 153.7 156.6 98.2 0 135-70.4 140.8-106.9H248v-85.3h236.1c2.3 12.7 3.9 24.9 3.9 41.4z" />
                                            </svg>
                                        </a>
                                        <a href="{{ $url->page_url }}" target="_blank" class="inline-block ml-2 text-blue-600 hover:text-blue-800">
                                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                                <path d="M352 0c-12.9 0-24.6 7.8-29.6 19.8s-2.2 25.7 6.9 34.9L370.7 96 201.4 265.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L416 141.3l41.4 41.4c9.2 9.2 22.9 11.9 34.9 6.9s19.8-16.6 19.8-29.6l0-128c0-17.7-14.3-32-32-32L352 0zM80 32C35.8 32 0 67.8 0 112L0 432c0 44.2 35.8 80 80 80l320 0c44.2 0 80-35.8 80-80l0-112c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 112c0 8.8-7.2 16-16 16L80 448c-8.8 0-16-7.2-16-16l0-320c0-8.8 7.2-16 16-16l112 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L80 32z" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                        @csrf
                        <input type="hidden" name="sitemap_id" value="{{ $sitemapId }}">

                        <!-- Submit Button -->
                        <div class="mt-4">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Submit Selected URLs for Indexing
                            </button>
                        </div>
                        <div class="pagination-info mt-4">
                            @if ($urls instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            {{ $urls->appends(['domain' => request('domain')])->links() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </form>
    <div class="mt-8 pb-8 container mx-auto text-center">
        <h3 class="font-semibold text-lg text-gray-800 dark:text-gray-200">Legend</h3>
        <div class="flex flex-col items-center mt-2">
            <div class="flex items-center mt-2">
                <span class="inline-block w-3 h-3 mr-2 rounded-full blink bg-green-500"></span>
                <span class="text-sm text-gray-800 dark:text-gray-200">Seen within the last 24 hours</span>
            </div>
            <div class="flex items-center mt-2">
                <span class="inline-block w-3 h-3 mr-2 rounded-full blink bg-yellow-500"></span>
                <span class="text-sm text-gray-800 dark:text-gray-200">Seen within the last 24-48 hours</span>
            </div>
            <div class="flex items-center mt-2">
                <span class="inline-block w-3 h-3 mr-2 rounded-full blink bg-red-500"></span>
                <span class="text-sm text-gray-800 dark:text-gray-200">Seen more than 48 hours ago.</span>
            </div>
            <div class="flex items-center mt-2">
                <span class="text-sm text-gray-800 dark:text-gray-200">Once a URL hasn't been seen in the sitemap in 72 hours, it will be removed from {{ config('app.name') }}.</span>
            </div>
        </div>
    </div>


</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownButton = document.getElementById('dropdown-button');
        const dropdownMenu = document.getElementById('dropdown-menu');
        const selectedItem = document.getElementById('selected-item');
        const domainForm = document.getElementById('domain-form');
        const selectedDomainInput = document.getElementById('selected-domain');

        // Toggle dropdown menu
        dropdownButton.addEventListener('click', function() {
            dropdownMenu.classList.toggle('hidden');
        });

        // Handle selection and checkmark update
        dropdownMenu.querySelectorAll('li').forEach(item => {
            item.addEventListener('click', function() {
                const domain = item.getAttribute('data-domain');
                selectedItem.innerText = domain || 'Select Domain';
                selectedDomainInput.value = domain;

                // Remove checkmark from all items
                dropdownMenu.querySelectorAll('li span[role="checkmark"]').forEach(checkmark => {
                    checkmark.classList.add('hidden');
                });

                // Add checkmark to the selected item
                const checkmark = item.querySelector('span[role="checkmark"]');
                if (checkmark) {
                    checkmark.classList.remove('hidden');
                }

                // Submit the form to load the new data
                domainForm.submit();
            });
        });

        // Hide dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.classList.add('hidden');
            }
        });
    });

    // Event listener for 'select all' checkbox
    document.getElementById('index-form').addEventListener('submit', function(event) {
        const selectedUrls = document.querySelectorAll('.select-row:checked');
        if (selectedUrls.length === 0) {
            event.preventDefault();
            alert('Please select at least one URL to submit for indexing.');
        }
    });


    document.getElementById('select-all').addEventListener('change', function(event) {
        const isChecked = event.target.checked;
        const checkboxes = document.querySelectorAll('.select-row');
        checkboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
        });
    });
</script>