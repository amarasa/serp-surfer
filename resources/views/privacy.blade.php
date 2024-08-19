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
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Privacy Policy</h2>

                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Introduction</h2>
                    <p class="text-gray-600 mb-6">
                        At Serp Surfer, we take your privacy seriously. This Privacy Policy outlines how we collect, use, and protect
                        your personal information when you use our website and services. By accessing or using our services, you agree
                        to the terms outlined in this policy.
                    </p>

                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Information We Collect</h2>
                    <p class="text-gray-600 mb-4">
                        We collect information to provide better services to our users. The types of information we collect include:
                    </p>
                    <ul class="list-disc pl-5 text-gray-600 mb-6">
                        <li class="mb-2">
                            <strong>Personal Information:</strong> When you sign up for an account or use our services, we may collect
                            personal information such as your name, email address, and payment details.
                        </li>
                        <li class="mb-2">
                            <strong>Service Data:</strong> We collect data related to your use of our services, including the URLs and
                            sitemaps you submit for indexing, your interactions with our platform, and your usage patterns.
                        </li>
                        <li class="mb-2">
                            <strong>Log Information:</strong> Our servers automatically record information ("log data") created by your
                            use of our services. Log data may include information such as your IP address, browser type, the referring
                            domain, and the pages you visit.
                        </li>
                        <li class="mb-2">
                            <strong>Cookies and Tracking Technologies:</strong> We use cookies and similar tracking technologies to
                            enhance your experience on our platform. Cookies help us understand how you interact with our services,
                            personalize your experience, and improve our offerings.
                        </li>
                    </ul>

                    <h2 class="text-xl font-semibold text-gray-700 mb-4">How We Use Your Information</h2>
                    <p class="text-gray-600 mb-6">
                        The information we collect is used to provide, maintain, and improve our services. Specifically, we may use
                        your information to:
                    </p>
                    <ul class="list-disc pl-5 text-gray-600 mb-6">
                        <li class="mb-2"><strong>Deliver Services:</strong> Process your submissions and manage your account.</li>
                        <li class="mb-2"><strong>Communicate:</strong> Send you updates, notifications, and other information regarding
                            your account or our services.
                        </li>
                        <li class="mb-2"><strong>Improve Services:</strong> Analyze user behavior and trends to improve the
                            functionality and usability of our platform.
                        </li>
                        <li class="mb-2"><strong>Security:</strong> Monitor and enhance the security of our services to protect your
                            data.
                        </li>
                    </ul>

                    <h2 class="text-xl font-semibold text-gray-700 mb-4">How We Share Your Information</h2>
                    <p class="text-gray-600 mb-4">
                        We do not sell or rent your personal information to third parties. We may share your information in the
                        following situations:
                    </p>
                    <ul class="list-disc pl-5 text-gray-600 mb-6">
                        <li class="mb-2"><strong>Service Providers:</strong> We may share your information with trusted third-party
                            service providers who assist us in delivering our services, such as payment processors and cloud hosting
                            services. These providers are obligated to protect your information and only use it as directed by us.
                        </li>
                        <li class="mb-2"><strong>Legal Requirements:</strong> We may disclose your information if required to do so by
                            law or if we believe that such action is necessary to comply with legal obligations or to protect the
                            security or integrity of our platform.
                        </li>
                    </ul>

                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Data Security</h2>
                    <p class="text-gray-600 mb-6">
                        We implement industry-standard security measures to protect your information from unauthorized access,
                        alteration, disclosure, or destruction. However, no method of transmission over the internet or electronic
                        storage is 100% secure, and we cannot guarantee absolute security.
                    </p>

                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Your Choices and Rights</h2>
                    <p class="text-gray-600 mb-4">
                        You have the right to:
                    </p>
                    <ul class="list-disc pl-5 text-gray-600 mb-6">
                        <li class="mb-2"><strong>Access:</strong> Request a copy of the personal information we hold about you.</li>
                        <li class="mb-2"><strong>Correction:</strong> Request that we correct any inaccurate or incomplete information
                            about you.
                        </li>
                        <li class="mb-2"><strong>Deletion:</strong> Request that we delete your personal information, subject to certain
                            exceptions.
                        </li>
                        <li class="mb-2"><strong>Opt-Out:</strong> Unsubscribe from marketing communications by following the
                            instructions provided in our emails.
                        </li>
                    </ul>

                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Changes to This Privacy Policy</h2>
                    <p class="text-gray-600 mb-6">
                        We may update this Privacy Policy from time to time. Any changes will be posted on this page, and if the changes
                        are significant, we will provide a more prominent notice. We encourage you to review this policy periodically to
                        stay informed about how we are protecting your information.
                    </p>

                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Contact Us</h2>
                    <p class="text-gray-600 mb-6">
                        If you have any questions or concerns about this Privacy Policy or our data practices, please contact us at our
                        <a href="/contact" class="text-blue-600 hover:text-blue-800">Contact Page</a>.
                    </p>
                </div>

            </div>
        </main>
    </div>

    @include('layouts.footer')


</body>

</html>