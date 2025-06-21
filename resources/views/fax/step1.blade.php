@extends('layouts.app')

@section('title', '▷ Send Fax Online for $3 - Fast & Secure Online Fax Service')

@section('meta_description', 'Send fax online instantly for $3. Upload PDF documents, enter fax number, and send faxes online with tracked delivery. No fax machine required. Trusted worldwide')

@section('content')
<div class="bg-white rounded-lg shadow-md p-8">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-4">Send Fax Online</h1>
        <p class="text-center text-gray-600 mb-6">Send faxes online for $3 with tracked delivery. Trusted by thousands of people and businesses worldwide. </p>
        <div class="text-center mb-6">
            <div class="flex items-center justify-center space-x-6 mb-4">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm text-gray-600 font-medium">SSL Encrypted</span>
                </div>
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m-3-6h6" />
                    </svg>
                    <span class="text-sm text-gray-600 font-medium">No Hidden Fees</span>
                </div>
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span class="text-sm text-gray-600 font-medium">Tracked Delivery</span>
                </div>
            </div>
       
        
        <div class="flex items-center justify-center mb-4">
            <div class="flex items-center space-x-4">
                <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-full font-semibold">1</div>
                <div class="w-16 h-0.5 bg-gray-300"></div>
                <div class="flex items-center justify-center w-8 h-8 bg-gray-300 text-gray-600 rounded-full font-semibold">2</div>
                <div class="w-16 h-0.5 bg-gray-300"></div>
                <div class="flex items-center justify-center w-8 h-8 bg-gray-300 text-gray-600 rounded-full font-semibold">3</div>
            </div>
        </div>
        <h2 class="text-xl font-bold text-center text-gray-800">Upload Document</h2>
        <p class="text-center text-gray-600 mt-2">Upload your PDF and enter the destination fax number</p>
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

    <form action="{{ route('fax.step1') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        
        <div>
            <label for="pdf_file" class="block text-sm font-medium text-gray-700 mb-2">
                Document or Image <span class="text-red-500">*</span>
            </label>
            <div id="drop-zone" class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-all duration-200">
                <div class="space-y-1 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="flex flex-col items-center text-sm text-gray-600">
                        <label for="pdf_file" class="relative cursor-pointer bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white px-6 py-3 rounded-md font-medium focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-purple-500 transition-all duration-200 mb-2">
                            <span>Upload file</span>
                            <input id="pdf_file" name="pdf_file" type="file" accept=".pdf,.jpg,.jpeg,.png,.gif,.svg,.webp" class="sr-only" required>
                        </label>
                        <p class="text-gray-500">or drag and drop</p>
                    </div>
                    <p class="text-xs text-gray-500">PDF, JPG, PNG, GIF, SVG, WebP up to 50MB</p>
                    <p id="file-name" class="text-sm text-green-600 font-medium hidden"></p>
                </div>
            </div>
        </div>

        <div>
            <label for="recipient_number" class="block text-sm font-medium text-gray-700 mb-2">
                Recipient Fax Number <span class="text-red-500">*</span>
            </label>
            <div class="flex">
                <select id="country_code" name="country_code" class="flex-shrink-0 bg-white border border-gray-300 rounded-l-md px-3 py-2 focus:outline-none focus:ring-faxzen-purple focus:border-faxzen-purple">
                    <option value="+1" data-flag="🇺🇸">🇺🇸 +1</option>
                    <option value="+1" data-flag="🇨🇦">🇨🇦 +1</option>
                    <option value="+44" data-flag="🇬🇧">🇬🇧 +44</option>
                    <option value="+353" data-flag="🇮🇪">🇮🇪 +353</option>
                    <option value="+33" data-flag="🇫🇷">🇫🇷 +33</option>
                    <option value="+49" data-flag="🇩🇪">🇩🇪 +49</option>
                    <option value="+39" data-flag="🇮🇹">🇮🇹 +39</option>
                    <option value="+34" data-flag="🇪🇸">🇪🇸 +34</option>
                    <option value="+351" data-flag="🇵🇹">🇵🇹 +351</option>
                    <option value="+31" data-flag="🇳🇱">🇳🇱 +31</option>
                    <option value="+41" data-flag="🇨🇭">🇨🇭 +41</option>
                    <option value="+43" data-flag="🇦🇹">🇦🇹 +43</option>
                    <option value="+32" data-flag="🇧🇪">🇧🇪 +32</option>
                    <option value="+352" data-flag="🇱🇺">🇱🇺 +352</option>
                    <option value="+45" data-flag="🇩🇰">🇩🇰 +45</option>
                    <option value="+46" data-flag="🇸🇪">🇸🇪 +46</option>
                    <option value="+47" data-flag="🇳🇴">🇳🇴 +47</option>
                    <option value="+358" data-flag="🇫🇮">🇫🇮 +358</option>
                    <option value="+354" data-flag="🇮🇸">🇮🇸 +354</option>
                    <option value="+48" data-flag="🇵🇱">🇵🇱 +48</option>
                    <option value="+420" data-flag="🇨🇿">🇨🇿 +420</option>
                    <option value="+421" data-flag="🇸🇰">🇸🇰 +421</option>
                    <option value="+36" data-flag="🇭🇺">🇭🇺 +36</option>
                    <option value="+40" data-flag="🇷🇴">🇷🇴 +40</option>
                    <option value="+359" data-flag="🇧🇬">🇧🇬 +359</option>
                    <option value="+385" data-flag="🇭🇷">🇭🇷 +385</option>
                    <option value="+386" data-flag="🇸🇮">🇸🇮 +386</option>
                    <option value="+372" data-flag="🇪🇪">🇪🇪 +372</option>
                    <option value="+371" data-flag="🇱🇻">🇱🇻 +371</option>
                    <option value="+370" data-flag="🇱🇹">🇱🇹 +370</option>
                    <option value="+30" data-flag="🇬🇷">🇬🇷 +30</option>
                    <option value="+90" data-flag="🇹🇷">🇹🇷 +90</option>
                    <option value="+7" data-flag="🇷🇺">🇷🇺 +7</option>
                    <option value="+380" data-flag="🇺🇦">🇺🇦 +380</option>
                    <option value="+61" data-flag="🇦🇺">🇦🇺 +61</option>
                    <option value="+64" data-flag="🇳🇿">🇳🇿 +64</option>
                    <option value="+65" data-flag="🇸🇬">🇸🇬 +65</option>
                    <option value="+60" data-flag="🇲🇾">🇲🇾 +60</option>
                    <option value="+66" data-flag="🇹🇭">🇹🇭 +66</option>
                    <option value="+84" data-flag="🇻🇳">🇻🇳 +84</option>
                    <option value="+62" data-flag="🇮🇩">🇮🇩 +62</option>
                    <option value="+63" data-flag="🇵🇭">🇵🇭 +63</option>
                    <option value="+852" data-flag="🇭🇰">🇭🇰 +852</option>
                    <option value="+886" data-flag="🇹🇼">🇹🇼 +886</option>
                    <option value="+81" data-flag="🇯🇵">🇯🇵 +81</option>
                    <option value="+82" data-flag="🇰🇷">🇰🇷 +82</option>
                    <option value="+86" data-flag="🇨🇳">🇨🇳 +86</option>
                    <option value="+91" data-flag="🇮🇳">🇮🇳 +91</option>
                    <option value="+92" data-flag="🇵🇰">🇵🇰 +92</option>
                    <option value="+880" data-flag="🇧🇩">🇧🇩 +880</option>
                    <option value="+94" data-flag="🇱🇰">🇱🇰 +94</option>
                    <option value="+98" data-flag="🇮🇷">🇮🇷 +98</option>
                    <option value="+964" data-flag="🇮🇶">🇮🇶 +964</option>
                    <option value="+966" data-flag="🇸🇦">🇸🇦 +966</option>
                    <option value="+971" data-flag="🇦🇪">🇦🇪 +971</option>
                    <option value="+972" data-flag="🇮🇱">🇮🇱 +972</option>
                    <option value="+20" data-flag="🇪🇬">🇪🇬 +20</option>
                    <option value="+27" data-flag="🇿🇦">🇿🇦 +27</option>
                    <option value="+234" data-flag="🇳🇬">🇳🇬 +234</option>
                    <option value="+254" data-flag="🇰🇪">🇰🇪 +254</option>
                    <option value="+55" data-flag="🇧🇷">🇧🇷 +55</option>
                    <option value="+54" data-flag="🇦🇷">🇦🇷 +54</option>
                    <option value="+56" data-flag="🇨🇱">🇨🇱 +56</option>
                    <option value="+57" data-flag="🇨🇴">🇨🇴 +57</option>
                    <option value="+51" data-flag="🇵🇪">🇵🇪 +51</option>
                    <option value="+58" data-flag="🇻🇪">🇻🇪 +58</option>
                    <option value="+52" data-flag="🇲🇽">🇲🇽 +52</option>
                </select>
                <input type="tel" 
                       id="recipient_number" 
                       name="recipient_number" 
                       value="{{ old('recipient_number') }}"
                       placeholder="5551234567"
                       class="flex-1 px-3 py-2 border border-l-0 border-gray-300 rounded-r-md shadow-sm focus:outline-none focus:ring-faxzen-purple focus:border-faxzen-purple"
                       required>
            </div>
            <p class="mt-1 text-sm text-gray-500">Select your country and enter the fax number without the country code</p>
        </div>

        <div class="pt-4">
            <button type="submit" 
                    class="w-full bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white py-3 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200 font-semibold">
                Continue to Step 2
            </button>
        </div>

         <!-- Trust Badges -->
         
        </div>
    </form>
</div>

<!-- Hero Section with Image -->
<div class="mt-12 bg-white rounded-lg shadow-md overflow-hidden">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
        <!-- Text Content -->
        <div class="p-8 lg:p-12 text-center">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-800 mb-6">
            Send A Fax Online With Ease
            </h2>
            <p class="text-lg text-gray-600 leading-relaxed max-w-4xl mx-auto">
            We've revolutionized the way you send faxes online. No more searching for outdated fax machines or dealing with the hassles of traditional faxing. With FaxZen, it's as simple as uploading your PDF document, paying just $3, and watching your fax go on its way – no machine required. Join thousands of satisfied users who have embraced the future of faxing with FaxZen, where sending faxes is fast, easy, and hassle-free.
            </p>
        </div>
        
        <!-- Image -->
        <div class="h-[400px] bg-gray-100">
            <img 
                src="https://imagedelivery.net/k0P4EcPiouU_XzyGSmgmUw/307e046b-9566-4a97-1495-12bf4e94f000/public" 
                alt="Send Fax Online"
                class="w-full object-contain"
            >
        </div>
    </div>
</div>

<div class="mt-8 bg-white border border-purple-500 rounded-lg p-6">
    <h3 class="text-lg font-semibold bg-gradient-to-r from-purple-500 to-purple-600 bg-clip-text text-transparent mb-4">How it works</h3>
    <div class="grid md:grid-cols-3 gap-6">
        <div class="text-center">
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">1</div>
            <h4 class="font-semibold text-gray-800">Upload File</h4>
            <p class="text-sm text-gray-600">Upload your PDF document or image and enter the fax number</p>
        </div>
        <div class="text-center">
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">2</div>
            <h4 class="font-semibold text-gray-800">Enter Details</h4>
            <p class="text-sm text-gray-600">Provide your name and email address</p>
        </div>
        <div class="text-center">
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">3</div>
            <h4 class="font-semibold text-gray-800">Pay & Send</h4>
            <p class="text-sm text-gray-600">Complete payment and we'll send your fax instantly</p>
        </div>
    </div>
</div>

<!-- Real-Time Tracking Section -->
<div class="mt-8 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-8">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
        <!-- Text Content -->
        <div>
            <h3 class="text-2xl font-bold text-gray-800 mb-4">
                Track Your Fax Delivery in Real-Time
            </h3>
            <p class="text-gray-600 mb-4">
                Never wonder if your fax was delivered again. Our advanced tracking system shows you exactly where your fax is in the delivery process.
            </p>
            <div class="space-y-2">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-sm text-gray-700">Live status updates every step of the way</span>
                </div>
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-sm text-gray-700">Confirmed delivery notifications</span>
                </div>
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-sm text-gray-700">No more guessing if your fax arrived</span>
                </div>
            </div>
        </div>
        
        <!-- Screenshot Placeholder -->
        <div class="bg-white rounded-lg shadow-lg p-4">
            <div class="bg-gray-100 rounded-md h-64 flex items-center justify-center">
                <div class="text-center text-gray-500">
                    
                    <img src="https://imagedelivery.net/k0P4EcPiouU_XzyGSmgmUw/465b9b6d-58da-48b7-340e-37cc26372600/public">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Customer Reviews & Trust Indicators -->
<div class="mt-8 grid md:grid-cols-2 gap-8">
    <!-- Customer Reviews -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">What Our Customers Say</h3>
        <div class="space-y-4">
            <div class="border-b border-gray-200 pb-4">
                <div class="flex items-center mb-2">
                    <div class="flex text-yellow-400">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L0 6.91l6.564-.955L10 0l3.436 5.955L20 6.91l-5.245 4.635L15.878 18z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L0 6.91l6.564-.955L10 0l3.436 5.955L20 6.91l-5.245 4.635L15.878 18z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L0 6.91l6.564-.955L10 0l3.436 5.955L20 6.91l-5.245 4.635L15.878 18z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L0 6.91l6.564-.955L10 0l3.436 5.955L20 6.91l-5.245 4.635L15.878 18z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L0 6.91l6.564-.955L10 0l3.436 5.955L20 6.91l-5.245 4.635L15.878 18z"/></svg>
                    </div>
                    <span class="ml-2 text-sm text-gray-600">5/5</span>
                </div>
                <p class="text-sm text-gray-700">"Fast, reliable, and so much easier than traditional fax machines. Sent my documents instantly!"</p>
                <p class="text-xs text-gray-500 mt-1">- Sarah M., Small Business Owner</p>
            </div>
            <div class="border-b border-gray-200 pb-4">
                <div class="flex items-center mb-2">
                    <div class="flex text-yellow-400">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L0 6.91l6.564-.955L10 0l3.436 5.955L20 6.91l-5.245 4.635L15.878 18z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L0 6.91l6.564-.955L10 0l3.436 5.955L20 6.91l-5.245 4.635L15.878 18z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L0 6.91l6.564-.955L10 0l3.436 5.955L20 6.91l-5.245 4.635L15.878 18z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L0 6.91l6.564-.955L10 0l3.436 5.955L20 6.91l-5.245 4.635L15.878 18z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L0 6.91l6.564-.955L10 0l3.436 5.955L20 6.91l-5.245 4.635L15.878 18z"/></svg>
                    </div>
                    <span class="ml-2 text-sm text-gray-600">5/5</span>
                </div>
                <p class="text-sm text-gray-700">"Perfect for sending contracts. The delivery confirmation gives me peace of mind."</p>
                <p class="text-xs text-gray-500 mt-1">- Mike R., Legal Professional</p>
            </div>
            <div>
                <div class="flex items-center mb-2">
                    <div class="flex text-yellow-400">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L0 6.91l6.564-.955L10 0l3.436 5.955L20 6.91l-5.245 4.635L15.878 18z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L0 6.91l6.564-.955L10 0l3.436 5.955L20 6.91l-5.245 4.635L15.878 18z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L0 6.91l6.564-.955L10 0l3.436 5.955L20 6.91l-5.245 4.635L15.878 18z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L0 6.91l6.564-.955L10 0l3.436 5.955L20 6.91l-5.245 4.635L15.878 18z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L0 6.91l6.564-.955L10 0l3.436 5.955L20 6.91l-5.245 4.635L15.878 18z"/></svg>
                    </div>
                    <span class="ml-2 text-sm text-gray-600">5/5</span>
                </div>
                <p class="text-sm text-gray-700">"Needed to send tax documents to the IRS last minute. Worked perfectly and saved me a trip to the store!"</p>
                <p class="text-xs text-gray-500 mt-1">- Jennifer L., Taxpayer</p>
            </div>
        </div>
    </div>

    <!-- Security & Trust -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Your Security is Our Priority</h3>
        <div class="space-y-4">
            <div class="flex items-start space-x-3">
                <svg class="w-6 h-6 text-green-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <div>
                    <h4 class="font-semibold text-gray-800">256-bit SSL Encryption</h4>
                    <p class="text-sm text-gray-600">Your documents are encrypted during transmission and storage</p>
                </div>
            </div>
            <div class="flex items-start space-x-3">
                <svg class="w-6 h-6 text-green-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <div>
                    <h4 class="font-semibold text-gray-800">Automatic Document Deletion</h4>
                    <p class="text-sm text-gray-600">Files are permanently deleted after 24 hours</p>
                </div>
            </div>
            <div class="flex items-start space-x-3">
                <svg class="w-6 h-6 text-green-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" />
                </svg>
                <div>
                    <h4 class="font-semibold text-gray-800">Private & Confidential</h4>
                    <p class="text-sm text-gray-600">Your documents are handled with complete privacy</p>
                </div>
            </div>
            <div class="flex items-start space-x-3">
                <svg class="w-6 h-6 text-green-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <div>
                    <h4 class="font-semibold text-gray-800">Secure Payments via Stripe</h4>
                    <p class="text-sm text-gray-600">Your payment information is protected by industry standards</p>
                </div>
            </div>
        </div>
        
        <div class="mt-6 pt-4 border-t border-gray-200">
            <div class="flex items-center justify-center space-x-4">
                <div class="text-center">
                    <div class="text-2xl font-bold bg-gradient-to-r from-purple-500 to-purple-600 bg-clip-text text-transparent">50,000+</div>
                    <div class="text-xs text-gray-600">Faxes Sent</div>
                </div>
             
                <div class="text-center">
                    <div class="text-2xl font-bold bg-gradient-to-r from-purple-500 to-purple-600 bg-clip-text text-transparent">24/7</div>
                    <div class="text-xs text-gray-600">Support</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SEO Content Section -->
<div class="mt-12 bg-white rounded-lg shadow-md p-8">
    <div class="max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Why Choose to Send Fax Online?</h2>
        
        <div class="prose prose-lg max-w-none text-gray-700 space-y-6">
            <p>
                In today's digital world, the ability to <strong>send fax online</strong> has revolutionized how businesses and individuals handle document transmission. Gone are the days of searching for a physical fax machine, dealing with busy signals, or worrying about paper jams. When you send a fax online, you gain access to a reliable, efficient, and cost-effective solution that works 24/7 from anywhere in the world.
            </p>

            <h3 class="text-2xl font-semibold text-gray-800 mt-8 mb-4">The Modern Way to Send Fax Online</h3>
            <p>
                Our online fax service makes it incredibly simple to <strong>send a fax online</strong> in just three easy steps. Upload your PDF document, enter the recipient's fax number, and complete your payment – it's that straightforward. Unlike traditional fax machines that require physical presence and maintenance, our platform allows you to transmit faxes online from your computer, tablet, or smartphone at any time of day.
            </p>

            <p>
                When you choose to send faxes online with FaxZen, you're choosing a service that's trusted by thousands of businesses worldwide. Our platform processes over 50,000 faxes monthly, ensuring your documents reach their destination quickly and securely. Whether you need to send contracts, medical records, legal documents, or tax forms, our service provides the reliability you need.
            </p>

            <h3 class="text-2xl font-semibold text-gray-800 mt-8 mb-4">Benefits of Choosing to Send Fax Online</h3>
            <p>
                The advantages of deciding to use online fax services are numerous and compelling. First, there's the convenience factor – you can send documents via fax from anywhere with an internet connection, eliminating the need to visit an office or find a physical fax machine. This is particularly valuable for remote workers, travelers, or anyone who needs to send urgent documents outside of business hours.
            </p>

            <p>
                Cost-effectiveness is another major benefit when you send faxes digitally. Traditional fax machines require significant upfront investment, ongoing maintenance, dedicated phone lines, and supplies like paper and toner. When you <strong>send a fax online</strong>, you only pay for what you use – just $3 per fax with no hidden fees or monthly subscriptions.
            </p>

            <p>
                Security is paramount when sending faxes through our online platform. We employ 256-bit SSL encryption to protect your documents during transmission, ensuring that sensitive information remains confidential. Additionally, all documents are automatically deleted from our servers after 24 hours, providing an extra layer of privacy protection that traditional fax machines cannot offer.
            </p>



            <h3 class="text-2xl font-semibold text-gray-800 mt-8 mb-4">The Future of Document Transmission</h3>
            <p>
                As more organizations recognize the benefits of digital transformation, the trend toward <strong>sending faxes online</strong> continues to grow. Our service represents the evolution of faxing technology, combining the legal validity and widespread acceptance of traditional fax with the convenience and efficiency of modern internet technology.
            </p>

            <p>
                When you choose FaxZen for your document transmission needs, you're not just sending a document – you're participating in a more sustainable, efficient, and reliable method of business communication. Our instant delivery confirmation gives you peace of mind, knowing your important documents have reached their destination successfully.
            </p>

            <p>
                Ready to experience the convenience of modern faxing? Join thousands of satisfied customers who have made the smart choice to use our online fax service. Upload your document today and discover why our platform is the preferred solution for fast, secure, and affordable digital fax transmission.
            </p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('pdf_file');
    const fileName = document.getElementById('file-name');

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    // Highlight drop area when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

    // Handle dropped files
    dropZone.addEventListener('drop', handleDrop, false);

    // Handle file selection via button
    fileInput.addEventListener('change', handleFileSelect, false);

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    function highlight(e) {
        dropZone.classList.add('border-gray-400', 'bg-gradient-to-br', 'from-purple-50', 'to-purple-100');
    }

    function unhighlight(e) {
        dropZone.classList.remove('border-gray-400', 'bg-gradient-to-br', 'from-purple-50', 'to-purple-100');
    }

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;

        if (files.length > 0) {
            const file = files[0];
            const allowedTypes = [
                'application/pdf',
                'image/jpeg',
                'image/jpg', 
                'image/png',
                'image/gif',
                'image/svg+xml',
                'image/webp'
            ];
            
            if (allowedTypes.includes(file.type)) {
                fileInput.files = files;
                showFileName(file.name);
            } else {
                alert('Please select a PDF document or image file (JPG, PNG, GIF, SVG, WebP).');
            }
        }
    }

    function handleFileSelect(e) {
        const file = e.target.files[0];
        if (file) {
            showFileName(file.name);
        }
    }

    function showFileName(name) {
        fileName.textContent = `Selected: ${name}`;
        fileName.classList.remove('hidden');
    }

    // Fax number validation
    const countryCodeSelect = document.getElementById('country_code');
    const recipientNumberInput = document.getElementById('recipient_number');
    const form = document.querySelector('form');
    
    // Add validation feedback elements
    const phoneContainer = recipientNumberInput.closest('div').parentElement;
    let validationMessage = phoneContainer.querySelector('.validation-message');
    if (!validationMessage) {
        validationMessage = document.createElement('p');
        validationMessage.className = 'validation-message mt-1 text-sm hidden';
        phoneContainer.appendChild(validationMessage);
    }

    function validateFaxNumber() {
        const countryCode = countryCodeSelect.value;
        const number = recipientNumberInput.value.replace(/[^0-9]/g, '');
        
        // Clear previous validation
        validationMessage.classList.add('hidden');
        recipientNumberInput.classList.remove('border-red-500', 'border-green-500');
        
        if (!number) return;
        
        let isValid = true;
        let message = '';
        
        // Basic length check
        if (number.length < 7) {
            isValid = false;
            message = 'Fax number must be at least 7 digits long.';
        } else if (number.length > 15) {
            isValid = false;
            message = 'Fax number is too long (maximum 15 digits).';
        } else {
            // Country-specific validation
            switch (countryCode) {
                case '+1': // US/Canada
                    if (number.length !== 10) {
                        isValid = false;
                        message = 'US/Canada fax numbers must be 10 digits long.';
                    } else if (['0', '1'].includes(number[0])) {
                        isValid = false;
                        message = 'Area code cannot start with 0 or 1.';
                    } else if (['0', '1'].includes(number[3])) {
                        isValid = false;
                        message = 'Exchange code cannot start with 0 or 1.';
                                                              } else if (['000', '911'].includes(number.substring(0, 3))) {
                         isValid = false;
                         message = 'Invalid area code.';
                     }
                    break;
                case '+44': // UK
                    if (number.length < 10 || number.length > 11) {
                        isValid = false;
                        message = 'UK fax numbers must be 10-11 digits long.';
                    }
                    break;
                case '+33': // France
                    if (number.length !== 9) {
                        isValid = false;
                        message = 'French fax numbers must be 9 digits long.';
                    }
                    break;
                case '+49': // Germany
                    if (number.length < 10 || number.length > 12) {
                        isValid = false;
                        message = 'German fax numbers must be 10-12 digits long.';
                    }
                    break;
                case '+39': // Italy
                    if (number.length < 9 || number.length > 10) {
                        isValid = false;
                        message = 'Italian fax numbers must be 9-10 digits long.';
                    }
                    break;
                case '+34': // Spain
                    if (number.length !== 9) {
                        isValid = false;
                        message = 'Spanish fax numbers must be 9 digits long.';
                    }
                    break;
                case '+61': // Australia
                    if (number.length !== 9) {
                        isValid = false;
                        message = 'Australian fax numbers must be 9 digits long.';
                    }
                    break;
                default:
                    if (number.length < 7 || number.length > 12) {
                        isValid = false;
                        message = 'Fax number must be 7-12 digits long for this country.';
                    }
            }
        }
        
        // Check for invalid patterns
        if (isValid) {
            // All same digits
            if (/^(\d)\1+$/.test(number)) {
                isValid = false;
                message = 'Fax number cannot be all the same digit.';
            }
            // Sequential digits
            else if (['0123456789', '1234567890', '9876543210', '0987654321'].includes(number)) {
                isValid = false;
                message = 'Fax number cannot be sequential digits.';
            }
                         // Common test numbers (be less restrictive for fax numbers)
             else if (['0000000000', '1111111111', '2222222222', '3333333333', '4444444444', '6666666666', '7777777777', '8888888888', '9999999999'].includes(number)) {
                 isValid = false;
                 message = 'Please enter a real fax number.';
             }
        }
        
        // Show validation result
        if (number.length >= 7) { // Only show validation for numbers long enough
            if (isValid) {
                recipientNumberInput.classList.add('border-green-500');
                validationMessage.textContent = '✓ Valid fax number';
                validationMessage.className = 'validation-message mt-1 text-sm text-green-600';
                validationMessage.classList.remove('hidden');
            } else {
                recipientNumberInput.classList.add('border-red-500');
                validationMessage.textContent = message;
                validationMessage.className = 'validation-message mt-1 text-sm text-red-600';
                validationMessage.classList.remove('hidden');
            }
        }
        
        return isValid;
    }

    // Update placeholder based on country selection
    function updatePlaceholder() {
        const countryCode = countryCodeSelect.value;
        const placeholders = {
            '+1': '5551234567',        // US/Canada
            '+44': '2012345678',       // UK
            '+33': '123456789',        // France  
            '+49': '301234567',        // Germany
            '+39': '0212345678',       // Italy
            '+34': '912345678',        // Spain
            '+353': '12345678',        // Ireland
            '+61': '212345678',        // Australia
            '+81': '312345678',        // Japan
            '+86': '1012345678',       // China
        };
        
        recipientNumberInput.placeholder = placeholders[countryCode] || '1234567890';
    }

        // Add event listeners for real-time validation
    recipientNumberInput.addEventListener('input', validateFaxNumber);
    countryCodeSelect.addEventListener('change', function() {
        updatePlaceholder();
        validateFaxNumber();
    });
    
    // Set initial placeholder
    updatePlaceholder();
    
    // Validate on form submit
    form.addEventListener('submit', function(e) {
        if (!validateFaxNumber() && recipientNumberInput.value.replace(/[^0-9]/g, '').length >= 7) {
            e.preventDefault();
            recipientNumberInput.focus();
        }
    });
});
</script>
@endsection 