<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - FaxZen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'faxzen-purple': '#8b5cf6',
                        'faxzen-purple-dark': '#7c3aed',
                    }
                }
            }
        }
    </script>
    <style>
        .content-editor {
            min-height: 300px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <a href="/" class="text-xl font-bold text-faxzen-purple">FaxZen Admin</a>
                    </div>
                    
                    <!-- Navigation Links -->
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="{{ route('admin.fax-jobs') }}" 
                           class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm
                           {{ request()->routeIs('admin.fax-jobs') ? 'border-faxzen-purple text-faxzen-purple' : '' }}">
                            Fax Jobs
                        </a>
                        <a href="{{ route('admin.blog.index') }}" 
                           class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm
                           {{ request()->routeIs('admin.blog.*') ? 'border-faxzen-purple text-faxzen-purple' : '' }}">
                            Blog Posts
                        </a>
                    </div>
                </div>
                
                <!-- Right side -->
                <div class="flex items-center">
                    <a href="/" class="text-gray-500 hover:text-gray-700 text-sm">
                        ← Back to Site
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Page Header -->
        <div class="mb-6">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        @yield('page-title')
                    </h2>
                    @hasSection('page-description')
                        <p class="mt-1 text-sm text-gray-500">
                            @yield('page-description')
                        </p>
                    @endif
                </div>
                @hasSection('page-actions')
                    <div class="mt-4 flex md:mt-0 md:ml-4">
                        @yield('page-actions')
                    </div>
                @endif
            </div>
        </div>

        <!-- Main Content -->
        <div class="bg-white shadow rounded-lg">
            @yield('content')
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Auto-hide flash messages after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('[role="alert"]');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        // Confirm delete actions
        function confirmDelete(message = 'Are you sure you want to delete this item?') {
            return confirm(message);
        }
    </script>
    
    @stack('scripts')
</body>
</html> 