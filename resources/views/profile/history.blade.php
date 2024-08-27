<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Index History') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if($indexingResults->isEmpty())
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-gray-700 dark:text-gray-300">No indexing history found.</p>
            </div>
            @else
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($indexingResults as $result)
                    <li class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $result->url }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Indexed on {{ $result->index_date->format('F j, Y, g:i a') }}</p>
                            </div>
                            <div class="ml-4">
                                <span class="text-xs text-green-700 dark:text-green-500 bg-green-100 dark:bg-green-800 rounded-full px-2 py-1">Indexed</span>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>