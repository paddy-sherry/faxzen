@extends('layouts.app')

@section('title', 'Step 1 of 3 - Upload Document | FaxZen')

@section('content')
<div class="bg-white rounded-lg shadow-md p-8">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-4">Send Fax Online For $3</h1>
        <p class="text-center text-gray-600 mb-6">Send faxes online with instant delivery. Trusted by thousands of people and businesses worldwide. </p>
        
       
        
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
                PDF Document <span class="text-red-500">*</span>
            </label>
            <div id="drop-zone" class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-all duration-200">
                <div class="space-y-1 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="flex flex-col items-center text-sm text-gray-600">
                        <label for="pdf_file" class="relative cursor-pointer bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white px-6 py-3 rounded-md font-medium focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-purple-500 transition-all duration-200 mb-2">
                            <span>Upload a PDF file</span>
                            <input id="pdf_file" name="pdf_file" type="file" accept=".pdf" class="sr-only" required>
                        </label>
                        <p class="text-gray-500">or drag and drop</p>
                    </div>
                    <p class="text-xs text-gray-500">PDF up to 50MB</p>
                    <p id="file-name" class="text-sm text-green-600 font-medium hidden"></p>
                </div>
            </div>
        </div>

        <div>
            <label for="recipient_number" class="block text-sm font-medium text-gray-700 mb-2">
                Recipient Fax Number <span class="text-red-500">*</span>
            </label>
            <input type="tel" 
                   id="recipient_number" 
                   name="recipient_number" 
                   value="{{ old('recipient_number') }}"
                   placeholder="e.g., +1-555-123-4567"
                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-faxzen-purple focus:border-faxzen-purple"
                   required>
            <p class="mt-1 text-sm text-gray-500">Include country code (e.g., +1 for US/Canada)</p>
        </div>

        <div class="pt-4">
            <button type="submit" 
                    class="w-full bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white py-3 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200 font-semibold">
                Continue to Step 2
            </button>
        </div>

         <!-- Trust Badges -->
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
                    <span class="text-sm text-gray-600 font-medium">Instant Delivery</span>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="mt-8 bg-white border border-purple-500 rounded-lg p-6">
    <h3 class="text-lg font-semibold bg-gradient-to-r from-purple-500 to-purple-600 bg-clip-text text-transparent mb-4">How it works</h3>
    <div class="grid md:grid-cols-3 gap-6">
        <div class="text-center">
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">1</div>
            <h4 class="font-semibold text-gray-800">Upload PDF</h4>
            <p class="text-sm text-gray-600">Upload your PDF document and enter the fax number</p>
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

<!-- Hero Section with Image -->
<div class="mt-12 bg-white rounded-lg shadow-md overflow-hidden">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
        <!-- Text Content -->
        <div class="p-8 lg:p-12 text-center">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-800 mb-6">
            Send A Fax Online With Ease
            </h2>
            <p class="text-lg text-gray-600 leading-relaxed max-w-4xl mx-auto">
            We’ve revolutionized the way you send faxes online. No more searching for outdated fax machines or dealing with the hassles of traditional faxing. With FaxZen, it’s as simple as uploading your PDF document, paying just $3, and watching your fax go on its way – no machine required. Join thousands of satisfied users who have embraced the future of faxing with FaxZen, where sending faxes is fast, easy, and hassle-free.
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
            if (file.type === 'application/pdf') {
                fileInput.files = files;
                showFileName(file.name);
            } else {
                alert('Please select a PDF file.');
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
});
</script>
@endsection 