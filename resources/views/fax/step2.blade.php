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
                
                <!-- Pricing Options -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-4">
                        Choose Your Option <span class="text-red-500">*</span>
                    </label>
            @endif
        @else
            <!-- User not logged in - show payment options -->
            <!-- Pricing Options -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-4">
                    Choose Your Option <span class="text-red-500">*</span>
                </label>
        @else
            <!-- User not logged in - show payment options -->
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
                                        <div class="text-2xl font-bold text-faxzen-blue">$3.00</div>
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
        @endauth
        
        @auth
            @if(!Auth::user()->hasCredits())
                <!-- User logged in but no credits - show payment options -->
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
                                            <div class="text-2xl font-bold text-faxzen-blue">$3.00</div>
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
        @endauth

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
                if (onetimeRadio.checked) {
                    onetimeFlow.classList.remove('hidden');
                    creditsFlow.classList.add('hidden');
                } else {
                    onetimeFlow.classList.add('hidden');
                    creditsFlow.classList.remove('hidden');
                }
            }

            onetimeRadio.addEventListener('change', updateFlow);
            creditsRadio.addEventListener('change', updateFlow);
        });
        </script>
    @endif
@else
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const onetimeRadio = document.getElementById('payment_type_onetime');
        const creditsRadio = document.getElementById('payment_type_credits');
        const onetimeFlow = document.getElementById('onetime-flow');
        const creditsFlow = document.getElementById('credits-flow');

        function updateFlow() {
            if (onetimeRadio.checked) {
                onetimeFlow.classList.remove('hidden');
                creditsFlow.classList.add('hidden');
            } else {
                onetimeFlow.classList.add('hidden');
                creditsFlow.classList.remove('hidden');
            }
        }

        onetimeRadio.addEventListener('change', updateFlow);
        creditsRadio.addEventListener('change', updateFlow);
    });
    </script>
@endauth
@endsection 