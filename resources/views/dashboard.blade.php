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
                            <span class="absolute inset-y-0 left-0 flex items-center pl-2 text-indigo-600 hidden">
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Index Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Last Scanned
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
                                    <input type="checkbox" class="select-row">
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    <div class="table-cell-title tooltip-slow" data-tippy-content="{{ $url->page_title ?? 'No Title' }}">
                                        {{ $url->page_title ?? 'No Title' }}
                                    </div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400">{{ $url->page_url }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {!! $url->index_status ?
                                    '<svg class="w-6 h-6 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                        <path fill="#157f1f" d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-111 111-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L369 209z" />
                                    </svg>' :
                                    '<svg class="w-6 h-6 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                        <path fill="#EC0B43" d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c-9.4 9.4-9.4 24.6 0 33.9l47 47-47 47c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l47-47 47 47c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-47-47 47-47c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-47 47-47-47c-9.4-9.4-24.6-9.4-33.9 0z" />
                                    </svg>' !!}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ $url->updated_at ? $url->updated_at->diffForHumans() : 'No Data' }}
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
                    <div class="pagination-info mt-4">
                        @if ($urls instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        {{ $urls->appends(['domain' => request('domain')])->links() }}
                        @endif
                    </div>
                </div>
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

        // Handle selection
        dropdownMenu.querySelectorAll('li').forEach(item => {
            item.addEventListener('click', function() {
                const domain = item.getAttribute('data-domain');
                selectedItem.innerText = domain || 'Select Domain';
                selectedDomainInput.value = domain;
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

    const selectedItem = document.getElementById('selected-item');

    document.getElementById('select-all').addEventListener('change', function(event) {
        const isChecked = event.target.checked;
        const checkboxes = document.querySelectorAll('.select-row');
        checkboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
        });
    });

    items.forEach(item => {
        item.addEventListener('click', function() {
            const selectedText = item.querySelector('.block').innerText;
            selectedItem.innerText = selectedText;

            // Hide the dropdown
            dropdownMenu.classList.add('hidden');

            // Highlight the selected item
            items.forEach(i => i.querySelector('span + span').classList.add('hidden'));
            item.querySelector('span + span').classList.remove('hidden');

            // Fetch the data for the selected domain
            fetchUrlsForDomain(selectedText);
        });
    });
</script>