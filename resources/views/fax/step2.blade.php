@extends('layouts.app')

@section('title', 'Your Details - Step 2 of 3')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Progress Bar -->
    <div class="flex justify-center mb-8">
        <div class="flex items-center">
            <div class="flex items-center justify-center w-8 h-8 bg-green-500 text-white rounded-full">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="w-16 h-1 bg-green-500 mx-2"></div>
            <div class="flex items-center justify-center w-8 h-8 bg-blue-500 text-white rounded-full font-semibold">
                2
            </div>
            <div class="w-16 h-1 bg-gray-300 mx-2"></div>
            <div class="flex items-center justify-center w-8 h-8 bg-gray-300 text-gray-600 rounded-full font-semibold">
                3
            </div>
        </div>
    </div>

    <h1 class="text-3xl font-bold text-center mb-2">Your Details</h1>
    <p class="text-gray-600 text-center mb-8">Enter your contact information</p>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L10 11.414l1.293-1.293a1 1 0 001.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
                    <ul class="mt-1 text-sm text-red-700 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    @if ($faxJob->status === 'payment_pending')
        <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-600">
                        <strong>Welcome back!</strong> You can modify your email address if needed, then continue to complete your payment.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('fax.step2', $faxJob->hash) }}" method="POST" class="space-y-6">
        @csrf
        
        @auth
            @if(Auth::user()->hasCredits())
                <!-- User has credits - show credit usage interface -->
                <div class="bg-green-50 border border-green-200 rounded-md p-6 mb-6">
                    <div class="flex items-center mb-4">
                        <svg class="h-6 w-6 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-green-800">Using Your Fax Credits</h3>
                    </div>
                    <div class="bg-white rounded-lg p-4 border border-green-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-600">You have</p>
                                <p class="text-2xl font-bold text-green-600">{{ Auth::user()->fax_credits }} credits</p>
                                <p class="text-sm text-gray-500">available</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">This fax will use</p>
                                <p class="text-xl font-semibold text-gray-900">1 credit</p>
                                <p class="text-sm text-gray-500">{{ Auth::user()->fax_credits - 1 }} remaining after</p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- User logged in but no credits - show payment options -->
                <div class="bg-orange-50 border border-orange-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-orange-600">
                                <strong>No Credits Available</strong> You need to purchase credits or pay per fax to continue.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Pricing Options for authenticated users without credits -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-4">
                        Choose Your Option <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-4">
                        <!-- One-time Payment Option -->
                        <div class="flex items-start">
                            <input type="radio" 
                                   id="payment_type_onetime" 
                                   name="payment_type" 
                                   value="onetime"
                                   checked
                                   class="mt-1 h-4 w-4 text-faxzen-blue focus:ring-faxzen-blue border-gray-300">
                            <div class="ml-3 flex-1">
                                <label for="payment_type_onetime" class="cursor-pointer">
                                    <div class="border border-gray-300 rounded-lg p-4 hover:border-faxzen-blue hover:bg-blue-50 transition-colors">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-900">One-time Payment</h3>
                                                <p class="text-sm text-gray-600">Perfect for occasional fax sending</p>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-2xl font-bold text-faxzen-blue">$5.00</div>
                                                <div class="text-sm text-gray-500">per fax</div>
                                            </div>
                                        </div>
                                        <ul class="mt-3 text-sm text-gray-600 space-y-1">
                                            <li>• Send one fax immediately</li>
                                            <li>• No account required</li>
                                            <li>• Email confirmation included</li>
                                        </ul>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Account with Credits Option -->
                        <div class="flex items-start">
                            <input type="radio" 
                                   id="payment_type_credits" 
                                   name="payment_type" 
                                   value="credits"
                                   class="mt-1 h-4 w-4 text-faxzen-blue focus:ring-faxzen-blue border-gray-300">
                            <div class="ml-3 flex-1">
                                <label for="payment_type_credits" class="cursor-pointer">
                                    <div class="border border-gray-300 rounded-lg p-4 hover:border-faxzen-blue hover:bg-blue-50 transition-colors">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-900">20-Fax Package</h3>
                                                <p class="text-sm text-gray-600">Best value for regular users</p>
                                                <span class="inline-block mt-1 px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                                    SAVE 67%
                                                </span>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-2xl font-bold text-faxzen-blue">$20.00</div>
                                                <div class="text-sm text-gray-500">20 faxes ($1.00 each)</div>
                                            </div>
                                        </div>
                                        <ul class="mt-3 text-sm text-gray-600 space-y-1">
                                            <li>• Send 20 faxes anytime</li>
                                            <li>• Account dashboard to track usage</li>
                                            <li>• Fax history and confirmations</li>
                                            <li>• Credits never expire</li>
                                        </ul>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @else
            <!-- User not logged in - show payment options -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-4">
                    Choose Your Option <span class="text-red-500">*</span>
                </label>
                <div class="space-y-4">
                    <!-- One-time Payment Option -->
                    <div class="flex items-start">
                        <input type="radio" 
                               id="payment_type_onetime_guest" 
                               name="payment_type" 
                               value="onetime"
                               checked
                               class="mt-1 h-4 w-4 text-faxzen-blue focus:ring-faxzen-blue border-gray-300">
                        <div class="ml-3 flex-1">
                            <label for="payment_type_onetime_guest" class="cursor-pointer">
                                <div class="border border-gray-300 rounded-lg p-4 hover:border-faxzen-blue hover:bg-blue-50 transition-colors">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">One-time Payment</h3>
                                            <p class="text-sm text-gray-600">Perfect for occasional fax sending</p>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-2xl font-bold text-faxzen-blue">$5.00</div>
                                            <div class="text-sm text-gray-500">per fax</div>
                                        </div>
                                    </div>
                                    <ul class="mt-3 text-sm text-gray-600 space-y-1">
                                        <li>• Send one fax immediately</li>
                                        <li>• No account required</li>
                                        <li>• Email confirmation included</li>
                                    </ul>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Account with Credits Option -->
                    <div class="flex items-start">
                        <input type="radio" 
                               id="payment_type_credits_guest" 
                               name="payment_type" 
                               value="credits"
                               class="mt-1 h-4 w-4 text-faxzen-blue focus:ring-faxzen-blue border-gray-300">
                        <div class="ml-3 flex-1">
                            <label for="payment_type_credits_guest" class="cursor-pointer">
                                <div class="border border-gray-300 rounded-lg p-4 hover:border-faxzen-blue hover:bg-blue-50 transition-colors">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">20-Fax Package</h3>
                                            <p class="text-sm text-gray-600">Best value for regular users</p>
                                            <span class="inline-block mt-1 px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                                SAVE 67%
                                            </span>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-2xl font-bold text-faxzen-blue">$20.00</div>
                                            <div class="text-sm text-gray-500">20 faxes ($1.00 each)</div>
                                        </div>
                                    </div>
                                    <ul class="mt-3 text-sm text-gray-600 space-y-1">
                                        <li>• Send 20 faxes anytime</li>
                                        <li>• Account dashboard to track usage</li>
                                        <li>• Fax history and confirmations</li>
                                        <li>• Credits never expire</li>
                                    </ul>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        @endauth

        <!-- Fax Scheduling Section -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-4">
                When should we send your fax? <span class="text-red-500">*</span>
            </label>
            <div class="space-y-4">
                <!-- Send Now Option -->
                <div class="flex items-start">
                    <input type="radio" 
                           id="schedule_type_now" 
                           name="schedule_type" 
                           value="now"
                           checked
                           class="mt-1 h-4 w-4 text-faxzen-blue focus:ring-faxzen-blue border-gray-300">
                    <div class="ml-3 flex-1">
                        <label for="schedule_type_now" class="cursor-pointer">
                            <div class="border border-gray-300 rounded-lg p-4 hover:border-faxzen-blue hover:bg-blue-50 transition-colors">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">Send Immediately</h3>
                                        <p class="text-sm text-gray-600">Your fax will be sent right after payment</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Schedule for Later Option -->
                <div class="flex items-start">
                    <input type="radio" 
                           id="schedule_type_later" 
                           name="schedule_type" 
                           value="later"
                           class="mt-1 h-4 w-4 text-faxzen-blue focus:ring-faxzen-blue border-gray-300">
                    <div class="ml-3 flex-1">
                        <label for="schedule_type_later" class="cursor-pointer">
                            <div class="border border-gray-300 rounded-lg p-4 hover:border-faxzen-blue hover:bg-blue-50 transition-colors">
                                <div class="flex items-center mb-3">
                                    <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">Schedule for Later</h3>
                                        <p class="text-sm text-gray-600">Choose a specific date and time</p>
                                    </div>
                                </div>
                                
                                <!-- Scheduling Controls (hidden by default) -->
                                <div id="schedule-controls" class="hidden mt-4 pt-4 border-t border-gray-200">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="schedule_date" class="block text-sm font-medium text-gray-700 mb-2">
                                                Date
                                            </label>
                                            <input type="date" 
                                                   id="schedule_date" 
                                                   name="schedule_date" 
                                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-faxzen-blue focus:border-faxzen-blue">
                                        </div>
                                        <div>
                                            <label for="schedule_time" class="block text-sm font-medium text-gray-700 mb-2">
                                                Time
                                            </label>
                                            <select id="schedule_time" 
                                                    name="schedule_time" 
                                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-faxzen-blue focus:border-faxzen-blue">
                                                <!-- Time options will be populated by JavaScript -->
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <p class="text-sm text-blue-600">
                                            <span class="font-medium">Your timezone:</span> <span id="user-timezone">Loading...</span>
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Times are shown in your local timezone. Fax will be sent at the exact time you select.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        @guest
            <!-- Email field for non-authenticated users -->
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
                <p class="mt-1 text-sm text-gray-500">We'll send you a confirmation when your fax is delivered</p>
            </div>
        @else
            <!-- Hidden input for authenticated users - use their account email -->
            <input type="hidden" name="sender_email" value="{{ Auth::user()->email }}">
            
            <!-- Show confirmation email info -->
            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <div>
                        <p class="text-sm text-blue-700">
                            <strong>Confirmation will be sent to:</strong> {{ Auth::user()->email }}
                        </p>
                    </div>
                </div>
            </div>
        @endguest

        <div class="flex space-x-4 pt-4">
            <a href="{{ route('fax.step1') }}" 
               class="flex-1 bg-gray-300 text-gray-700 py-3 px-4 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors font-semibold text-center">
                ← Back
            </a>
            <button type="submit" 
                    class="flex-1 bg-faxzen-blue text-white py-3 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-faxzen-blue transition-colors font-semibold">
                @auth
                    @if(Auth::user()->hasCredits())
                        Send Fax (Use 1 Credit) →
                    @else
                        Continue to Payment →
                    @endif
                @else
                    Continue to Payment →
                @endauth
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
    
    @auth
        @if(Auth::user()->hasCredits())
            <!-- User has credits flow -->
            <ul class="text-sm text-green-700 space-y-2">
                <li class="flex items-start">
                    <span class="mr-2">•</span>
                    <span>1 credit will be deducted from your account</span>
                </li>
                <li class="flex items-start">
                    <span class="mr-2">•</span>
                    <span>Your fax will be sent immediately</span>
                </li>
                <li class="flex items-start">
                    <span class="mr-2">•</span>
                    <span>You'll receive an email confirmation when delivered</span>
                </li>
                <li class="flex items-start">
                    <span class="mr-2">•</span>
                    <span>View your fax history in your dashboard</span>
                </li>
            </ul>
        @else
            <!-- User logged in but no credits - show payment flows -->
            <!-- One-time payment flow -->
            <div id="onetime-flow" class="payment-flow">
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
            
            <!-- Credits flow -->
            <div id="credits-flow" class="payment-flow hidden">
                <ul class="text-sm text-green-700 space-y-2">
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>Complete payment securely with Stripe</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>20 more credits will be added to your account</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>Your fax will be sent immediately</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>View your updated credit balance in your dashboard</span>
                    </li>
                </ul>
            </div>
        @endif
    @else
        <!-- User not logged in - show payment flows -->
        <!-- One-time payment flow -->
        <div id="onetime-flow" class="payment-flow">
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
        
        <!-- Credits flow -->
        <div id="credits-flow" class="payment-flow hidden">
            <ul class="text-sm text-green-700 space-y-2">
                <li class="flex items-start">
                    <span class="mr-2">•</span>
                    <span>Complete payment securely with Stripe</span>
                </li>
                <li class="flex items-start">
                    <span class="mr-2">•</span>
                    <span>Your account will be created with 20 fax credits</span>
                </li>
                <li class="flex items-start">
                    <span class="mr-2">•</span>
                    <span>Your first fax will be sent immediately</span>
                </li>
                <li class="flex items-start">
                    <span class="mr-2">•</span>
                    <span>Access your dashboard via magic link authentication</span>
                </li>
            </ul>
        </div>
    @endauth
</div>

@auth
    @if(!Auth::user()->hasCredits())
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const onetimeRadio = document.getElementById('payment_type_onetime');
            const creditsRadio = document.getElementById('payment_type_credits');
            const onetimeFlow = document.getElementById('onetime-flow');
            const creditsFlow = document.getElementById('credits-flow');

            function updateFlow() {
                if (onetimeRadio && onetimeRadio.checked) {
                    onetimeFlow.classList.remove('hidden');
                    creditsFlow.classList.add('hidden');
                } else if (creditsRadio && creditsRadio.checked) {
                    onetimeFlow.classList.add('hidden');
                    creditsFlow.classList.remove('hidden');
                }
            }

            if (onetimeRadio) onetimeRadio.addEventListener('change', updateFlow);
            if (creditsRadio) creditsRadio.addEventListener('change', updateFlow);
        });
        </script>
    @endif
@else
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const onetimeRadio = document.getElementById('payment_type_onetime_guest');
        const creditsRadio = document.getElementById('payment_type_credits_guest');
        const onetimeFlow = document.getElementById('onetime-flow');
        const creditsFlow = document.getElementById('credits-flow');

        function updateFlow() {
            if (onetimeRadio && onetimeRadio.checked) {
                onetimeFlow.classList.remove('hidden');
                creditsFlow.classList.add('hidden');
            } else if (creditsRadio && creditsRadio.checked) {
                onetimeFlow.classList.add('hidden');
                creditsFlow.classList.remove('hidden');
            }
        }

        if (onetimeRadio) onetimeRadio.addEventListener('change', updateFlow);
        if (creditsRadio) creditsRadio.addEventListener('change', updateFlow);
    });
    </script>
@endauth

<!-- Fax Scheduling JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== PAGE LOAD DEBUG ===');
    console.log('DOM Content Loaded - JavaScript is running');
    console.log('Current URL:', window.location.href);
    console.log('Moment.js available:', typeof moment);
    
    // Initialize scheduling functionality
    initializeScheduling();
    
    function initializeScheduling() {
        console.log('=== INITIALIZE SCHEDULING DEBUG ===');
        const nowRadio = document.getElementById('schedule_type_now');
        const laterRadio = document.getElementById('schedule_type_later');
        const scheduleControls = document.getElementById('schedule-controls');
        
        console.log('Elements found:');
        console.log('- nowRadio:', nowRadio);
        console.log('- laterRadio:', laterRadio);
        console.log('- scheduleControls:', scheduleControls);
        const dateInput = document.getElementById('schedule_date');
        const timeSelect = document.getElementById('schedule_time');
        const userTimezoneSpan = document.getElementById('user-timezone');
        
        // Get user's timezone
        const userTimezone = moment.tz.guess();
        userTimezoneSpan.textContent = userTimezone;
        
        // Set minimum date to today
        const today = moment().format('YYYY-MM-DD');
        dateInput.min = today;
        dateInput.value = today;
        
        // Populate time options
        populateTimeOptions();
        
        // Handle radio button changes
        nowRadio.addEventListener('change', function() {
            if (this.checked) {
                scheduleControls.classList.add('hidden');
                clearScheduleValidation();
            }
        });
        
        laterRadio.addEventListener('change', function() {
            if (this.checked) {
                scheduleControls.classList.remove('hidden');
                validateScheduleTime();
            }
        });
        
        // Handle date change
        dateInput.addEventListener('change', function() {
            populateTimeOptions();
            validateScheduleTime();
        });
        
        // Handle time change
        timeSelect.addEventListener('change', validateScheduleTime);
        
        function populateTimeOptions() {
            const selectedDate = dateInput.value;
            const isToday = moment(selectedDate).isSame(moment(), 'day');
            const now = moment();
            
            timeSelect.innerHTML = '';
            
            // Generate time slots every 15 minutes
            const startHour = isToday ? Math.max(now.hour(), 6) : 6; // Start from 6 AM or current hour
            const startMinute = isToday ? (now.minute() > 45 ? 0 : Math.ceil(now.minute() / 15) * 15) : 0;
            
            // If it's today and we're past the start time, start from next valid slot
            let startTime = moment().hour(startHour).minute(startMinute);
            if (isToday && startTime.isBefore(now)) {
                startTime = moment(now).add(15 - (now.minute() % 15), 'minutes');
            }
            
            const endTime = moment().hour(22).minute(0); // End at 10 PM
            
            const currentTime = startTime.clone();
            
            while (currentTime.isSameOrBefore(endTime)) {
                const timeValue = currentTime.format('HH:mm');
                const timeDisplay = currentTime.format('h:mm A');
                
                const option = document.createElement('option');
                option.value = timeValue;
                option.textContent = timeDisplay;
                
                timeSelect.appendChild(option);
                currentTime.add(15, 'minutes');
            }
            
            // If no options available (e.g., it's too late today), show message
            if (timeSelect.options.length === 0) {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'No available times today';
                option.disabled = true;
                timeSelect.appendChild(option);
            }
        }
        
        function validateScheduleTime() {
            const selectedDate = dateInput.value;
            const selectedTime = timeSelect.value;
            
            if (!selectedDate || !selectedTime) {
                showScheduleError('Please select both date and time');
                return false;
            }
            
            // Create the scheduled datetime in user's timezone
            const scheduledDateTime = moment.tz(`${selectedDate} ${selectedTime}`, userTimezone);
            const now = moment();
            
            if (scheduledDateTime.isBefore(now)) {
                showScheduleError('Selected time must be in the future');
                return false;
            }
            
            // Check if it's too far in the future (30 days)
            const maxDate = moment().add(30, 'days');
            if (scheduledDateTime.isAfter(maxDate)) {
                showScheduleError('Cannot schedule more than 30 days in advance');
                return false;
            }
            
            clearScheduleValidation();
            return true;
        }
        
        function showScheduleError(message) {
            clearScheduleValidation();
            const errorDiv = document.createElement('div');
            errorDiv.id = 'schedule-error';
            errorDiv.className = 'mt-2 text-sm text-red-600';
            errorDiv.textContent = message;
            scheduleControls.appendChild(errorDiv);
        }
        
        function clearScheduleValidation() {
            const existingError = document.getElementById('schedule-error');
            if (existingError) {
                existingError.remove();
            }
        }
        
        // Form validation before submit
        const form = document.querySelector('form');
        console.log('=== JAVASCRIPT DEBUG ===');
        console.log('Form element found:', form);
        console.log('Form action:', form ? form.action : 'NO FORM');
        console.log('Later radio checked:', laterRadio ? laterRadio.checked : 'NO LATER RADIO');
        
        if (!form) {
            console.error('ERROR: Form element not found! JavaScript will not work.');
            return;
        }
        
        form.addEventListener('submit', function(e) {
            console.log('=== FORM SUBMIT HANDLER TRIGGERED ===');
            console.log('Event:', e);
            console.log('Later radio checked:', laterRadio.checked);
            if (laterRadio.checked) {
                if (!validateScheduleTime()) {
                    e.preventDefault();
                    return false;
                }
                
                // Convert the selected time to UTC for server processing
                const selectedDate = dateInput.value;
                const selectedTime = timeSelect.value;
                
                // Debug logging
                console.log('=== SCHEDULING DEBUG INFO ===');
                console.log('Selected Date:', selectedDate);
                console.log('Selected Time:', selectedTime);
                console.log('User Timezone:', userTimezone);
                console.log('Moment.js available:', typeof moment);
                
                // Check if moment.tz is available
                if (typeof moment === 'undefined') {
                    alert('ERROR: Moment.js is not loaded! Scheduling will fail.');
                    e.preventDefault();
                    return false;
                }
                
                const scheduledDateTime = moment.tz(`${selectedDate} ${selectedTime}`, userTimezone);
                console.log('Scheduled DateTime (local):', scheduledDateTime.format());
                console.log('Scheduled DateTime (UTC):', scheduledDateTime.utc().format('YYYY-MM-DD HH:mm:ss'));
                
                // Add hidden input with UTC timestamp
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'scheduled_time_utc';
                hiddenInput.value = scheduledDateTime.utc().format('YYYY-MM-DD HH:mm:ss');
                form.appendChild(hiddenInput);
                
                // Add timezone for reference
                const timezoneInput = document.createElement('input');
                timezoneInput.type = 'hidden';
                timezoneInput.name = 'user_timezone';
                timezoneInput.value = userTimezone;
                form.appendChild(timezoneInput);
                
                console.log('Hidden fields added:', {
                    scheduled_time_utc: hiddenInput.value,
                    user_timezone: timezoneInput.value
                });
            }
        });
    }
});
</script>
@endsection