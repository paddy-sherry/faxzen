@extends('layouts.app')

@section('title', 'Payment Successful | FaxZen')

@section('content')
<div class="bg-white rounded-lg shadow-md p-8 text-center">
    <div class="mb-8">
        <div class="flex items-center justify-center mb-6">
            <div class="bg-green-100 rounded-full p-4">
                <svg class="w-16 h-16 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
        <h2 class="text-3xl font-bold text-gray-800 mb-4">Payment Successful!</h2>
        <p class="text-xl text-gray-600 mb-2">Your fax is being sent now</p>
        <p class="text-gray-500">You'll receive an email confirmation shortly</p>
    </div>

    <div class="bg-gray-50 rounded-lg p-6 mb-8">
        <h3 class="font-semibold text-gray-800 mb-4">Fax Details</h3>
        <div class="text-left max-w-md mx-auto space-y-2">
            <div class="flex justify-between">
                <span class="text-gray-600">To:</span>
                <span class="font-medium">{{ $faxJob->recipient_number }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Document:</span>
                <span class="font-medium">{{ $faxJob->file_original_name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Email:</span>
                <span class="font-medium">{{ $faxJob->sender_email }}</span>
            </div>
            <hr class="my-3">
            <div class="flex justify-between font-semibold">
                <span>Amount Paid:</span>
                <span class="text-green-600">${{ number_format($faxJob->amount, 2) }}</span>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-blue-50 rounded-lg p-4">
            <h4 class="font-semibold text-faxzen-blue mb-2">What happens now?</h4>
            <ul class="text-sm text-gray-700 space-y-1">
                <li>• Your fax is being processed and sent immediately</li>
                <li>• You'll receive an email confirmation when delivery is complete</li>
                <li>• If there are any issues, we'll retry automatically</li>
            </ul>
        </div>

        <div class="pt-4">
            <a href="{{ route('fax.step1') }}" 
               class="inline-block bg-faxzen-blue text-white py-3 px-8 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-faxzen-blue transition-colors font-semibold">
                Send Another Fax
            </a>
        </div>
    </div>
</div>

<div class="mt-8 bg-green-50 rounded-lg p-6">
    <div class="text-center">
        <h3 class="text-lg font-semibold text-green-800 mb-2">Thank you for using FaxZen!</h3>
        <p class="text-green-700">We've made faxing simple, fast, and reliable.</p>
    </div>
</div>

<!-- Google Ads Conversion Tracking -->
@if(config('services.google.ads_conversion_id') && !session('conversion_tracked_' . $faxJob->id) && $faxJob->amount > 0)
<script>
    // Track Google Ads conversion for successful payment (only for actual payments, not credit usage)
    gtag('event', 'conversion', {
        'send_to': '{{ config('services.google.ads_conversion_id') }}/{{ config('services.google.ads_conversion_label') }}',
        'value': {{ $faxJob->amount }},
        'currency': 'USD',
        'transaction_id': '{{ $faxJob->hash }}',
        'custom_parameters': {
            'payment_type': '{{ $faxJob->amount == 20 ? "bulk_package" : "single_fax" }}',
            'recipient_country': '{{ substr($faxJob->recipient_number, 0, 2) == "+1" ? "US" : "International" }}',
            'page': 'success'
        }
    });
    
    // Enhanced conversion data (if customer email available)
    @if($faxJob->sender_email)
    gtag('config', '{{ config('services.google.ads_id') }}', {
        'customer_lifetime_value': {{ $faxJob->amount }},
        'user_data': {
            'email_address': '{{ hash('sha256', strtolower(trim($faxJob->sender_email))) }}',
            'phone_number': '{{ hash('sha256', preg_replace("/[^0-9]/", "", $faxJob->recipient_number)) }}'
        }
    });
    @endif
    
    console.log('Google Ads conversion tracked on success page:', {
        value: {{ $faxJob->amount }},
        transaction_id: '{{ $faxJob->hash }}',
        payment_type: '{{ $faxJob->amount == 20 ? "bulk_package" : "single_fax" }}'
    });
</script>
@php
    // Mark conversion as tracked in session to prevent duplicate tracking
    session(['conversion_tracked_' . $faxJob->id => true]);
@endphp
@endif
@endsection 