@extends('layouts.app')

@section('title', 'Fax Status - Tracking Your Delivery | FaxZen')

@section('content')
<div class="bg-white rounded-lg shadow-md p-8">
    <div class="mb-8 text-center">
        <h2 class="text-3xl font-bold text-gray-800 mb-4">Fax Status</h2>
        <p class="text-gray-600">Tracking your fax delivery in real-time</p>
    </div>

    <!-- Progress Bar -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <span class="text-sm font-medium text-gray-700">Progress</span>
            <span class="text-sm font-medium text-gray-700">{{ $faxJob->getCompletionPercentage() }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3">
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-3 rounded-full transition-all duration-500" 
                 style="width: {{ $faxJob->getCompletionPercentage() }}%"></div>
        </div>
    </div>

    <!-- Status Steps -->
    <div class="mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Step 1: Preparing -->
            <div class="text-center">
                <div class="flex items-center justify-center mb-3">
                    @if($faxJob->getCurrentStep() > 1)
                        <div class="bg-green-100 rounded-full p-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    @elseif($faxJob->getCurrentStep() == 1)
                        <div class="bg-blue-100 rounded-full p-3 animate-pulse">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                    @else
                        <div class="bg-gray-100 rounded-full p-3">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                    @endif
                </div>
                <h4 class="font-semibold {{ $faxJob->getCurrentStep() >= 1 ? 'text-gray-800' : 'text-gray-400' }}">
                    Preparing Fax
                </h4>
                <p class="text-sm {{ $faxJob->getCurrentStep() >= 1 ? 'text-gray-600' : 'text-gray-400' }}">
                    Processing your document
                </p>
                @if($faxJob->prepared_at)
                    <p class="text-xs text-green-600 mt-1">
                        {{ $faxJob->prepared_at->format('g:i A') }}
                    </p>
                @endif
            </div>

            <!-- Step 2: Sending -->
            <div class="text-center">
                <div class="flex items-center justify-center mb-3">
                    @if($faxJob->getCurrentStep() > 2)
                        <div class="bg-green-100 rounded-full p-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    @elseif($faxJob->getCurrentStep() == 2)
                        <div class="bg-blue-100 rounded-full p-3 animate-pulse">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                        </div>
                    @else
                        <div class="bg-gray-100 rounded-full p-3">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                        </div>
                    @endif
                </div>
                <h4 class="font-semibold {{ $faxJob->getCurrentStep() >= 2 ? 'text-gray-800' : 'text-gray-400' }}">
                    Sending
                </h4>
                <p class="text-sm {{ $faxJob->getCurrentStep() >= 2 ? 'text-gray-600' : 'text-gray-400' }}">
                    Transmitting to recipient
                </p>
                @if($faxJob->sending_started_at)
                    <p class="text-xs text-green-600 mt-1">
                        {{ $faxJob->sending_started_at->format('g:i A') }}
                    </p>
                @endif
            </div>

            <!-- Step 3: Delivered -->
            <div class="text-center">
                <div class="flex items-center justify-center mb-3">
                    @if($faxJob->getCurrentStep() > 3)
                        <div class="bg-green-100 rounded-full p-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    @elseif($faxJob->getCurrentStep() == 3)
                        <div class="bg-green-100 rounded-full p-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    @else
                        <div class="bg-gray-100 rounded-full p-3">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    @endif
                </div>
                <h4 class="font-semibold {{ $faxJob->getCurrentStep() >= 3 ? 'text-gray-800' : 'text-gray-400' }}">
                    Delivered
                </h4>
                <p class="text-sm {{ $faxJob->getCurrentStep() >= 3 ? 'text-gray-600' : 'text-gray-400' }}">
                    Successfully received
                </p>
                @if($faxJob->delivered_at)
                    <p class="text-xs text-green-600 mt-1">
                        {{ $faxJob->delivered_at->format('g:i A') }}
                    </p>
                @endif
            </div>

            <!-- Step 4: Email Confirmation -->
            <div class="text-center">
                <div class="flex items-center justify-center mb-3">
                    @if($faxJob->getCurrentStep() >= 4)
                        <div class="bg-green-100 rounded-full p-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    @else
                        <div class="bg-gray-100 rounded-full p-3">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                </div>
                <h4 class="font-semibold {{ $faxJob->getCurrentStep() >= 4 ? 'text-gray-800' : 'text-gray-400' }}">
                    Email Confirmation
                </h4>
                <p class="text-sm {{ $faxJob->getCurrentStep() >= 4 ? 'text-gray-600' : 'text-gray-400' }}">
                    Receipt sent to you
                </p>
                @if($faxJob->email_sent_at)
                    <p class="text-xs text-green-600 mt-1">
                        {{ $faxJob->email_sent_at->format('g:i A') }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Current Status Message -->
    <div class="mb-8 text-center">
        @if($faxJob->getCurrentStep() == 1)
            <div class="bg-blue-50 rounded-lg p-4">
                <h3 class="font-semibold text-blue-800 mb-2">🔄 Preparing Your Fax</h3>
                <p class="text-blue-700">Your fax is in our queue and will be processed shortly.</p>
            </div>
        @elseif($faxJob->getCurrentStep() == 2)
            <div class="bg-yellow-50 rounded-lg p-4">
                <h3 class="font-semibold text-yellow-800 mb-2">📤 Sending Your Fax</h3>
                <p class="text-yellow-700">Your fax is being transmitted to {{ $faxJob->recipient_number }}.</p>
                @if($faxJob->telnyx_fax_id)
                    <p class="text-xs text-yellow-600 mt-2">Telnyx ID: {{ $faxJob->telnyx_fax_id }}</p>
                @endif
            </div>
        @elseif($faxJob->getCurrentStep() == 3)
            <div class="bg-green-50 rounded-lg p-4">
                <h3 class="font-semibold text-green-800 mb-2">✅ Fax Delivered Successfully!</h3>
                <p class="text-green-700">Your fax has been successfully delivered to {{ $faxJob->recipient_number }}.</p>
                @if($faxJob->telnyx_status)
                    <p class="text-xs text-green-600 mt-2">Status: {{ ucwords(str_replace('_', ' ', $faxJob->telnyx_status)) }}</p>
                @endif
            </div>
        @elseif($faxJob->getCurrentStep() == 4)
            <div class="bg-green-50 rounded-lg p-4">
                <h3 class="font-semibold text-green-800 mb-2">🎉 Complete!</h3>
                <p class="text-green-700">Your fax has been delivered and confirmation email sent to {{ $faxJob->sender_email }}.</p>
            </div>
        @endif
    </div>

    <!-- Fax Details -->
    <div class="bg-gray-50 rounded-lg p-6 mb-8">
        <h3 class="font-semibold text-gray-800 mb-4">Fax Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">To:</span>
                    <span class="font-medium">{{ $faxJob->recipient_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Document:</span>
                    <span class="font-medium">{{ $faxJob->file_original_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">From:</span>
                    <span class="font-medium">{{ $faxJob->sender_name }}</span>
                </div>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Email:</span>
                    <span class="font-medium">{{ $faxJob->sender_email }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Amount Paid:</span>
                    <span class="font-medium text-green-600">${{ number_format($faxJob->amount, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Started:</span>
                    <span class="font-medium">{{ $faxJob->created_at->format('M j, Y g:i A') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="text-center space-y-4">
        @if($faxJob->getCurrentStep() < 4)
            <div class="bg-blue-50 rounded-lg p-4">
                <p class="text-sm text-blue-700 mb-3">This page will automatically refresh to show updates.</p>
                <button onclick="window.location.reload()" 
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    Refresh Status
                </button>
            </div>
        @endif
        
        <div class="pt-4">
            <a href="{{ route('fax.step1') }}" 
               class="inline-block bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white py-3 px-8 rounded-md font-semibold transition-all duration-200">
                Send Another Fax
            </a>
        </div>
    </div>
</div>

@if($faxJob->getCurrentStep() < 4)
<script>
    // Auto-refresh every 5 seconds if not complete
    setTimeout(function() {
        window.location.reload();
    }, 5000);
</script>
@endif
@endsection 