@extends('layouts.app')

@section('title', 'Account Settings - FaxZen')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-3xl font-bold text-gray-900">Account Settings</h1>
        <p class="text-gray-600 mt-1">Manage your account information and preferences</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Navigation -->
        <div class="lg:col-span-1">
            <nav class="bg-white rounded-lg shadow-md p-6">
                <ul class="space-y-2">
                    <li>
                        <a href="#profile" class="block px-3 py-2 text-sm font-medium text-faxzen-blue bg-blue-50 rounded-md">
                            Profile Information
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('account.dashboard') }}" class="block px-3 py-2 text-sm font-medium text-gray-700 hover:text-faxzen-blue hover:bg-blue-50 rounded-md">
                            ← Back to Dashboard
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Profile Information -->
            <div id="profile" class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Profile Information</h2>
                
                @if (session('success'))
                    <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                        <p class="text-green-600 text-sm">{{ session('success') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                        <ul class="text-red-600 text-sm space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('account.settings.update') }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', Auth::user()->name) }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-faxzen-blue focus:border-faxzen-blue"
                               required>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', Auth::user()->email) }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-faxzen-blue focus:border-faxzen-blue"
                               required>
                        <p class="mt-1 text-sm text-gray-500">This is where we'll send fax confirmations and account updates</p>
                    </div>

                    <!-- Account Stats -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-gray-900 mb-3">Account Information</h3>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Fax Credits:</span>
                                <span class="font-medium text-gray-900">{{ Auth::user()->fax_credits }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Member Since:</span>
                                <span class="font-medium text-gray-900">{{ Auth::user()->created_at->format('M Y') }}</span>
                            </div>
                            @if(Auth::user()->credits_purchased_at)
                            <div>
                                <span class="text-gray-500">Credits Purchased:</span>
                                <span class="font-medium text-gray-900">{{ Auth::user()->credits_purchased_at->format('M j, Y') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <button type="submit" 
                                class="bg-faxzen-blue text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-faxzen-blue transition-colors font-semibold">
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>

            <!-- Passwordless Authentication Info -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">🛡️ Passwordless Authentication</h2>
                
                <div class="bg-green-50 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">Secure & Convenient</h3>
                            <div class="mt-2 text-sm text-green-700">
                                <p>Your account uses magic link authentication for maximum security:</p>
                                <ul class="list-disc list-inside mt-2 space-y-1">
                                    <li>No passwords to remember or lose</li>
                                    <li>No risk of password breaches</li>
                                    <li>Secure access links sent to your email</li>
                                    <li>Links expire automatically for security</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 text-sm text-gray-600">
                    <p><strong>How to sign in:</strong> Just enter your email address on the login page and we'll send you a secure access link.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection