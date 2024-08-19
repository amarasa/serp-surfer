<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://unpkg.com/tippy.js@6/dist/tippy.css" />
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="bg-gray-100 dark:bg-gray-900">
        @include('layouts.navigation')
        @include('layouts.header')



        <!-- Page Content -->
        <main>
            @if (session('info'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition.opacity.duration.500ms @click="show = false" class="fixed top-4 right-4 max-w-xs bg-white border border-gray-200 rounded-xl shadow-lg dark:bg-neutral-800 dark:border-neutral-700" role="alert">
                <div class="flex p-4">
                    <div class="flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#3fa7d6" viewBox="0 0 512 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                            <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336l24 0 0-64-24 0c-13.3 0-24-10.7-24-24s10.7-24 24-24l48 0c13.3 0 24 10.7 24 24l0 88 8 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-80 0c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z" />
                        </svg>
                    </div>
                    <div class="ms-3">
                        <p class="text-sm text-gray-700 dark:text-neutral-400">
                            {{ session('info') }}
                        </p>
                    </div>
                </div>
            </div>
            @endif

            @if (session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition.opacity.duration.500ms @click="show = false" class="fixed top-4 right-4 max-w-xs bg-white border border-gray-200 rounded-xl shadow-lg dark:bg-neutral-800 dark:border-neutral-700" role="alert">
                <div class="flex p-4">
                    <div class="flex-shrink-0">
                        <svg class="flex-shrink-0 size-4 text-teal-500 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"></path>
                        </svg>
                    </div>
                    <div class="ms-3">
                        <p class="text-sm text-gray-700 dark:text-neutral-400">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            </div>
            @endif

            @if (session('error'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition.opacity.duration.500ms @click="show = false" class="fixed top-4 right-4 max-w-xs bg-white border border-gray-200 rounded-xl shadow-lg dark:bg-neutral-800 dark:border-neutral-700" role="alert">
                <div class="flex p-4">
                    <div class="flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#ef233c" viewBox="0 0 512 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                            <path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c-9.4 9.4-9.4 24.6 0 33.9l47 47-47 47c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l47-47 47 47c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-47-47 47-47c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-47 47-47-47c-9.4-9.4-24.6-9.4-33.9 0z" />
                        </svg>
                    </div>
                    <div class="ms-3">
                        <p class="text-sm text-gray-700 dark:text-neutral-400">
                            {{ session('error') }}
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <x-slot name="header">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Dashboard') }}
                </h2>
            </x-slot>

            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h2>Our Mission</h2>
                        <p>Our mission is to empower webmasters, SEO professionals, and businesses of all sizes by providing them with tools that streamline the often complex process of ensuring their content is visible in Google’s search results. We strive to offer an intuitive and user-friendly experience that saves you time and enhances your digital presence.</p>
                        <h2>What We Do </h2>
                        <p>Serp Surfer provides an automated way to manage your sitemaps and submit URLs for indexing to Google. Our platform takes care of the heavy lifting, allowing you to focus on creating great content while we ensure that content is discoverable by your audience. From integrating with Google Search Console to managing multiple service accounts, Serp Surfer is your partner in maximizing your online visibility.</p>
                        <h2>Why Choose Us?</h2>
                        <ol>
                            <li><span class="font-bold">Automation:</span> We simplify the process of submitting your URLs for indexing. No more manual submissions or tedious monitoring.</li>
                            <li><span class="font-bold">Efficiency:</span> Our platform uses advanced technology to automatically handle the distribution of indexing requests across multiple Google service accounts, optimizing your daily quotas and improving your chances of getting indexed quickly.</li>
                            <li><span class="font-bold">Customization:</span> We offer flexible solutions tailored to meet the unique needs of your website or business. Whether you're managing a small blog or a large-scale operation, our tools adapt to your requirements.</li>
                            <li><span class="font-bold">Reliability:</span> With our user-friendly dashboard and real-time insights, you can trust Serp Surfer to provide accurate and up-to-date information about the status of your URLs and sitemaps.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </main>
    </div>

    @include('layouts.footer')


</body>

</html>