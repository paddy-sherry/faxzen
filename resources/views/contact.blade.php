@extends('layouts.app')

@section('title', 'Contact Us - FaxZen Online Fax Service Support')

@section('meta_description', 'Contact FaxZen support for help with online fax services. Get assistance with fax delivery, technical issues, billing questions, and more.')

@section('content')
<div class="bg-white rounded-lg shadow-md p-8 max-w-4xl mx-auto">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-4">Contact Us</h1>
        <p class="text-center text-gray-600">We're here to help with your online fax needs</p>
    </div>

    <div class="grid md:grid-cols-2 gap-8">
        <!-- Contact Information -->
        <div class="space-y-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Get in Touch</h2>
                <p class="text-gray-600 mb-6">
                    Have questions about our online fax service? Need help with a fax delivery? We're here to assist you.
                </p>
            </div>

            <!-- Email Support -->
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Email Support</h3>
                    <p class="text-gray-600 mb-2">For general inquiries and support</p>
                    <a href="mailto:info@faxzen.com" class="text-purple-600 hover:text-purple-700 font-medium">info@faxzen.com</a>
                    <p class="text-sm text-gray-500 mt-1">Response time: Within 24 hours</p>
                </div>
            </div>

            <!-- Business Hours -->
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Support Hours</h3>
                    <p class="text-gray-600">Monday - Friday: 9:00 AM - 6:00 PM EST</p>
                    <p class="text-gray-600">Saturday - Sunday: 10:00 AM - 4:00 PM EST</p>
                    <p class="text-sm text-gray-500 mt-1">Emergency support available 24/7</p>
                </div>
            </div>

            <!-- Response Time -->
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Fast Response</h3>
                    <p class="text-gray-600">Most support requests are answered within 2-4 hours during business hours</p>
                    <p class="text-sm text-gray-500 mt-1">Urgent fax delivery issues: Within 1 hour</p>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div>
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Frequently Asked Questions</h2>
            
            <div class="space-y-4">
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-2">How do I know if my fax was delivered?</h3>
                    <p class="text-gray-600 text-sm">You'll receive real-time status updates and an email confirmation once your fax is successfully delivered. You can also track the progress on your status page.</p>
                </div>

                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-2">What file formats do you support?</h3>
                    <p class="text-gray-600 text-sm">We support PDF, JPG, PNG, GIF, SVG, and WebP files up to 50MB in size.</p>
                </div>

                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-2">Can I get a refund if my fax fails?</h3>
                    <p class="text-gray-600 text-sm">Yes! We automatically refund failed faxes due to technical issues on our end. Refunds are processed within 5-7 business days.</p>
                </div>

                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-2">How secure is my document?</h3>
                    <p class="text-gray-600 text-sm">All documents are encrypted with 256-bit SSL during transmission and automatically deleted from our servers after 24 hours.</p>
                </div>

                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-2">Do you support international fax numbers?</h3>
                    <p class="text-gray-600 text-sm">Yes! We support fax delivery to over 100 countries worldwide. Select your country code when entering the recipient number.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Form Section -->
    <div class="mt-12 pt-8 border-t border-gray-200">
        <div class="max-w-2xl mx-auto">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Send Us a Message</h2>
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-6 text-center">
                <svg class="w-16 h-16 text-purple-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Email Us Directly</h3>
                <p class="text-gray-600 mb-4">
                    For the fastest response, send us an email with your question or concern. Include your fax tracking information if applicable.
                </p>
                <a href="mailto:info@faxzen.com?subject=FaxZen Support Request" 
                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold rounded-md transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Send Email
                </a>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="mt-12 pt-8 border-t border-gray-200 text-center">
        <a href="{{ route('fax.step1') }}" 
           class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold rounded-md transition-all duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Send Fax
        </a>
    </div>
</div>
@endsection 