<section id="google-service-worker" class="pt-8 pb-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
    <!-- Form to Add a New Service Worker -->
    <div class="mb-8 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
        <h3 class="font-semibold text-lg text-gray-800 dark:text-gray-200 mb-4">Add New Service Worker</h3>
        <form action="{{ route('add.service.workers') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label for="json_file" class="block text-sm font-medium text-gray-700">Upload JSON File</label>
                <input type="file" name="json_file" id="json_file" class="mt-1 block w-full" required>
            </div>

            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Add Service Worker
            </button>
        </form>

    </div>

    <!-- List of Service Workers -->
    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
        <h3 class="font-semibold text-lg text-gray-800 dark:text-gray-200 mb-4">Active Service Workers</h3>

        @if ($workers->isEmpty())
        <p class="text-gray-500 dark:text-gray-400">No active service workers.</p>
        @else
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Worker Name</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Used By</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($workers as $worker)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $worker->address }}</td>
                    <td class="px-6 py-4 text-sm text-center text-gray-900 dark:text-gray-100">{{ $worker->used }}</td>
                    <td class="px-6 py-4 text-sm text-right">
                        <a href="#" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-600 inline-block">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block" viewBox="0 0 512 512">
                                <path d="M410.3 231l11.3-11.3-33.9-33.9-62.1-62.1L291.7 89.8l-11.3 11.3-22.6 22.6L58.6 322.9c-10.4 10.4-18 23.3-22.2 37.4L1 480.7c-2.5 8.4-.2 17.5 6.1 23.7s15.3 8.5 23.7 6.1l120.3-35.4c14.1-4.2 27-11.8 37.4-22.2L387.7 253.7 410.3 231zM160 399.4l-9.1 22.7c-4 3.1-8.5 5.4-13.3 6.9L59.4 452l23-78.1c1.4-4.9 3.8-9.4 6.9-13.3l22.7-9.1 0 32c0 8.8 7.2 16 16 16l32 0zM362.7 18.7L348.3 33.2 325.7 55.8 314.3 67.1l33.9 33.9 62.1 62.1 33.9 33.9 11.3-11.3 22.6-22.6 14.5-14.5c25-25 25-65.5 0-90.5L453.3 18.7c-25-25-65.5-25-90.5 0zm-47.4 168l-144 144c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l144-144c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6z" />
                            </svg>
                        </a>
                        <a href="#" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-600 inline-block ml-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block" viewBox="0 0 640 512">
                                <path d="M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304l91.4 0C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7L29.7 512C13.3 512 0 498.7 0 482.3zM471 143c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z" />
                            </svg>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination Links -->
        <div class="mt-4">
            {{ $workers->links() }}
        </div>
        @endif
    </div>
</section>