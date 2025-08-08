@extends('layouts.app')

@section('title', 'Fax Details - FaxZen')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Fax Details</h1>
                <p class="text-gray-600 mt-1">Job #{{ $faxJob->id }}</p>
            </div>
            <div class="text-right">
                @if($faxJob->telnyx_status === 'delivered')
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">✓ Delivered</span>
                @elseif($faxJob->telnyx_status === 'failed')
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">✗ Failed</span>
                @elseif($faxJob->telnyx_status === 'sending')
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">📤 Sending</span>
                @elseif($faxJob->telnyx_status === 'queued')
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">⏳ Queued</span>
                @elseif($faxJob->telnyx_status === 'media.processed')
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-purple-100 text-purple-800">🔄 Processing</span>
                @elseif($faxJob->telnyx_status)
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($faxJob->telnyx_status) }}</span>
                @elseif($faxJob->status === 'paid')
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">⏳ Processing</span>
                @else
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($faxJob->status) }}</span>
                @endif
            </div>
        </div>

        <!-- Fax Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Document Information</h3>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $faxJob->file_original_name }}</p>
                                @if($faxJob->original_file_size)
                                    <p class="text-xs text-gray-500">{{ number_format($faxJob->original_file_size / 1024, 1) }} KB</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recipient</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <p class="text-sm font-medium text-gray-900">{{ $faxJob->recipient_number }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Transmission Details</h3>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Started:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $faxJob->created_at->format('M j, Y g:i A') }}</span>
                        </div>
                        @if($faxJob->prepared_at)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Prepared:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $faxJob->prepared_at->format('M j, Y g:i A') }}</span>
                        </div>
                        @endif
                        @if($faxJob->sending_started_at)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Sending Started:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $faxJob->sending_started_at->format('M j, Y g:i A') }}</span>
                        </div>
                        @endif
                        @if($faxJob->delivered_at)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Delivered:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $faxJob->delivered_at->format('M j, Y g:i A') }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Cost:</span>
                            <span class="text-sm font-medium text-gray-900">
                                @if($faxJob->amount == 0)
                                    <span class="text-green-600">Credit Used</span>
                                @else
                                    ${{ number_format($faxJob->amount, 2) }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                @if($faxJob->telnyx_fax_id)
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Technical Details</h3>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Telnyx Fax ID:</span>
                            <span class="text-sm font-mono text-gray-900">{{ $faxJob->telnyx_fax_id }}</span>
                        </div>
                        @if($faxJob->telnyx_status)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Provider Status:</span>
                            <span class="text-sm font-medium text-gray-900">{{ ucfirst($faxJob->telnyx_status) }}</span>
                        </div>
                        @endif
                        @if($faxJob->retry_attempts)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Retry Attempts:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $faxJob->retry_attempts }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Status Messages -->
        @if($faxJob->telnyx_status === 'delivered')
            <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800">Fax Delivered Successfully</h3>
                        <p class="text-sm text-green-700 mt-1">Your fax has been successfully delivered to {{ $faxJob->recipient_number }}.</p>
                    </div>
                </div>
            </div>
        @elseif($faxJob->telnyx_status === 'failed')
            <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Fax Delivery Failed</h3>
                        <p class="text-sm text-red-700 mt-1">
                            Unfortunately, your fax could not be delivered.
                            @if($faxJob->error_message)
                                Error: {{ $faxJob->error_message }}
                            @endif
                        </p>
                        @if($faxJob->amount > 0)
                            <p class="text-sm text-red-700 mt-1">You have not been charged for this failed transmission.</p>
                        @endif
                    </div>
                </div>
            </div>
        @elseif($faxJob->telnyx_status === 'sending')
            <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Fax Sending</h3>
                        <p class="text-sm text-blue-700 mt-1">Your fax is currently being transmitted. We'll update the status once delivery is confirmed.</p>
                    </div>
                </div>
            </div>
        @elseif($faxJob->telnyx_status === 'queued')
            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Fax Queued</h3>
                        <p class="text-sm text-yellow-700 mt-1">Your fax is queued for transmission and will begin sending shortly.</p>
                    </div>
                </div>
            </div>
        @elseif($faxJob->status === 'paid' && !$faxJob->telnyx_status)
            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Processing Payment</h3>
                        <p class="text-sm text-yellow-700 mt-1">Your payment has been received and your fax is being prepared for transmission.</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Actions -->
        <div class="flex space-x-4 pt-6 border-t border-gray-200">
            <a href="{{ route('account.dashboard') }}" 
               class="bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors font-semibold">
                ← Back to Dashboard
            </a>
            
            @if($faxJob->telnyx_status === 'failed')
                <a href="{{ route('fax.step1') }}" 
                   class="bg-faxzen-blue text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-faxzen-blue transition-colors font-semibold">
                    Try Again
                </a>
            @endif
        </div>
    </div>
</div>
@endsection