<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'Send fax online for $3 with FaxZen. Fast, secure online fax service. Upload PDF documents and send faxes instantly without a fax machine. Trusted by thousands.')">
    <title>@yield('title', 'FaxZen - Send Faxes Online')</title>
    <link rel="icon" type="image/gif" href="https://imagedelivery.net/k0P4EcPiouU_XzyGSmgmUw/fb1d0ae8-0136-4e6a-bc03-fe4e8d30f300/public">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'faxzen-purple': '#8b5cf6',
                        'faxzen-purple-dark': '#7c3aed',
                        'faxzen-blue': '#1e40af',
                        'faxzen-light': '#3b82f6',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen">
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-4xl mx-auto px-4 py-2">
            <div class="flex items-center justify-between">
                <a href="/"><img src="https://imagedelivery.net/k0P4EcPiouU_XzyGSmgmUw/42ca1bda-1138-4c31-ca0c-479017295900/public"></a>
                @if(!request()->is('/'))
                    <a href="/" class="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white px-4 py-2 rounded-md font-medium transition-all duration-200">
                        Send Fax
                    </a>
                @endif
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-8">
        @yield('content')
    </main>

    <footer class="bg-white border-t mt-16">
        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="text-center text-gray-600">
                <p>&copy; {{ date('Y') }} FaxZen. All rights reserved.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html> 