<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin') }}
        </h2>
    </x-slot>

    <!-- Main content -->
    <div class="flex ">
        <!-- Sidebar -->
        <div class="w-[300px] bg-white border-r-[1px] border-neutral-300">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <nav class="space-y-4">
                    <a href="{{ route('admin.users') }}" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-md">
                        User List
                    </a>
                    <a href="{{ route('admin.sitemaps') }}" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-md">
                        Sitemap List
                    </a>
                    <a href="{{ route('admin.urls') }}" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-md">
                        URL List
                    </a>
                    <a href="/horizon" target="_blank" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-md">
                        Horizon
                    </a>
                    <a href="{{ route('service.workers') }}" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-md">
                        Google Service Workers
                    </a>
                    </a>
                    <a href="{{ route('server.controls') }}" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-md">
                        Server Controls
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 bg-white dark:bg-gray-800">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                @if (Route::currentRouteName() == 'admin.users')
                @include('admin.partials.user-list')
                @elseif (Route::currentRouteName() == 'admin.sitemaps')
                @include('admin.partials.sitemap-list')
                @elseif (Route::currentRouteName() == 'admin.urls')
                @include('admin.partials.urls-list')
                @elseif (Route::currentRouteName() == 'service.workers')
                @include('admin.partials.google-service-worker')
                @elseif (Route::currentRouteName() == 'server.controls')
                @include('admin.partials.server-controls')
                @else
                <p>Select an option from the menu.</p>
                @endif
            </div>
        </div>
        <!-- / Main content -->
    </div>

</x-app-layout>