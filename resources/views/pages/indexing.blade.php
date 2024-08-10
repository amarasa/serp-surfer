<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Indexing Submission') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">The following URLs have been submitted for indexing:</h3>

                    @if (!empty($urls))
                    <ul class="list-disc list-inside">
                        @foreach($urls as $url)
                        <li>{{ $url }}</li>
                        @endforeach
                    </ul>
                    @else
                    <p>No URLs were submitted for indexing.</p>
                    @endif

                    <div class="mt-6">
                        <a href="{{ url()->previous() }}" class="text-blue-600 hover:text-blue-800">
                            Return to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>