<section id="sitemap-list" class="mt-8">
    <!-- Search Box -->
    <div class="mb-4">
        <input type="text" id="search-input" class="block w-full p-2 border rounded" placeholder="Search by URL">
    </div>

    <!-- Sitemap List Table -->
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sitemap URL</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Owner</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Auto Scanner</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody id="sitemap-list-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($sitemaps as $sitemap)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $sitemap->url }}
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                    @foreach($sitemap->users as $user)
                    {{ $user->name }}@if(!$loop->last), @endif
                    @endforeach
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                    @php
                    $queuedCount = $sitemap->queuedUrls()->count();
                    $processedCount = $sitemap->sitemapUrls()->count();
                    $totalUrls = $queuedCount + $processedCount;
                    @endphp
                    @if ($totalUrls > 0)
                    @if ($queuedCount > 0)
                    In Queue
                    @else
                    <div class="flex justify-center">
                        <label class="inline-flex relative items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer auto-scan-toggle" data-sitemap-id="{{ $sitemap->id }}" {{ $sitemap->auto_scan ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    @endif
                    @else
                    <form method="POST" action="{{ route('sitemap.queue', $sitemap->id) }}">
                        @csrf
                        <button type="submit" class="process-button text-indigo-600 hover:text-indigo-900 focus:outline-none">
                            Process Sitemap
                        </button>
                    </form> @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <svg class="w-6 h-6 inline-block transition duration-300 ease-in-out hover:opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                        <path d="M64 96c0-35.3 28.7-64 64-64l384 0c35.3 0 64 28.7 64 64l0 256-64 0 0-256L128 96l0 256-64 0L64 96zM0 403.2C0 392.6 8.6 384 19.2 384l601.6 0c10.6 0 19.2 8.6 19.2 19.2c0 42.4-34.4 76.8-76.8 76.8L76.8 480C34.4 480 0 445.6 0 403.2zM281 209l-31 31 31 31c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-48-48c-9.4-9.4-9.4-24.6 0-33.9l48-48c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9zM393 175l48 48c9.4 9.4 9.4 24.6 0 33.9l-48 48c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l31-31-31-31c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0z" />
                    </svg>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $sitemaps->links() }}
    </div>

    <!-- JavaScript for Search Box -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            const sitemapTableBody = document.getElementById('sitemap-list-body');

            function attachEventListeners() {
                const autoScanToggles = document.querySelectorAll('.auto-scan-toggle');
                autoScanToggles.forEach(toggle => {
                    toggle.addEventListener('change', function() {
                        const sitemapId = this.getAttribute('data-sitemap-id');
                        toggleAutoScan(sitemapId);
                    });
                });
            }

            searchInput.addEventListener('input', function() {
                const filter = searchInput.value;

                axios.get('/admin/sitemaps/search', {
                        params: {
                            query: filter
                        }
                    })
                    .then(response => {
                        const sitemaps = response.data;
                        sitemapTableBody.innerHTML = '';

                        sitemaps.forEach(sitemap => {
                            const sitemapRow = document.createElement('tr');

                            sitemapRow.innerHTML = `
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            ${sitemap.url}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    ${sitemap.users.map(user => user.name).join(', ')}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    ${sitemap.sitemapUrls && sitemap.sitemapUrls.length > 0
                                        ? (sitemap.queuedUrls && sitemap.queuedUrls.length > 0 
                                            ? 'In Queue' 
                                            : `<div class="flex justify-center">
                                                    <label class="inline-flex relative items-center cursor-pointer">
                                                        <input type="checkbox" class="sr-only peer auto-scan-toggle" data-sitemap-id="${sitemap.id}" ${sitemap.auto_scan ? 'checked' : ''}>
                                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                                    </label>
                                                </div>`)
                                        : `<form method="POST" action="/admin/sitemaps/${sitemap.id}/queue">
                                                @csrf
                                                <button type="submit" class="process-button text-indigo-600 hover:text-indigo-900 focus:outline-none">
                                                    Process Sitemap
                                                </button>
                                            </form>`}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <svg class="w-6 h-6 inline-block transition duration-300 ease-in-out hover:opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                                        <path d="M64 96c0-35.3 28.7-64 64-64l384 0c35.3 0 64 28.7 64 64l0 256-64 0 0-256L128 96l0 256-64 0L64 96zM0 403.2C0 392.6 8.6 384 19.2 384l601.6 0c10.6 0 19.2 8.6 19.2 19.2c0 42.4-34.4 76.8-76.8 76.8L76.8 480C34.4 480 0 445.6 0 403.2zM281 209l-31 31 31 31c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-48-48c-9.4-9.4-9.4-24.6 0-33.9l48-48c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9zM393 175l48 48c9.4 9.4 9.4 24.6 0 33.9l-48 48c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l31-31-31-31c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0z"/>
                                    </svg>
                                </td>
                            `;

                            sitemapTableBody.appendChild(sitemapRow);
                        });

                        // Attach event listeners to the new checkboxes
                        attachEventListeners();
                    })
                    .catch(error => {
                        console.error('Error fetching sitemaps:', error);
                    });
            });

            // Attach event listeners to the initial checkboxes
            attachEventListeners();
        });

        function toggleAutoScan(sitemapId) {
            axios.post('/admin/sitemaps/toggle-auto-scan', {
                    sitemap_id: sitemapId
                })
                .then(response => {
                    if (response.data.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Auto scan updated successfully.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload(); // Reload the page to reflect changes
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to update auto scan.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error updating auto scan:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while updating the auto scan.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
        }
    </script>
</section>