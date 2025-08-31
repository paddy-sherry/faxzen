@extends('layouts.app')

@section('title', 'Fax Status - Tracking Your Delivery | FaxZen')

@section('content')
<style>
    @keyframes pulse-progress {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.8; }
    }
    
    @keyframes shimmer {
        0% { background-position: -200px 0; }
        100% { background-position: calc(200px + 100%) 0; }
    }
    
    @keyframes breathe {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.02); }
    }
    
    @keyframes spin-slow {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    @keyframes bounce-gentle {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-3px); }
    }
    
    .progress-bar-animated {
        animation: pulse-progress 2s ease-in-out infinite;
    }
    
    .shimmer-effect {
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        background-size: 200px 100%;
        animation: shimmer 1.5s infinite;
    }
    
    .breathe-animation {
        animation: breathe 3s ease-in-out infinite;
    }
    
    .spin-slow {
        animation: spin-slow 3s linear infinite;
    }
    
    .bounce-gentle {
        animation: bounce-gentle 2s ease-in-out infinite;
    }
    
    .status-card {
        transition: all 0.3s ease;
    }
    
    .status-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
</style>

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
        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden relative">
            @if($faxJob->getCurrentStep() < 4)
                <div class="absolute inset-0 shimmer-effect"></div>
            @endif
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-3 rounded-full transition-all duration-500 {{ $faxJob->getCurrentStep() < 4 ? 'progress-bar-animated' : '' }}" 
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
                        <div class="bg-green-100 rounded-full p-3 bounce-gentle">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    @elseif($faxJob->getCurrentStep() == 1)
                        <div class="bg-blue-100 rounded-full p-3 animate-pulse">
                            <svg class="w-6 h-6 text-blue-600 spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
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
                        <div class="bg-green-100 rounded-full p-3 bounce-gentle">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    @elseif($faxJob->getCurrentStep() == 2)
                        <div class="bg-blue-100 rounded-full p-3 animate-pulse">
                            <svg class="w-6 h-6 text-blue-600 bounce-gentle" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        <div class="bg-green-100 rounded-full p-3 bounce-gentle">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    @elseif($faxJob->getCurrentStep() == 3)
                        <div class="bg-green-100 rounded-full p-3 bounce-gentle">
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
                        <div class="bg-green-100 rounded-full p-3 bounce-gentle">
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
        @if($faxJob->isPendingScheduled())
            <div class="bg-purple-50 rounded-lg p-4 status-card breathe-animation">
                <h3 class="font-semibold text-purple-800 mb-2">🕒 Fax Scheduled</h3>
                <p class="text-purple-700">Your fax is scheduled to be sent on:</p>
                <p class="text-lg font-semibold text-purple-900 mt-2" id="scheduled-time-display">{{ $faxJob->getScheduledTimeFormatted() }}</p>
                <div class="mt-3">
                    <div class="text-sm text-purple-600" id="countdown-timer"></div>
                </div>
            </div>
        @elseif($faxJob->getCurrentStep() == 1)
            <div class="bg-blue-50 rounded-lg p-4 status-card breathe-animation">
                <h3 class="font-semibold text-blue-800 mb-2">🔄 Preparing Your Fax</h3>
                @if($faxJob->isScheduled())
                    <p class="text-blue-700">Your fax will be sent on {{ $faxJob->getScheduledTimeFormatted() }}</p>
                @else
                    <p class="text-blue-700">Your fax is in our queue and will be processed shortly.</p>
                @endif
                <div class="mt-3 flex justify-center">
                    <div class="flex space-x-1">
                        <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                        <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse" style="animation-delay: 0.2s"></div>
                        <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse" style="animation-delay: 0.4s"></div>
                    </div>
                </div>
            </div>
        @elseif($faxJob->getCurrentStep() == 2)
            <div class="bg-yellow-50 rounded-lg p-4 status-card breathe-animation">
                <h3 class="font-semibold text-yellow-800 mb-2">📤 Sending Your Fax</h3>
                <p class="text-yellow-700">Your fax is being sent to {{ $faxJob->recipient_number }}. Please allow up to 10 minutes. We keep trying until it's done!</p>
                
                @php
                    $retryStageMessage = $faxJob->getRetryStageMessage();
                @endphp
                
                @if($retryStageMessage)
                    <div class="mt-4 p-4 bg-{{ $retryStageMessage['color'] }}-50 rounded-md border-l-4 border-{{ $retryStageMessage['color'] }}-400">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-{{ $retryStageMessage['color'] }}-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($retryStageMessage['color'] === 'blue')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                @elseif($retryStageMessage['color'] === 'yellow')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                @endif
                            </svg>
                            <div class="flex-1">
                                <h4 class="text-{{ $retryStageMessage['color'] }}-900 font-semibold text-sm">{{ $retryStageMessage['title'] }}</h4>
                                <p class="text-{{ $retryStageMessage['color'] }}-800 text-sm mt-1">{{ $retryStageMessage['message'] }}</p>
                                
                                @if(isset($retryStageMessage['next_attempt']))
                                    <div class="mt-3 p-2 bg-{{ $retryStageMessage['color'] }}-100 rounded border">
                                        <div class="text-{{ $retryStageMessage['color'] }}-800 text-xs space-y-1">
                                            <div><strong>Recipient timezone:</strong> {{ $retryStageMessage['timezone_info']['timezone'] ?? 'Unknown' }}</div>
                                            <div><strong>Current time there:</strong> {{ \Carbon\Carbon::parse($retryStageMessage['timezone_info']['current_time'] ?? '')->format('g:i A T') }}</div>
                                            <div><strong>Next retry:</strong> {{ $retryStageMessage['next_attempt'] }}</div>
                                            @if($retryStageMessage['timezone_info']['is_weekend'] ?? false)
                                                <div class="text-{{ $retryStageMessage['color'] }}-700 font-medium">🏖️ Weekend - waiting for Monday</div>
                                            @else
                                                <div class="text-{{ $retryStageMessage['color'] }}-700 font-medium">🌙 After hours - waiting for business hours</div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                
                                @if($faxJob->retry_attempts > 0)
                                    <div class="mt-2 text-{{ $retryStageMessage['color'] }}-700 text-xs">
                                        Attempt {{ $faxJob->retry_attempts }} of {{ $faxJob->canRetry() ? '20' : $faxJob->retry_attempts }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
                
                <div class="mt-4 flex justify-center">
                    <div class="flex space-x-1">
                        <div class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></div>
                        <div class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse" style="animation-delay: 0.2s"></div>
                        <div class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse" style="animation-delay: 0.4s"></div>
                    </div>
                </div>
            </div>
        @elseif($faxJob->getCurrentStep() == 3)
            <div class="bg-green-50 rounded-lg p-4 status-card">
                <h3 class="font-semibold text-green-800 mb-2">✅ Fax Delivered Successfully!</h3>
                <p class="text-green-700">Your fax has been successfully delivered to {{ $faxJob->recipient_number }}.</p>
                @if($faxJob->telnyx_status)
                    <p class="text-xs text-green-600 mt-2">Status: {{ ucwords(str_replace('_', ' ', $faxJob->telnyx_status)) }}</p>
                @endif
            </div>
        @elseif($faxJob->getCurrentStep() == 4)
            <div class="bg-green-50 rounded-lg p-4 status-card">
                <h3 class="font-semibold text-green-800 mb-2">🎉 Complete!</h3>
                <p class="text-green-700">Your fax has been delivered and confirmation email sent to {{ $faxJob->sender_email }}.</p>
            </div>
        @endif
    </div>

    <!-- Fax Details -->
    <div class="bg-gray-50 rounded-lg p-6 mb-8 status-card">
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
                @if($faxJob->scheduled_time)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Scheduled For:</span>
                        <span class="font-medium text-purple-600">{{ $faxJob->getScheduledTimeFormatted() }}</span>
                    </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-600">{{ $faxJob->scheduled_time ? 'Created' : 'Started' }}:</span>
                    <span class="font-medium">{{ $faxJob->created_at->format('M j, Y g:i A') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="text-center space-y-4">
        @if($faxJob->getCurrentStep() < 4)
            <div class="bg-blue-50 rounded-lg p-4 status-card">
                <p class="text-sm text-blue-700 mb-3">This page will automatically refresh to show updates.</p>
                <button onclick="window.location.reload()" 
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors transform hover:scale-105">
                    🔄 Refresh Status
                </button>
            </div>
        @endif
        
   
    </div>
</div>

<!-- Google Ads Conversion Tracking -->
@if(config('services.google.ads_conversion_id') && !session('conversion_tracked_' . $faxJob->id))
<script>
    // Track Google Ads conversion for successful payment
    gtag('event', 'conversion', {
        'send_to': '{{ config('services.google.ads_conversion_id') }}/{{ config('services.google.ads_conversion_label') }}',
        'value': {{ $faxJob->amount }},
        'currency': 'USD',
        'transaction_id': '{{ $faxJob->hash }}',
        'custom_parameters': {
            'payment_type': '{{ $faxJob->amount == 20 ? "bulk_package" : "single_fax" }}',
            'recipient_country': '{{ substr($faxJob->recipient_number, 0, 2) == "+1" ? "US" : "International" }}'
        }
    });
    
    // Enhanced conversion data (hashed customer information for better attribution)
    @if($faxJob->sender_email)
    gtag('config', '{{ config('services.google.ads_id') }}', {
        'customer_lifetime_value': {{ $faxJob->amount }},
        'user_data': {
            'email_address': '{{ hash('sha256', strtolower(trim($faxJob->sender_email))) }}',
            'phone_number': '{{ hash('sha256', preg_replace("/[^0-9]/", "", $faxJob->recipient_number)) }}'
        }
    });
    @endif
    
    console.log('Google Ads conversion tracked:', {
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

<!-- Countdown Timer for Scheduled Faxes -->
@if($faxJob->isPendingScheduled())
<script>
// Convert scheduled time to user's local timezone for display
document.addEventListener('DOMContentLoaded', function() {
    const scheduledTimeUTC = '{{ $faxJob->scheduled_time->toISOString() }}';
    const userTimezone = moment.tz.guess();
    
    console.log('=== STATUS PAGE TIMEZONE DEBUG ===');
    console.log('UTC Time from server:', scheduledTimeUTC);
    console.log('User Timezone:', userTimezone);
    
    // Convert UTC time to user's local timezone for display
    if (typeof moment !== 'undefined') {
        console.log('Raw server time string:', scheduledTimeUTC);
        
        // Try different parsing methods to handle the timezone issue
        const method1 = moment.utc(scheduledTimeUTC);
        const method2 = moment(scheduledTimeUTC).utc();
        const method3 = moment.parseZone(scheduledTimeUTC).utc();
        
        console.log('Method 1 (moment.utc):', method1.format('YYYY-MM-DD HH:mm:ss [UTC]'));
        console.log('Method 2 (moment().utc):', method2.format('YYYY-MM-DD HH:mm:ss [UTC]'));
        console.log('Method 3 (parseZone):', method3.format('YYYY-MM-DD HH:mm:ss [UTC]'));
        
        // Use the most reliable method - direct parsing of ISO string
        const serverTime = moment(scheduledTimeUTC);
        console.log('Server time (direct parse):', serverTime.format('YYYY-MM-DD HH:mm:ss z'));
        
        // WORKAROUND: If the database is storing local time instead of UTC,
        // we need to treat it as such
        const now = moment().tz(userTimezone);
        console.log('Current time in user timezone:', now.format('YYYY-MM-DD HH:mm:ss z'));
        
        // Parse the server time as if it's already in the user's timezone
        // This handles the case where Step 2 didn't convert properly
        const scheduledTimeInUserTz = moment.tz(scheduledTimeUTC.slice(0, -1), userTimezone);
        console.log('Treating server time as local time:', scheduledTimeInUserTz.format('YYYY-MM-DD HH:mm:ss z'));
        
        const timeDiffFromLocal = scheduledTimeInUserTz.diff(now, 'minutes');
        const timeDiffFromUTC = serverTime.tz(userTimezone).diff(now, 'minutes');
        
        console.log('Time diff if treated as local (minutes):', timeDiffFromLocal);
        console.log('Time diff if treated as UTC (minutes):', timeDiffFromUTC);
        
        // Choose the interpretation that makes more sense (closer to a reasonable scheduling time)
        let displayTime;
        if (Math.abs(timeDiffFromLocal) < Math.abs(timeDiffFromUTC) && timeDiffFromLocal > -30) {
            // Use local time interpretation if it's more reasonable and not in the past
            displayTime = scheduledTimeInUserTz;
            console.log('Using local time interpretation (database has local time)');
        } else {
            // Use UTC interpretation
            displayTime = serverTime.tz(userTimezone);
            console.log('Using UTC interpretation (database has UTC time)');
        }
        
        const displayElement = document.getElementById('scheduled-time-display');
        if (displayElement) {
            displayElement.innerHTML = displayTime.format('MMM D, YYYY [at] h:mm A z');
        }
        
        console.log('Final display format:', displayTime.format('MMM D, YYYY [at] h:mm A z'));
        console.log('Final display time chosen:', displayTime.format('YYYY-MM-DD HH:mm:ss z'));
    } else {
        console.log('Moment.js not available, showing UTC time');
    }
});

function updateCountdown() {
    const scheduledTime = new Date('{{ $faxJob->scheduled_time->toISOString() }}');
    const now = new Date();
    const timeDiff = scheduledTime - now;
    
    if (timeDiff <= 0) {
        document.getElementById('countdown-timer').innerHTML = 'Sending now...';
        // Refresh page to show updated status
        setTimeout(function() {
            window.location.reload();
        }, 2000);
        return;
    }
    
    const days = Math.floor(timeDiff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((timeDiff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((timeDiff % (1000 * 60)) / 1000);
    
    let countdownText = 'Sending in: ';
    if (days > 0) {
        countdownText += `${days}d ${hours}h ${minutes}m`;
    } else if (hours > 0) {
        countdownText += `${hours}h ${minutes}m ${seconds}s`;
    } else if (minutes > 0) {
        countdownText += `${minutes}m ${seconds}s`;
    } else {
        countdownText += `${seconds}s`;
    }
    
    document.getElementById('countdown-timer').innerHTML = countdownText;
}

// Update countdown immediately and then every second
updateCountdown();
setInterval(updateCountdown, 1000);
</script>
@endif

@if($faxJob->getCurrentStep() < 4)
<script>
    // Auto-refresh every 5 seconds if not complete (but not for scheduled faxes that haven't started yet)
    @if(!$faxJob->isPendingScheduled())
    setTimeout(function() {
        window.location.reload();
    }, 10000);
    
    // Add a subtle countdown indicator
    let countdown = 10;
    const countdownElement = document.createElement('div');
    countdownElement.className = 'fixed bottom-4 right-4 bg-blue-600 text-white px-3 py-2 rounded-lg text-sm shadow-lg';
    countdownElement.innerHTML = `Auto-refresh in ${countdown}s`;
    document.body.appendChild(countdownElement);
    
    const countdownInterval = setInterval(() => {
        countdown--;
        countdownElement.innerHTML = `Auto-refresh in ${countdown}s`;
        if (countdown <= 0) {
            clearInterval(countdownInterval);
            countdownElement.innerHTML = 'Refreshing...';
        }
    }, 1000);
    @endif
</script>
@endif
@endsection 