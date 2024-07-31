<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="pt-8 pb-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="relative inline-block w-1/4">
            <div class="relative">
                <button id="dropdown-button" class="w-full text-left bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm pl-3 pr-10 py-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <span id="selected-item" class="block truncate">{{ $domains[0] ?? '' }}</span>
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
                    <li class="cursor-pointer select-none relative py-2 pl-3 pr-9 text-gray-900 dark:text-gray-100 hover:bg-indigo-600 hover:text-white">
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
        </div>
    </div>



    <div class="py-12">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("Placeholder") }}
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
        const items = dropdownMenu.querySelectorAll('li');

        dropdownButton.addEventListener('click', function() {
            dropdownMenu.classList.toggle('hidden');
        });

        items.forEach(item => {
            item.addEventListener('click', function() {
                const selectedText = item.querySelector('span').innerText;
                selectedItem.innerText = selectedText;

                items.forEach(i => i.querySelector('span + span').classList.add('hidden'));
                item.querySelector('span + span').classList.remove('hidden');

                dropdownMenu.classList.add('hidden');
            });
        });

        document.addEventListener('click', function(event) {
            if (!dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.classList.add('hidden');
            }
        });
    });
</script>