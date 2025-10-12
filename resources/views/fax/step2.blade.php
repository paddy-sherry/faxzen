@extends('layouts.app')

@section('title', 'Complete Your Fax Details')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="relative flex items-center justify-center mb-2">
        <a href="{{ route('fax.step1') }}" 
           class="absolute left-0 inline-flex items-center text-gray-600 hover:text-gray-800 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back
        </a>
        
        <h1 class="text-3xl font-bold">Almost Ready to Send!</h1>
    </div>
    
    <div class="text-center mb-8">
        <p class="text-gray-600 text-center">Just a few details and your fax will be on its way</p>
        
    </div>

    <!-- File Preview Section -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
        <h2 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Documents to Send
        </h2>
        
        <div class="space-y-3">
            @if($faxJob->hasMultipleFiles())
                <div class="text-sm text-blue-800 mb-3">
                    <strong>{{ $faxJob->file_count }} files</strong> will be merged into a single fax:
                </div>
                @foreach($faxJob->getAllOriginalNames() as $index => $fileName)
                    <div class="flex items-center bg-white rounded-md p-3 border border-blue-100">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                <span class="text-blue-600 font-semibold text-sm">{{ $index + 1 }}</span>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">{{ $fileName }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="flex items-center justify-between bg-white rounded-md p-3 border border-blue-100">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">{{ $faxJob->getPrimaryOriginalName() }}</div>
                            <div class="text-sm text-gray-500">
                                @if($faxJob->original_file_size > 1024 * 1024)
                                    {{ number_format($faxJob->original_file_size / (1024 * 1024), 1) }} MB
                                @else
                                    {{ number_format($faxJob->original_file_size / 1024, 1) }} KB
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        
        <div class="mt-4 text-sm text-blue-700">
            <strong>Recipient:</strong> {{ $faxJob->recipient_number }}
        </div>
    </div>

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


    @if(session('discount_applied'))
        <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800">🎉 Special 50% Off Discount Applied!</h3>
                    <p class="mt-1 text-sm text-green-700">
                        Your discount code <strong>{{ session('discount_applied')['code'] }}</strong> has been applied. 
                        You save <strong>${{ number_format(session('discount_applied')['amount'], 2) }}</strong>!
                    </p>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('fax.step2', $faxJob->hash) }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- Cover Page Section -->
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Add Cover Page
            </h2>
            <div class="flex items-center mb-4">
                <input type="checkbox" 
                       id="include_cover_page" 
                       name="include_cover_page" 
                       value="1"
                       {{ old('include_cover_page') ? 'checked' : '' }}
                       class="h-4 w-4 text-faxzen-blue focus:ring-faxzen-blue border-gray-300 rounded">
                <label for="include_cover_page" class="ml-3 block text-sm text-gray-600 cursor-pointer">
                    Include a professional cover sheet with sender/recipient details, subject line, and message.
                </label>
            </div>
            
            <div id="cover-page-fields" class="space-y-4 bg-gray-50 rounded-lg p-4" style="display: none;">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Sender Information -->
                    <div class="space-y-3">
                        <h4 class="font-semibold text-gray-800 border-b border-gray-300 pb-1">From</h4>
                        <div>
                            <label for="cover_sender_name" class="block text-sm font-medium text-gray-700 mb-1">Your Name</label>
                            <input type="text" 
                                   id="cover_sender_name" 
                                   name="cover_sender_name" 
                                   value="{{ old('cover_sender_name') }}"
                                   placeholder="John Smith"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-faxzen-blue focus:border-faxzen-blue text-sm">
                        </div>
                        <div>
                            <label for="cover_sender_company" class="block text-sm font-medium text-gray-700 mb-1">Company (Optional)</label>
                            <input type="text" 
                                   id="cover_sender_company" 
                                   name="cover_sender_company" 
                                   value="{{ old('cover_sender_company') }}"
                                   placeholder="Acme Corporation"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-faxzen-blue focus:border-faxzen-blue text-sm">
                        </div>
                        <div>
                            <label for="cover_sender_phone" class="block text-sm font-medium text-gray-700 mb-1">Your Phone (Optional)</label>
                            <input type="tel" 
                                   id="cover_sender_phone" 
                                   name="cover_sender_phone" 
                                   value="{{ old('cover_sender_phone') }}"
                                   placeholder="+1 (555) 123-4567"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-faxzen-blue focus:border-faxzen-blue text-sm">
                        </div>
                    </div>

                    <!-- Recipient Information -->
                    <div class="space-y-3">
                        <h4 class="font-semibold text-gray-800 border-b border-gray-300 pb-1">To</h4>
                        <div>
                            <label for="cover_recipient_name" class="block text-sm font-medium text-gray-700 mb-1">Recipient Name (Optional)</label>
                            <input type="text" 
                                   id="cover_recipient_name" 
                                   name="cover_recipient_name" 
                                   value="{{ old('cover_recipient_name') }}"
                                   placeholder="Jane Doe"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-faxzen-blue focus:border-faxzen-blue text-sm">
                        </div>
                        <div>
                            <label for="cover_recipient_company" class="block text-sm font-medium text-gray-700 mb-1">Recipient Company (Optional)</label>
                            <input type="text" 
                                   id="cover_recipient_company" 
                                   name="cover_recipient_company" 
                                   value="{{ old('cover_recipient_company') }}"
                                   placeholder="Widget Inc."
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-faxzen-blue focus:border-faxzen-blue text-sm">
                        </div>
                        <div class="bg-blue-50 p-3 rounded border">
                            <p class="text-sm text-blue-700">
                                <strong>Fax Number:</strong> {{ $faxJob->recipient_number }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Subject and Message -->
                <div class="space-y-3">
                    <div>
                        <label for="cover_subject" class="block text-sm font-medium text-gray-700 mb-1">Subject (Optional)</label>
                        <input type="text" 
                               id="cover_subject" 
                               name="cover_subject" 
                               value="{{ old('cover_subject') }}"
                               placeholder="Invoice for services rendered"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-faxzen-blue focus:border-faxzen-blue">
                    </div>
                    <div>
                        <label for="cover_message" class="block text-sm font-medium text-gray-700 mb-1">Message (Optional)</label>
                        <textarea id="cover_message" 
                                  name="cover_message" 
                                  rows="3"
                                  placeholder="Please find attached invoice. Contact me if you have any questions."
                                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-faxzen-blue focus:border-faxzen-blue">{{ old('cover_message') }}</textarea>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
                    <p class="text-sm text-blue-700">
                        <svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        The cover page will be added as the first page of your fax transmission.
                    </p>
                </div>
            </div>
        </div>
        <br/>
        <!-- Payment Section -->
        <div class="mt-12 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                Choose Your Payment Option
            </h2>
        
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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- One-time Payment Option -->
                        <div class="relative">
                            <input type="radio" 
                                   id="payment_type_onetime" 
                                   name="payment_type" 
                                   value="onetime"
                                   class="absolute top-4 right-4 h-4 w-4 text-faxzen-blue focus:ring-faxzen-blue border-gray-300 payment-option">
                            <label for="payment_type_onetime" class="cursor-pointer block">
                                <div class="payment-card border border-gray-300 rounded-lg p-4 transition-colors h-full relative">
                                    <div class="popular-badge absolute -top-2 left-4" style="display: none;">
                                        <span class="bg-gradient-to-r from-green-500 to-green-600 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-sm">
                                            💰 SELECTED
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-start mb-3">
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-900">Single Fax</h3>
                                                <p class="text-sm text-gray-600">Perfect for occasional fax sending</p>
                                            </div>
                                        <div class="text-right pr-8">
                                                @if($faxJob->hasDiscount())
                                                    <div class="text-lg text-gray-500 line-through">${{ number_format($faxJob->original_amount ?? $faxJob->amount, 2) }}</div>
                                                    <div class="price-amount text-2xl font-bold text-green-600">${{ number_format($faxJob->getFinalAmount(), 2) }}</div>
                                                    <div class="text-sm text-green-600 font-medium">50% OFF!</div>
                                                @else
                                                    <div class="price-amount text-2xl font-bold text-gray-900">${{ number_format($faxJob->amount, 2) }}</div>
                                                    <div class="text-sm text-gray-500">per fax</div>
                                                @endif
                                            </div>
                                        </div>
                                    <ul class="text-sm text-gray-600 space-y-1">
                                            <li>• Send one fax immediately</li>
                                            @if($faxJob->hasDiscount())
                                                <li class="text-green-600 font-medium">• 🎉 Limited time 50% discount applied!</li>
                                            @endif
                                            <li>• No account required</li>
                                            <li>• Email confirmation included</li>
                                        </ul>
                                    </div>
                                </label>
                        </div>

                        <!-- 10-Fax Package Option -->
                        <div class="relative">
                            <input type="radio" 
                                   id="payment_type_credits_10" 
                                   name="payment_type" 
                                   value="credits_10"
                                   checked
                                   class="absolute top-4 right-4 h-4 w-4 text-faxzen-blue focus:ring-faxzen-blue border-gray-300 payment-option">
                            <label for="payment_type_credits_10" class="cursor-pointer block">
                                <div class="payment-card border-2 border-green-500 bg-green-50 rounded-lg p-4 hover:border-green-600 hover:bg-green-100 transition-colors relative h-full" data-default-selected="true">
                                    <div class="popular-badge absolute -top-2 left-4">
                                        <span class="bg-gradient-to-r from-green-500 to-green-600 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-sm">
                                            🎯 MOST POPULAR
                                            </span>
                                        </div>
                                    <div class="flex justify-between items-start mb-3">
                                            <div>
                                            <h3 class="text-xl font-bold text-gray-900">10 Fax Package</h3>
                                                <p class="text-sm text-gray-600">Great for small businesses</p>
                                            <span class="savings-badge inline-block mt-1 px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                                SAVE 81% 
                                                </span>
                                            </div>
                                        <div class="text-right pr-4">
                                            <div class="price-amount text-2xl font-bold text-green-600">$15.00</div>
                                                <div class="text-sm text-gray-500">10 faxes</div>
                                                <div class="text-xs text-gray-600 font-medium">($1.50 each)</div>
                                            </div>
                                        </div>
                                    <ul class="text-sm text-gray-600 space-y-1">
                                            <li>• Send 10 faxes anytime</li>
                                            <li>• Account dashboard to track usage</li>
                                            <li>• Fax history and confirmations</li>
                                            <li>• Credits never expire</li>
                                        </ul>
                                    </div>
                                </label>
                        </div>
                    </div>
                </div>
            @endif
        @endauth
        
        @guest
            <!-- User not logged in - show payment options -->
            <div class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- One-time Payment Option -->
                    <div class="relative">
                        <input type="radio" 
                               id="payment_type_onetime_guest" 
                               name="payment_type" 
                               value="onetime"
                               class="absolute top-4 right-4 h-4 w-4 text-faxzen-blue focus:ring-faxzen-blue border-gray-300 payment-option">
                        <label for="payment_type_onetime_guest" class="cursor-pointer block">
                            <div class="payment-card border border-gray-300 rounded-lg p-4 transition-colors h-full relative">
                                <div class="popular-badge absolute -top-2 left-4" style="display: none;">
                                    <span class="bg-gradient-to-r from-green-500 to-green-600 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-sm">
                                        💰 SELECTED
                                    </span>
                                </div>
                                <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">Single Fax</h3>
                                            <p class="text-sm text-gray-600">Perfect for occasional fax sending</p>
                                        </div>
                                    <div class="text-right pr-8">
                                            @if($faxJob->hasDiscount())
                                                <div class="text-lg text-gray-500 line-through">${{ number_format($faxJob->original_amount ?? $faxJob->amount, 2) }}</div>
                                                <div class="price-amount text-2xl font-bold text-green-600">${{ number_format($faxJob->getFinalAmount(), 2) }}</div>
                                                <div class="text-sm text-green-600 font-medium">50% OFF!</div>
                                            @else
                                                <div class="price-amount text-2xl font-bold text-gray-900">${{ number_format($faxJob->amount, 2) }}</div>
                                                <div class="text-sm text-gray-500">per fax</div>
                                            @endif
                                        </div>
                                    </div>
                                <ul class="text-sm text-gray-600 space-y-1">
                                        <li>• Send one fax immediately</li>
                                        @if($faxJob->hasDiscount())
                                            <li class="text-green-600 font-medium">• 🎉 Limited time 50% discount applied!</li>
                                        @endif
                                        <li>• No account required</li>
                                        <li>• Email confirmation included</li>
                                    </ul>
                                </div>
                            </label>
                    </div>

                    <!-- 10-Fax Package Option -->
                    <div class="relative">
                        <input type="radio" 
                               id="payment_type_credits_10_guest" 
                               name="payment_type" 
                               value="credits_10"
                               checked
                               class="absolute top-4 right-4 h-4 w-4 text-faxzen-blue focus:ring-faxzen-blue border-gray-300 payment-option">
                        <label for="payment_type_credits_10_guest" class="cursor-pointer block">
                            <div class="payment-card border-2 border-green-500 bg-green-50 rounded-lg p-4 hover:border-green-600 hover:bg-green-100 transition-colors relative h-full" data-default-selected="true">
                                <div class="popular-badge absolute -top-2 left-4">
                                    <span class="bg-gradient-to-r from-green-500 to-green-600 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-sm">
                                        🎯 MOST POPULAR
                                        </span>
                                    </div>
                                <div class="flex justify-between items-start mb-3">
                                        <div>
                                        <h3 class="text-xl font-bold text-gray-900">10 Fax Package</h3>
                                            <p class="text-sm text-gray-600">Great for small businesses</p>
                                        <span class="savings-badge inline-block mt-1 px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                            SAVE 81%
                                            </span>
                                        </div>
                                    <div class="text-right pr-4">
                                        <div class="price-amount text-2xl font-bold text-green-600">$15.00</div>
                                            <div class="text-sm text-gray-500">10 faxes</div>
                                            <div class="text-xs text-gray-600 font-medium">($1.50 each)</div>
                                      
                                        </div>
                                    </div>
                                <ul class="text-sm text-gray-600 space-y-1">
                                        <li>• Send 10 faxes anytime</li>
                                        <li>• Account dashboard to track usage</li>
                                        <li>• Fax history and confirmations</li>
                                        <li>• Credits never expire</li>
                                    </ul>
                                </div>
                            </label>
                        </div>
                    </div>
                                        </div>
        @endguest
                                        </div>

        <!-- Simplified Fax Scheduling -->
        <br/>
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                Schedule Your Fax
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Schedule Later Option -->
                <div class="relative">
                    <input type="radio" 
                           id="schedule_type_later" 
                           name="schedule_type" 
                           value="later"
                           class="absolute top-4 right-4 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 schedule-option">
                    <label for="schedule_type_later" class="cursor-pointer block">
                        <div class="schedule-card border border-gray-300 rounded-lg p-4 hover:border-blue-500 hover:bg-blue-50 transition-colors h-full">
                            <div class="recommended-badge absolute -top-2 left-4" style="display: none;">
                                <span class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-sm">
                                    🕒 SELECTED
                                </span>
                                </div>
                            <div class="flex items-start mb-3 pr-8">
                                <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Schedule for Later</h3>
                                    <p class="text-sm text-gray-600 mt-1">Choose date and time</p>
                                </div>
                            </div>
                            <div id="schedule-options" class="hidden">
                                <div class="grid grid-cols-2 gap-3 mt-3">
                                    <input type="date" id="schedule_date_alt" class="border border-gray-300 rounded px-3 py-2 text-sm" min="{{ date('Y-m-d') }}">
                                    <select id="schedule_time_alt" class="border border-gray-300 rounded px-3 py-2 text-sm">
                                        <option value="">Select time...</option>
                                        <option value="09:00">9:00 AM</option>
                                        <option value="10:00">10:00 AM</option>
                                        <option value="11:00">11:00 AM</option>
                                        <option value="12:00">12:00 PM</option>
                                        <option value="13:00">1:00 PM</option>
                                        <option value="14:00">2:00 PM</option>
                                        <option value="15:00">3:00 PM</option>
                                        <option value="16:00">4:00 PM</option>
                                        <option value="17:00">5:00 PM</option>
                                        <option value="18:00">6:00 PM</option>
                                    </select>
                                </div>
                                <div class="mt-2 text-xs text-gray-500 flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    <span id="user-timezone-display">Your timezone: Loading...</span>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Send Immediately Option -->
                <div class="relative">
                    <input type="radio" 
                           id="schedule_type_now" 
                           name="schedule_type" 
                           value="now"
                           checked
                           class="absolute top-4 right-4 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 schedule-option">
                    <label for="schedule_type_now" class="cursor-pointer block">
                        <div class="schedule-card border-2 border-green-500 bg-green-50 rounded-lg p-4 hover:border-green-600 hover:bg-green-100 transition-colors h-full" data-default-selected="true">
                            <div class="recommended-badge absolute -top-2 left-4">
                                <span class="bg-gradient-to-r from-green-500 to-green-600 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-sm">
                                    ⚡ RECOMMENDED
                                </span>
                                </div>
                            <div class="flex items-start mb-3 pr-8">
                                <svg class="w-5 h-5 text-green-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Send Immediately</h3>
                                    <p class="text-sm text-gray-600 mt-1">Fastest delivery option</p>
                                </div>
                            </div>
                        </div>
                    </label>
                    <input type="hidden" name="schedule_date" value="">
                    <input type="hidden" name="schedule_time" value="">
                </div>
            </div>
        </div>

        <!-- Show confirmation email info -->
        <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <div>
                    <p class="text-sm text-gray-700">
                        <strong>Confirmation will be sent to:</strong> {{ $faxJob->sender_email }}
                    </p>
                </div>
            </div>
        </div>

        <div class="pt-4">
            <button type="submit" 
                    class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white py-4 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 font-bold text-lg shadow-lg">
                @auth
                    @if(Auth::user()->hasCredits())
                        ✅ Send My Fax FREE →
                    @else
                        🔒 Pay Securely & Send Fax →
                    @endif
                @else
                    🔒 Pay Securely & Send Fax →
                @endauth
            </button>
            </div>
        
        <!-- Customer Testimonial -->
        <div class="mt-4 mb-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4 text-center">
            <div class="flex justify-center mb-3">
                <img src="{{ asset('images/tp-small.png') }}" alt="Customer testimonial" >
                    </div>

            <blockquote class="text-sm text-gray-700 italic mb-3">
                "FaxZen saved me hours of driving to find a fax machine. The interface is so simple and my important contracts were delivered instantly. Worth every penny!"
            </blockquote>
            
            <div class="flex items-center justify-center">
                <div class="flex text-yellow-400 mr-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    </div>
                <span class="text-sm text-gray-600">— Sarah M</span>
                    </div>
                </div>

        <!-- Social Proof for Conversion -->
        <div class="mt-4 text-center">
           
            
            <!-- Trust Badges -->
            <div class="flex items-center justify-center space-x-6 text-xs text-gray-500">
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                    256-bit SSL Encryption
                </div>
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Money Back Guarantee
            </div>
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    99.9% Success Rate
        </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Schedule toggle functionality
    const scheduleNowRadio = document.getElementById('schedule_type_now');
    const scheduleLaterRadio = document.getElementById('schedule_type_later');
    const scheduleOptions = document.getElementById('schedule-options');
    
    function updateScheduleOptions() {
        if (scheduleLaterRadio && scheduleLaterRadio.checked) {
            scheduleOptions.classList.remove('hidden');
        } else {
            scheduleOptions.classList.add('hidden');
            // Clear the hidden inputs when switching back to "Send Immediately"
            document.querySelector('input[name="schedule_date"]').value = '';
            document.querySelector('input[name="schedule_time"]').value = '';
        }
    }
    
    // Function to sync the visible inputs with hidden inputs and create UTC timestamp
    function syncScheduleInputs() {
        const dateInput = document.getElementById('schedule_date_alt');
        const timeInput = document.getElementById('schedule_time_alt');
        const hiddenDateInput = document.querySelector('input[name="schedule_date"]');
        const hiddenTimeInput = document.querySelector('input[name="schedule_time"]');
        
        if (dateInput && timeInput && hiddenDateInput && hiddenTimeInput) {
            hiddenDateInput.value = dateInput.value;
            hiddenTimeInput.value = timeInput.value;
            
            // Create the UTC timestamp that the server expects
            if (dateInput.value && timeInput.value) {
                // Create the local datetime string
                const localDateTime = dateInput.value + 'T' + timeInput.value + ':00';
                
                // Parse as local time (not UTC)
                const localDate = new Date(localDateTime);
                
                // Check if the local time is in the future
                const now = new Date();
                if (localDate <= now) {
                    alert('Please select a time in the future.');
                    return;
                }
                
                // Convert to UTC timestamp
                const utcTimestamp = localDate.toISOString();
                const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
                
                console.log('Local datetime:', localDateTime);
                console.log('Local date object:', localDate);
                console.log('Current time:', now);
                console.log('Is future?', localDate > now);
                
                // Set the fields the server actually expects
                let scheduledTimeUtcInput = document.querySelector('input[name="scheduled_time_utc"]');
                let userTimezoneInput = document.querySelector('input[name="user_timezone"]');
                
                if (!scheduledTimeUtcInput) {
                    scheduledTimeUtcInput = document.createElement('input');
                    scheduledTimeUtcInput.type = 'hidden';
                    scheduledTimeUtcInput.name = 'scheduled_time_utc';
                    document.querySelector('form').appendChild(scheduledTimeUtcInput);
                }
                
                if (!userTimezoneInput) {
                    userTimezoneInput = document.createElement('input');
                    userTimezoneInput.type = 'hidden';
                    userTimezoneInput.name = 'user_timezone';
                    document.querySelector('form').appendChild(userTimezoneInput);
                }
                
                scheduledTimeUtcInput.value = utcTimestamp;
                userTimezoneInput.value = userTimezone;
                
                console.log('=== SCHEDULING DEBUG ===');
                console.log('Created UTC timestamp:', utcTimestamp);
                console.log('User timezone:', userTimezone);
                console.log('Local time selected:', localDate.toString());
                console.log('UTC time will be:', new Date(utcTimestamp).toString());
                console.log('Time difference (minutes):', (localDate - now) / (1000 * 60));
            }
        }
    }
    
    if (scheduleNowRadio && scheduleLaterRadio && scheduleOptions) {
        scheduleNowRadio.addEventListener('change', updateScheduleOptions);
        scheduleLaterRadio.addEventListener('change', updateScheduleOptions);
        
        // Add event listeners to sync the schedule inputs
        const dateInput = document.getElementById('schedule_date_alt');
        const timeInput = document.getElementById('schedule_time_alt');
        
        if (dateInput && timeInput) {
            dateInput.addEventListener('change', syncScheduleInputs);
            timeInput.addEventListener('change', syncScheduleInputs);
        }
        
        // Sync inputs before form submission
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (scheduleLaterRadio.checked) {
                    syncScheduleInputs();
                    
                    // Validate that date and time are selected
                    const dateValue = dateInput.value;
                    const timeValue = timeInput.value;
                    
                    console.log('Schedule Later selected - Date:', dateValue, 'Time:', timeValue);
                    console.log('Hidden inputs - Date:', document.querySelector('input[name="schedule_date"]').value, 'Time:', document.querySelector('input[name="schedule_time"]').value);
                    console.log('UTC timestamp:', document.querySelector('input[name="scheduled_time_utc"]')?.value);
                    console.log('User timezone:', document.querySelector('input[name="user_timezone"]')?.value);
                    
                    if (!dateValue || !timeValue) {
                        e.preventDefault();
                        alert('Please select both date and time for scheduled fax.');
                        return false;
                    }
                    
                    // Ensure UTC timestamp was created
                    const utcInput = document.querySelector('input[name="scheduled_time_utc"]');
                    if (!utcInput || !utcInput.value) {
                        e.preventDefault();
                        alert('Error processing scheduled time. Please try again.');
                        return false;
                    }
                } else {
                    console.log('Send Immediately selected');
                }
            });
        }
        
        // Initial state
        updateScheduleOptions();
        
        // Display user's timezone
        const timezoneDisplay = document.getElementById('user-timezone-display');
        if (timezoneDisplay) {
            const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            const now = new Date();
            const timezoneName = now.toLocaleString('en', {timeZoneName: 'short'}).split(' ').pop();
            timezoneDisplay.textContent = `Your timezone: ${userTimezone} (${timezoneName})`;
        }
    }

    // Cover page functionality
    const coverPageCheckbox = document.getElementById('include_cover_page');
    const coverPageFields = document.getElementById('cover-page-fields');
    
    function toggleCoverPageFields() {
        if (coverPageCheckbox.checked) {
            coverPageFields.style.display = 'block';
        } else {
            coverPageFields.style.display = 'none';
        }
    }
    
    // Initial state
    toggleCoverPageFields();
    
    // Toggle on checkbox change
    coverPageCheckbox.addEventListener('change', toggleCoverPageFields);
    
    // Payment option styling functionality
    const paymentOptions = document.querySelectorAll('.payment-option');
    
    function updatePaymentOptionStyles() {
        paymentOptions.forEach(function(radio) {
            const card = radio.closest('.relative').querySelector('.payment-card');
            const popularBadge = card.querySelector('.popular-badge');
            const savingsBadge = card.querySelector('.savings-badge');
            const priceAmount = card.querySelector('.price-amount');
            const isDefaultSelected = card.hasAttribute('data-default-selected');
            
            if (radio.checked) {
                // Selected state - always green theme
                card.classList.remove('border-gray-300', 'border-blue-500', 'bg-blue-50', 'hover:border-faxzen-blue', 'hover:bg-blue-50');
                card.classList.add('bg-green-50', 'border-2', 'border-green-500');
                // Force the styling with inline styles as backup
                card.style.backgroundColor = '#f0fdf4';
                card.style.borderColor = '#10b981';
                card.style.borderWidth = '2px';
                
                if (popularBadge) {
                    popularBadge.style.display = 'block';
                }
                
                if (savingsBadge) {
                    savingsBadge.className = savingsBadge.className.replace(/bg-gray-100|text-gray-600|bg-green-100|text-green-800/g, '');
                    savingsBadge.classList.add('bg-green-100', 'text-green-800');
                }
                
                if (priceAmount) {
                    priceAmount.className = priceAmount.className.replace(/text-gray-900|text-green-600/g, '');
                    priceAmount.classList.add('text-green-600');
                }
            } else {
                // Unselected state - always gray theme
                card.classList.remove('border-green-500', 'border-blue-500', 'border-2', 'bg-green-50', 'bg-blue-50');
                card.classList.add('border', 'border-gray-300');
                // Clear inline styles
                card.style.backgroundColor = '';
                card.style.borderColor = '';
                card.style.borderWidth = '';
                
                if (popularBadge) {
                    popularBadge.style.display = 'none';
                }
                
                if (savingsBadge) {
                    savingsBadge.className = savingsBadge.className.replace(/bg-green-100|text-green-800|bg-gray-100|text-gray-600/g, '');
                    savingsBadge.classList.add('bg-gray-100', 'text-gray-600');
                }
                
                if (priceAmount) {
                    priceAmount.className = priceAmount.className.replace(/text-green-600|text-gray-900/g, '');
                    priceAmount.classList.add('text-gray-900');
                }
            }
        });
    }
    
    // Initial state - apply styles based on checked status
    updatePaymentOptionStyles();
    
    // Add event listeners to all payment options
    paymentOptions.forEach(function(radio) {
        radio.addEventListener('change', updatePaymentOptionStyles);
    });

    // Schedule option styling functionality
    const scheduleRadios = document.querySelectorAll('.schedule-option');
    
    function updateScheduleOptionStyles() {
        scheduleRadios.forEach(function(radio) {
            const card = radio.closest('.relative').querySelector('.schedule-card');
            const recommendedBadge = card.querySelector('.recommended-badge');
            
            if (radio.checked) {
                if (radio.value === 'now') {
                    // Send Immediately selected (green theme)
                    card.className = card.className.replace(/border-gray-300|border-blue-500|border-green-500|border-\w+-\d+/g, '');
                    card.className = card.className.replace(/bg-\w+-\d+/g, '');
                    card.classList.add('bg-green-50', 'border-2', 'border-green-500');
                    
                    if (recommendedBadge) {
                        recommendedBadge.style.display = 'block';
                        recommendedBadge.querySelector('span').className = 'bg-gradient-to-r from-green-500 to-green-600 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-sm';
                        recommendedBadge.querySelector('span').innerHTML = '⚡ RECOMMENDED';
                    }
                } else {
                    // Schedule Later selected (blue theme)
                    card.className = card.className.replace(/border-gray-300|border-blue-500|border-green-500|border-\w+-\d+/g, '');
                    card.className = card.className.replace(/bg-\w+-\d+/g, '');
                    card.classList.add('bg-blue-50', 'border-2', 'border-blue-500');
                    
                    if (recommendedBadge) {
                        recommendedBadge.style.display = 'block';
                        recommendedBadge.querySelector('span').className = 'bg-gradient-to-r from-blue-500 to-blue-600 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-sm';
                        recommendedBadge.querySelector('span').innerHTML = '🕒 SELECTED';
                    }
                }
            } else {
                // Unselected state (gray theme)
                card.className = card.className.replace(/border-green-500|border-blue-500|border-2/g, '');
                card.className = card.className.replace(/bg-green-50|bg-blue-50/g, '');
                card.classList.add('border', 'border-gray-300');
                
                if (recommendedBadge) { recommendedBadge.style.display = 'none'; }
            }
        });
    }
    
    // Initialize schedule option styles
    updateScheduleOptionStyles();
    
    // Add event listeners for schedule options
    scheduleRadios.forEach(function(radio) {
        radio.addEventListener('change', updateScheduleOptionStyles);
    });
});
</script>

<div class="mt-8 bg-blue-50 rounded-lg p-6">
    <div class="flex items-center mb-4">
        <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <h3 class="text-lg font-semibold text-blue-800">What happens next?</h3>
    </div>
    
    @auth
        @if(Auth::user()->hasCredits())
            <!-- User has credits flow -->
            <ul class="text-sm text-blue-700 space-y-2">
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
                <ul class="text-sm text-blue-700 space-y-2">
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
                <ul class="text-sm text-blue-700 space-y-2">
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>Complete payment securely with Stripe</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>Your credits will be added to your account</span>
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
            <ul class="text-sm text-blue-700 space-y-2">
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
            <ul class="text-sm text-blue-700 space-y-2">
                <li class="flex items-start">
                    <span class="mr-2">•</span>
                    <span>Complete payment securely with Stripe</span>
                </li>
                <li class="flex items-start">
                    <span class="mr-2">•</span>
                    <span>Your credits will be added to your account</span>
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
            const credits10Radio = document.getElementById('payment_type_credits_10');
            const creditsRadio = document.getElementById('payment_type_credits');
            const onetimeFlow = document.getElementById('onetime-flow');
            const creditsFlow = document.getElementById('credits-flow');

            function updateFlow() {
                if (onetimeRadio && onetimeRadio.checked) {
                    onetimeFlow.classList.remove('hidden');
                    creditsFlow.classList.add('hidden');
                } else if ((credits10Radio && credits10Radio.checked) || (creditsRadio && creditsRadio.checked)) {
                    onetimeFlow.classList.add('hidden');
                    creditsFlow.classList.remove('hidden');
                }
            }

            if (onetimeRadio) onetimeRadio.addEventListener('change', updateFlow);
            if (credits10Radio) credits10Radio.addEventListener('change', updateFlow);
            if (creditsRadio) creditsRadio.addEventListener('change', updateFlow);
            
            // Trigger initial update based on default selection
            updateFlow();
        });
        </script>
    @endif
@else
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const onetimeRadio = document.getElementById('payment_type_onetime_guest');
        const credits10Radio = document.getElementById('payment_type_credits_10_guest');
        const creditsRadio = document.getElementById('payment_type_credits_guest');
        const onetimeFlow = document.getElementById('onetime-flow');
        const creditsFlow = document.getElementById('credits-flow');

        function updateFlow() {
            if (onetimeRadio && onetimeRadio.checked) {
                onetimeFlow.classList.remove('hidden');
                creditsFlow.classList.add('hidden');
            } else if ((credits10Radio && credits10Radio.checked) || (creditsRadio && creditsRadio.checked)) {
                onetimeFlow.classList.add('hidden');
                creditsFlow.classList.remove('hidden');
            }
        }

        if (onetimeRadio) onetimeRadio.addEventListener('change', updateFlow);
        if (credits10Radio) credits10Radio.addEventListener('change', updateFlow);
        if (creditsRadio) creditsRadio.addEventListener('change', updateFlow);
        
        // Trigger initial update based on default selection
        updateFlow();
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
        
        // Form validation before submit - target the MAIN fax form, not the logout form!  
        const form = document.querySelector('form.space-y-6') || document.querySelector('form[action*="step2"]');
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
                
                // Parse the date and time with explicit format in user's timezone
                const dateTimeString = `${selectedDate} ${selectedTime}`;
                
                console.log('=== DETAILED TIMEZONE CONVERSION DEBUG ===');
                console.log('Raw inputs:');
                console.log('  - selectedDate:', selectedDate);
                console.log('  - selectedTime:', selectedTime);
                console.log('  - dateTimeString:', dateTimeString);
                console.log('  - userTimezone:', userTimezone);
                console.log('  - moment.tz available:', typeof moment.tz);
                
                // Try different parsing approaches
                console.log('=== TESTING DIFFERENT PARSING METHODS ===');
                
                // Method 1: Explicit format
                const method1 = moment.tz(dateTimeString, "YYYY-MM-DD HH:mm", userTimezone);
                console.log('Method 1 (explicit format):');
                console.log('  - Valid:', method1.isValid());
                console.log('  - Local:', method1.format('YYYY-MM-DD HH:mm:ss z'));
                console.log('  - UTC:', method1.clone().utc().format('YYYY-MM-DD HH:mm:ss'));
                
                // Method 2: Let moment auto-parse then set timezone
                const method2 = moment(`${selectedDate} ${selectedTime}`).tz(userTimezone);
                console.log('Method 2 (auto-parse + tz):');
                console.log('  - Valid:', method2.isValid());
                console.log('  - Local:', method2.format('YYYY-MM-DD HH:mm:ss z'));
                console.log('  - UTC:', method2.clone().utc().format('YYYY-MM-DD HH:mm:ss'));
                
                // Method 3: Parse as local time then convert
                const method3 = moment.tz(`${selectedDate} ${selectedTime}`, "YYYY-MM-DD HH:mm", userTimezone);
                console.log('Method 3 (same as method 1):');
                console.log('  - Valid:', method3.isValid());
                console.log('  - Local:', method3.format('YYYY-MM-DD HH:mm:ss z'));
                console.log('  - UTC:', method3.clone().utc().format('YYYY-MM-DD HH:mm:ss'));
                
                // Use the most reliable method
                const scheduledDateTime = method1.isValid() ? method1 : method2;
                console.log('=== FINAL CONVERSION RESULT ===');
                console.log('Using method:', method1.isValid() ? '1 (explicit)' : '2 (auto-parse)');
                console.log('Final local time:', scheduledDateTime.format('YYYY-MM-DD HH:mm:ss z'));
                console.log('Final UTC time:', scheduledDateTime.clone().utc().format('YYYY-MM-DD HH:mm:ss'));
                console.log('Timezone offset:', scheduledDateTime.format('Z'));
                
                // Create UTC timestamp for server
                const utcTimestamp = scheduledDateTime.clone().utc().format('YYYY-MM-DD HH:mm:ss');
                console.log('UTC timestamp being sent to server:', utcTimestamp);
                console.log('=== END DEBUG ===');
                
                // Add hidden input with UTC timestamp
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'scheduled_time_utc';
                hiddenInput.value = utcTimestamp; // Use the UTC timestamp we just calculated
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