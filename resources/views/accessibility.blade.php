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
                <div class="max-w-4xl mx-auto px-4 py-8">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Accessibility Statement</h2>

                    <p class="text-gray-600 mb-6">
                        At Serp Surfer, we are committed to ensuring digital accessibility for people with disabilities. We are continually improving the user experience for everyone and applying the relevant accessibility standards to ensure our website and services are accessible to all users.
                    </p>

                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Our Commitment</h2>
                    <p class="text-gray-600 mb-6">
                        We strive to make our website and services as accessible as possible by adhering to the Web Content Accessibility Guidelines (WCAG) 2.1. These guidelines outline ways to make web content more accessible for people with disabilities and user-friendly for everyone.
                    </p>

                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Accessibility Features</h2>
                    <p class="text-gray-600 mb-6">
                        To enhance accessibility, we have implemented several features across our website:
                    </p>
                    <ul class="list-disc list-inside text-gray-600 mb-6">
                        <li>Text alternatives for non-text content</li>
                        <li>Keyboard navigation for all interactive elements</li>
                        <li>Responsive design to ensure compatibility with screen readers and other assistive technologies</li>
                        <li>Consistent structure and design patterns for ease of use</li>
                    </ul>

                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Ongoing Efforts</h2>
                    <p class="text-gray-600 mb-6">
                        We recognize that accessibility is an ongoing effort. We are constantly working to ensure that all aspects of our website and services are fully accessible. If you encounter any barriers or have suggestions for improving accessibility, we encourage you to reach out to us.
                    </p>

                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Contact Us</h2>
                    <p class="text-gray-600 mb-6">
                        If you experience any difficulty accessing any part of our website or have questions about our accessibility efforts, please don't hesitate to <a href="/contact" class="text-blue-500 hover:text-blue-700">contact us</a>. Your feedback is invaluable in helping us improve our accessibility.
                    </p>
                </div>

            </div>
        </main>
    </div>

    @include('layouts.footer')


</body>

</html>