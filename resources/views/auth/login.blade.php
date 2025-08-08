@extends('layouts.app')

@section('title', 'Access Your Account - FaxZen')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-lg shadow-md p-8">
        <div class="text-center mb-8">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Sign in to FaxZen</h2>
            <p class="text-gray-600 mt-2">We'll send you a secure link to sign in</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                <ul class="text-red-600 text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-green-600 text-sm">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session('info'))
            <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
                <p class="text-blue-600 text-sm">{{ session('info') }}</p>
            </div>
        @endif

        <form action="{{ route('account.send-access-link') }}" method="POST" class="space-y-6">
            @csrf
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Your Email Address <span class="text-red-500">*</span>
                </label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="{{ old('email') }}"
                       placeholder="john@example.com"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-faxzen-blue focus:border-faxzen-blue text-lg"
                       required 
                       autofocus>
                <p class="mt-1 text-sm text-gray-500">We'll send a secure link to this email address</p>
            </div>

            <div>
                <button type="submit" 
                        class="w-full bg-faxzen-blue text-white py-3 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-faxzen-blue transition-colors font-semibold text-lg">
                    🔗 Send Secure Access Link
                </button>
            </div>
        </form>

        <div class="mt-8 bg-blue-50 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">🛡️ Passwordless & Secure</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>No passwords to remember or lose</li>
                            <li>Links expire in 1 hour for security</li>
                            <li>Each link works only once</li>
                            <li>More secure than traditional passwords</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 text-center">
            <div class="text-sm text-gray-500">
                <p>New to FaxZen? <a href="{{ route('fax.step1') }}" class="text-faxzen-blue hover:text-blue-700 font-medium">Send your first fax</a></p>
            </div>
        </div>
    </div>
</div>
@endsection