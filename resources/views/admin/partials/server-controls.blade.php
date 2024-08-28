<section id="server-controls" class="mt-8 max-w-4xl mx-auto">
    <div class="bg-white dark:bg-gray-800 p-6">
        <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-6">Server Controls</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Form for Force Run AutoScanSitemapsJob -->
            <form method="POST" action="{{ route('forceRunAutoScan') }}">
                @csrf
                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded-lg transition duration-150 ease-in-out">
                    <span class="block">Force Run</span>
                    <span class="block text-sm">AutoScanSitemapsJob</span>
                </button>
            </form>
            <!-- Force run RemoveOldUrlsJob job button -->
            <form method="POST" action="{{ route('forceRemoveOldJobs') }}">
                @csrf
                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded-lg transition duration-150 ease-in-out">
                    <span class="block">Force Run</span>
                    <span class="block text-sm">RemoveOldUrlsJob</span>
                </button>
            </form>
            <!-- Force run SubmitIndexingJob job button -->
            <form method="POST" action="{{ route('forceIndexing') }}">
                @csrf
                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded-lg transition duration-150 ease-in-out">
                    <span class="block">Force Run</span>
                    <span class="block text-sm">SubmitIndexingJob</span>
                </button>
            </form>
            <!-- Force run CheckIndexingStatusJob job button -->
            <form method="POST" action="{{ route('forceCheckIndexStatus') }}">
                @csrf
                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded-lg transition duration-150 ease-in-out">
                    <span class="block">Force Run</span>
                    <span class="block text-sm">CheckIndexingStatusJob</span>
                </button>
            </form>
            <!-- Clear cache button -->
            <form method="POST" action="{{ route('clearCache') }}">
                @csrf
                <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-4 rounded-lg transition duration-150 ease-in-out">
                    <span class="block">Clear</span>
                    <span class="block text-sm">Cache</span>
                </button>
            </form>
        </div>
    </div>
</section>