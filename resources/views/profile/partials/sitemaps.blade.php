<div class="max-w-7xl">
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Google Search Console Connected') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                @if(auth()->user()->sitemaps()->count() > 0)
            <form id="resync-form" method="POST" action="{{ route('sitemaps.resync') }}">
                @csrf
                <input type="hidden" id="overwrite-input" name="overwrite" value="0">
                <button type="button" class="inline-flex items-center px-4 py-2 bg-blue-600 dark:bg-blue-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 dark:hover:bg-blue-500 focus:bg-blue-500 dark:focus:bg-blue-500 active:bg-blue-700 dark:active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150" onclick="confirmResync()">
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
            @if ($workers->isEmpty())
            <p class="text-gray-500 dark:text-gray-400">No active service workers.</p>
            @else
            <div class="sitemaps-table mt-6">
                <div class="overflow-x-auto">
                    <p>In order to use SERP Surfer's indexing feature, you must first add our Google Services Workers to your Google Search Console.</p>
                    <p>Please add the following as users to your Search Console with <span class="bold">Owner</span> permissions:</p>
                    <ul>
                        @foreach ($workers as $worker)
                        <li>{{ $worker->address }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Pagination Links -->
            <div class="mt-4">
                {{ $workers->links() }}
            </div>

            @endif
            <div class="sitemaps-table mt-6">
                @if($sitemaps->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Sitemap URL
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center justify-center space-x-1">
                                        <span>Index</span>
                                        <svg class="cursor-help tooltip" data-tippy-content="Indicates whether the sitemap is an index sitemap, which lists other sitemaps for more detailed organization and coverage of the website's URLs." xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="16px" height="16px">
                                            <path fill="#A0A0A0" d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm169.8-90.7c7.9-22.3 29.1-37.3 52.8-37.3l58.3 0c34.9 0 63.1 28.3 63.1 63.1c0 22.6-12.1 43.5-31.7 54.8L280 264.4c-.2 13-10.9 23.6-24 23.6c-13.3 0-24-10.7-24-24l0-13.5c0-8.6 4.6-16.5 12.1-20.8l44.3-25.4c4.7-2.7 7.6-7.7 7.6-13.1c0-8.4-6.8-15.1-15.1-15.1l-58.3 0c-3.4 0-6.4 2.1-7.5 5.3l-.4 1.2c-4.4 12.5-18.2 19-30.6 14.6s-19-18.2-14.6-30.6l.4-1.2zM224 352a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z" />
                                        </svg>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center justify-center space-x-1">
                                        <span>Processed</span>
                                        <svg class="cursor-help tooltip" data-tippy-content="Indicates whether the sitemap has been run through our system to check for indexing statuses of URLs within the sitemaps, or if the URLs are in queue waiting to scan." xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="16px" height="16px">
                                            <path fill="#A0A0A0" d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm169.8-90.7c7.9-22.3 29.1-37.3 52.8-37.3l58.3 0c34.9 0 63.1 28.3 63.1 63.1c0 22.6-12.1 43.5-31.7 54.8L280 264.4c-.2 13-10.9 23.6-24 23.6c-13.3 0-24-10.7-24-24l0-13.5c0-8.6 4.6-16.5 12.1-20.8l44.3-25.4c4.7-2.7 7.6-7.7 7.6-13.1c0-8.4-6.8-15.1-15.1-15.1l-58.3 0c-3.4 0-6.4 2.1-7.5 5.3l-.4 1.2c-4.4 12.5-18.2 19-30.6 14.6s-19-18.2-14.6-30.6l.4-1.2zM224 352a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z" />
                                        </svg>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center justify-center space-x-1">
                                        <span>Last Processed</span>
                                    </div>
                                </th>

                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center justify-center space-x-1">
                                        <span>Action</span>
                                        <svg class="cursor-help tooltip" data-tippy-content="Once processed,toggle on/off to indicate whether you want SERP Surfer to scan your sitemap daily for new URLs." xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="16px" height="16px">
                                            <path fill="#A0A0A0" d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm169.8-90.7c7.9-22.3 29.1-37.3 52.8-37.3l58.3 0c34.9 0 63.1 28.3 63.1 63.1c0 22.6-12.1 43.5-31.7 54.8L280 264.4c-.2 13-10.9 23.6-24 23.6c-13.3 0-24-10.7-24-24l0-13.5c0-8.6 4.6-16.5 12.1-20.8l44.3-25.4c4.7-2.7 7.6-7.7 7.6-13.1c0-8.4-6.8-15.1-15.1-15.1l-58.3 0c-3.4 0-6.4 2.1-7.5 5.3l-.4 1.2c-4.4 12.5-18.2 19-30.6 14.6s-19-18.2-14.6-30.6l.4-1.2zM224 352a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z" />
                                        </svg>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                            @foreach($sitemaps as $sitemap)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    <a href="{{ $sitemap->url }}" target="_blank">{{ $sitemap->url }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-300">
                                    {{ $sitemap->is_index ? 'False' : 'True' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-300">
                                    @php
                                    $queuedCount = $sitemap->queuedUrls()->count();
                                    $processedCount = $sitemap->sitemapUrls()->count();
                                    $totalUrls = $queuedCount + $processedCount;
                                    @endphp
                                    @if ($totalUrls > 0)
                                    {{ $processedCount }} of {{ $totalUrls }} URLs Processed
                                    @if ($processedCount < $totalUrls) <span class="loading-spinner-container">currently processing
                                        <svg class="loading-spinner" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                            <path d="M304 48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zm0 416a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM48 304a48 48 0 1 0 0-96 48 48 0 1 0 0 96zm464-48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM142.9 437A48 48 0 1 0 75 369.1 48 48 0 1 0 142.9 437zm0-294.2A48 48 0 1 0 75 75a48 48 0 1 0 67.9 67.9zM369.1 437A48 48 0 1 0 437 369.1 48 48 0 1 0 369.1 437z" />
                                        </svg>
                                        </span>
                                        @endif
                                        @else
                                        Not yet processed
                                        @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-300">
                                    @if ($totalUrls > 0)
                                    @php
                                    $lastProcessed = $sitemap->sitemapUrls()->latest()->first();
                                    @endphp
                                    @if ($lastProcessed)
                                    {{ $lastProcessed->created_at->diffForHumans() }}
                                    @else
                                    Waiting in queue
                                    @endif
                                    @else
                                    -
                                    @endif
                                </td>


                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium">
                                    @if ($totalUrls > 0)
                                    <!-- Toggle switch for processed sitemaps -->
                                    <div class="flex justify-center">
                                        <label class="inline-flex relative items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer auto-scan-toggle" data-sitemap-id="{{ $sitemap->id }}" {{ $sitemap->auto_scan ? 'checked' : '' }}>
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                        </label>

                                    </div>
                                    @else
                                    <form method="POST" action="{{ route('sitemap.queue', $sitemap->id) }}">
                                        @csrf
                                        <button type="submit" class="process-button text-indigo-600 hover:text-indigo-900 focus:outline-none">
                                            Process Sitemap
                                        </button>
                                    </form>
                                    @endif
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.auto-scan-toggle').forEach(function(toggle) {
            toggle.addEventListener('change', function() {
                const sitemapId = this.getAttribute('data-sitemap-id');
                const isChecked = this.checked;

                fetch(`/sitemaps/${sitemapId}/toggle-auto-scan`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            auto_scan: isChecked
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('Auto-scan status updated successfully.');
                        } else {
                            console.error('Failed to update auto-scan status.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });
        });
    });

    function confirmResync() {
        Swal.fire({
            title: 'Are you sure?',
            html: `
            <p id="alert-text" class="text-gray-700">This action will look for new sitemaps. Your existing sitemaps and data will remain. To clear your existing data, toggle below.</p>
            <label class="inline-flex relative items-center cursor-pointer mt-3">
                <input type="checkbox" id="overwriteSwitch" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
            </label>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, re-sync it!',
            cancelButtonText: 'Cancel',
            preConfirm: () => {
                // Get the state of the toggle switch
                const isOverwrite = document.getElementById('overwriteSwitch').checked;

                // Include the toggle state in the form data if it's toggled on
                if (isOverwrite) {
                    const form = document.getElementById('resync-form');
                    let input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'overwrite';
                    input.value = '1'; // Or any value that indicates the data should be overwritten
                    form.appendChild(input);
                }

                // Submit the form
                document.getElementById('resync-form').submit();
            }
        });
    }
</script>