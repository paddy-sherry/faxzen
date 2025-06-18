@extends('layouts.app')

@section('title', 'Step 1 of 3 - Upload Document | FaxZen')

@section('content')
<div class="bg-white rounded-lg shadow-md p-8">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-8">Send Fax Online For $3</h1>
        <div class="flex items-center justify-center mb-4">
            <div class="flex items-center space-x-4">
                <div class="flex items-center justify-center w-8 h-8 bg-faxzen-blue text-white rounded-full font-semibold">1</div>
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
            <div id="drop-zone" class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-faxzen-blue transition-colors">
                <div class="space-y-1 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="flex flex-col items-center text-sm text-gray-600">
                        <label for="pdf_file" class="relative cursor-pointer bg-faxzen-blue text-white px-6 py-3 rounded-md font-medium hover:bg-blue-700 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-faxzen-blue transition-colors mb-2">
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
                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-faxzen-blue focus:border-faxzen-blue"
                   required>
            <p class="mt-1 text-sm text-gray-500">Include country code (e.g., +1 for US/Canada)</p>
        </div>

        <div class="pt-4">
            <button type="submit" 
                    class="w-full bg-faxzen-blue text-white py-3 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-faxzen-blue transition-colors font-semibold">
                Continue to Step 2
            </button>
        </div>
    </form>
</div>

<div class="mt-8 bg-blue-50 rounded-lg p-6">
    <h3 class="text-lg font-semibold text-faxzen-blue mb-4">How it works</h3>
    <div class="grid md:grid-cols-3 gap-6">
        <div class="text-center">
            <div class="bg-faxzen-blue text-white rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">1</div>
            <h4 class="font-semibold text-gray-800">Upload PDF</h4>
            <p class="text-sm text-gray-600">Upload your PDF document and enter the fax number</p>
        </div>
        <div class="text-center">
            <div class="bg-faxzen-blue text-white rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">2</div>
            <h4 class="font-semibold text-gray-800">Enter Details</h4>
            <p class="text-sm text-gray-600">Provide your name and email address</p>
        </div>
        <div class="text-center">
            <div class="bg-faxzen-blue text-white rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">3</div>
            <h4 class="font-semibold text-gray-800">Pay & Send</h4>
            <p class="text-sm text-gray-600">Complete payment and we'll send your fax instantly</p>
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
        dropZone.classList.add('border-faxzen-blue', 'bg-blue-50');
    }

    function unhighlight(e) {
        dropZone.classList.remove('border-faxzen-blue', 'bg-blue-50');
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