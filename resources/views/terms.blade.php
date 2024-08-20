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
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Terms and Conditions</h2>

                    <h2 class="text-xl font-semibold text-gray-700 mb-4">1. Introduction</h2>
                    <p class="text-gray-600 mb-6">
                        Welcome to Serp Surfer. By accessing and using our website and services, you agree to comply with and be bound by the following terms and conditions. Please review these terms carefully. If you do not agree with any of these terms, you should not use our website or services.
                    </p>

                    <h2 class="text-xl font-semibold text-gray-700 mb-4">2. Use of Services</h2>
                    <p class="text-gray-600 mb-6">
                        Our services are provided for your personal and business use. You agree not to use the services for any illegal or unauthorized purpose. You must not, in the use of the service, violate any laws in your jurisdiction.
                    </p>

                    <h2 class="text-xl font-semibold text-gray-700 mb-4">3. Account Registration</h2>
                    <p class="text-gray-600 mb-6">
                        To access certain features of our services, you may be required to register an account with us. You agree to provide accurate, current, and complete information during the registration process and to update such information to keep it accurate, current, and complete. You are responsible for safeguarding your account credentials and for any activities or actions under your account.
                    </p>

                    <h2 class="text-xl font-semibold text-gray-700 mb-4">4. Intellectual Property</h2>
                    <p class="text-gray-600 mb-6">
                        The content, trademarks, service marks, and logos available on our website and through our services are owned by or licensed to Serp Surfer. You are not permitted to copy, reproduce, distribute, or create derivative works based on any of our content without prior written permission from us.
                    </p>

                    <h2 class="text-xl font-semibold text-gray-700 mb-4">5. Limitation of Liability</h2>
                    <p class="text-gray-600 mb-6">
                        Serp Surfer is not liable for any direct, indirect, incidental, special, or consequential damages resulting from your use of our services or inability to use our services. This includes, but is not limited to, damages for loss of profits, goodwill, use, data, or other intangible losses.
                    </p>

                    <h2 class="text-xl font-semibold text-gray-700 mb-4">6. Termination</h2>
                    <p class="text-gray-600 mb-6">
                        We reserve the right to suspend or terminate your account and access to our services at our sole discretion, without notice or liability, if you breach any of these terms or for any other reason.
                    </p>

                    <h2 class="text-xl font-semibold text-gray-700 mb-4">7. Governing Law</h2>
                    <p class="text-gray-600 mb-6">
                        These terms and conditions are governed by and construed in accordance with the laws of the jurisdiction in which Serp Surfer operates, without regard to its conflict of law provisions.
                    </p>

                    <h2 class="text-xl font-semibold text-gray-700 mb-4">8. Changes to Terms</h2>
                    <p class="text-gray-600 mb-6">
                        We reserve the right to modify or replace these terms at any time. If a revision is material, we will provide at least 30 days' notice before any new terms take effect. What constitutes a material change will be determined at our sole discretion.
                    </p>

                    <h2 class="text-xl font-semibold text-gray-700 mb-4">9. Contact Us</h2>
                    <p class="text-gray-600 mb-6">
                        If you have any questions about these Terms and Conditions, please contact us through our <a href="/contact" class="text-blue-500 hover:text-blue-700">contact page</a>.
                    </p>
                </div>

            </div>
        </main>
    </div>

    @include('layouts.footer')


</body>

</html>