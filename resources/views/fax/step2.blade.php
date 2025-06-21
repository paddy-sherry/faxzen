@extends('layouts.app')

@section('title', 'Step 2 of 3 - Your Details | FaxZen')

@section('content')
<div class="bg-white rounded-lg shadow-md p-8">
    <div class="mb-8">
        <div class="flex items-center justify-center mb-4">
            <div class="flex items-center space-x-4">
                <div class="flex items-center justify-center w-8 h-8 bg-green-500 text-white rounded-full font-semibold">✓</div>
                <div class="w-16 h-0.5 bg-green-500"></div>
                <div class="flex items-center justify-center w-8 h-8 bg-faxzen-blue text-white rounded-full font-semibold">2</div>
                <div class="w-16 h-0.5 bg-gray-300"></div>
                <div class="flex items-center justify-center w-8 h-8 bg-gray-300 text-gray-600 rounded-full font-semibold">3</div>
            </div>
        </div>
        <h2 class="text-2xl font-bold text-center text-gray-800">Your Details</h2>
        <p class="text-center text-gray-600 mt-2">Enter your contact information</p>
    </div>

    <div class="bg-gray-50 rounded-lg p-4 mb-6">
        <h3 class="font-semibold text-gray-800 mb-2">Fax Summary</h3>
        <div class="text-sm text-gray-600 space-y-1">
            <p><strong>To:</strong> {{ $faxJob->recipient_number }}</p>
            <p><strong>Document:</strong> {{ $faxJob->file_original_name }}</p>
            <p><strong>Price:</strong> ${{ number_format($faxJob->amount, 2) }}</p>
        </div>
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

    <form action="{{ route('fax.step2', $faxJob->hash) }}" method="POST" class="space-y-6">
        @csrf
        
        <div>
            <label for="sender_name" class="block text-sm font-medium text-gray-700 mb-2">
                Your Full Name <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   id="sender_name" 
                   name="sender_name" 
                   value="{{ old('sender_name', $faxJob->sender_name) }}"
                   placeholder="John Doe"
                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-faxzen-blue focus:border-faxzen-blue"
                   required>
        </div>

        <div>
            <label for="sender_email" class="block text-sm font-medium text-gray-700 mb-2">
                Your Email Address <span class="text-red-500">*</span>
            </label>
            <input type="email" 
                   id="sender_email" 
                   name="sender_email" 
                   value="{{ old('sender_email', $faxJob->sender_email) }}"
                   placeholder="john@example.com"
                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-faxzen-blue focus:border-faxzen-blue"
                   required>
            <p class="mt-1 text-sm text-gray-500">We'll send you a confirmation when your fax is sent</p>
        </div>

        <div class="flex space-x-4 pt-4">
            <a href="{{ route('fax.step1') }}" 
               class="flex-1 bg-gray-300 text-gray-700 py-3 px-4 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors font-semibold text-center">
                ← Back
            </a>
            <button type="submit" 
                    class="flex-1 bg-faxzen-blue text-white py-3 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-faxzen-blue transition-colors font-semibold">
                Continue to Payment →
            </button>
        </div>
    </form>
</div>

<div class="mt-8 bg-green-50 rounded-lg p-6">
    <div class="flex items-center mb-4">
        <svg class="w-6 h-6 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <h3 class="text-lg font-semibold text-green-800">What happens next?</h3>
    </div>
    <ul class="text-sm text-green-700 space-y-2">
        <li class="flex items-start">
            <span class="mr-2">•</span>
            <span>Complete payment securely with Stripe</span>
        </li>
        <li class="flex items-start">
            <span class="mr-2">•</span>
            <span>Your fax will be sent immediately after payment</span>
        </li>
        <li class="flex items-start">
            <span class="mr-2">•</span>
            <span>You'll receive an email confirmation when sent</span>
        </li>
    </ul>
</div>
@endsection 