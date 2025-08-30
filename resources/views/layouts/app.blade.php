<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'Send fax online for $5 with FaxZen. Fast, secure online fax service. Upload PDF documents and send faxes instantly without a fax machine. Trusted by thousands.')">
    @stack('head')
    @if(!trim($__env->yieldPushContent('head')))
        <link rel="canonical" href="{{ url()->current() }}">
    @endif
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
    <script defer data-domain="faxzen.com" src="https://plausible.io/js/script.tagged-events.js"></script>
    
    <!-- Google Ads Global Site Tag -->
    @if(config('services.google.ads_id'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.ads_id') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        
        gtag('config', '{{ config('services.google.ads_id') }}');
        
        @if(config('services.google.analytics_id'))
        gtag('config', '{{ config('services.google.analytics_id') }}');
        @endif
    </script>
    @endif
    
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen">
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-4xl mx-auto px-4 py-2">
            <div class="flex items-center justify-between">
                <a href="/"><img src="https://imagedelivery.net/k0P4EcPiouU_XzyGSmgmUw/42ca1bda-1138-4c31-ca0c-479017295900/public"></a>
                <a href="https://ie.trustpilot.com/review/faxzen.com" target="_blank"><img width="200" src="/images/tp4.png"></a>
                
                <!-- Navigation Links -->
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="{{ route('fax.step1') }}#pricing" class="text-gray-600 hover:text-purple-600 transition-colors duration-200 font-medium">Pricing</a>
                    <a href="{{ route('blog.index') }}" class="text-gray-600 hover:text-purple-600 transition-colors duration-200 font-medium">Blog</a>
                    <a href="{{ route('contact') }}" class="text-gray-600 hover:text-purple-600 transition-colors duration-200 font-medium">Contact</a>
                    
                    @auth
                        <div class="flex items-center space-x-4">
                            <a href="{{ route('account.dashboard') }}" class="text-gray-600 hover:text-purple-600 transition-colors duration-200 font-medium">
                                My Account
                            </a>
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-gray-600 hover:text-purple-600 transition-colors duration-200 font-medium">
                                    Logout
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-purple-600 transition-colors duration-200 font-medium">Login</a>
                    @endauth
                </nav>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-button" class="md:hidden p-2 text-gray-600 hover:text-purple-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                @if(!request()->is('/'))
                    <a href="/" class="hidden md:block bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white px-4 py-2 rounded-md font-medium transition-all duration-200">
                        Send Fax
                    </a>
                @endif
            </div>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="md:hidden hidden border-t border-gray-200 mt-2 pt-2">
                <div class="flex flex-col space-y-2">
                    <a href="{{ route('blog.index') }}" class="text-gray-600 hover:text-purple-600 transition-colors duration-200 font-medium py-2">Blog</a>
                    <a href="{{ route('fax.step1') }}#pricing" class="text-gray-600 hover:text-purple-600 transition-colors duration-200 font-medium py-2">Pricing</a>
                    <a href="{{ route('contact') }}" class="text-gray-600 hover:text-purple-600 transition-colors duration-200 font-medium py-2">Contact</a>
                    
                    @auth
                        <div class="border-t border-gray-200 pt-2 mt-2">
                            <a href="{{ route('account.dashboard') }}" class="text-gray-600 hover:text-purple-600 transition-colors duration-200 font-medium py-2 block">My Account</a>
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-gray-600 hover:text-purple-600 transition-colors duration-200 font-medium py-2 text-left w-full">
                                    Logout
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-purple-600 transition-colors duration-200 font-medium py-2">Login</a>
                    @endauth
                    
                    @if(!request()->is('/'))
                        <a href="/" class="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white px-4 py-2 rounded-md font-medium transition-all duration-200 text-center mt-2">
                            Send Fax
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-8">
        @yield('content')
    </main>

    <footer class="bg-white border-t mt-16">
        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="text-center text-gray-600">
                <div class="mb-4">
                    <a href="{{ route('blog.index') }}" class="text-gray-600 hover:text-purple-600 transition-colors duration-200 mx-3">Blog</a>
                    <span class="text-gray-400">|</span>
                    <a href="{{ route('fax.step1') }}#pricing" class="text-gray-600 hover:text-purple-600 transition-colors duration-200 mx-3">Pricing</a>
                    <span class="text-gray-400">|</span>
                    <a href="{{ route('contact') }}" class="text-gray-600 hover:text-purple-600 transition-colors duration-200 mx-3">Contact</a>
                </div>
                <p>&copy; {{ date('Y') }} FaxZen. All rights reserved.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')

    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
        });
    </script>
</body>
</html> 