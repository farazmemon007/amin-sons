<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Ameen & Sons Corporate ERP') }}</title>

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        
        <!-- FontAwesome Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Tailwind CSS CDN (Ensures 100% reliable rendering on any live hosting) -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            navy: {
                                800: '#1e3a5f',
                                900: '#0f1f38',
                                950: '#091322',
                            },
                            gold: {
                                500: '#c8973a',
                                600: '#b28128',
                            }
                        }
                    }
                }
            }
        </script>

        <style>
            body {
                font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            }
            svg {
                max-width: 100%;
                height: auto;
            }
        </style>

        @if (file_exists(public_path('build/manifest.json')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="font-sans text-gray-900 antialiased bg-slate-900">
        <div class="min-h-screen flex flex-col justify-center items-center px-4 py-8 bg-gradient-to-br from-slate-950 via-navy-900 to-slate-900 relative overflow-hidden">
            <!-- Decorative Glow Elements -->
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="w-full sm:max-w-md relative z-10">
                {{ $slot }}
            </div>
            
            <div class="mt-6 text-center text-xs text-slate-400">
                &copy; {{ date('Y') }} Ameen & Sons Corporate ERP &mdash; All Rights Reserved.
            </div>
        </div>
    </body>
</html>
