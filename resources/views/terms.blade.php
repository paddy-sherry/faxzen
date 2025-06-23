@extends('layouts.app')

@section('title', 'Terms of Service - FaxZen Online Fax Service')

@section('meta_description', 'Terms of Service for FaxZen online fax service. Learn about our service terms, user responsibilities, and policies for sending faxes online.')

@section('content')
<div class="bg-white rounded-lg shadow-md p-8 max-w-4xl mx-auto">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-4">Terms of Service</h1>
        <p class="text-center text-gray-600">Last updated: June 23, 2025</p>
    </div>

    <div class="prose prose-lg max-w-none text-gray-700 space-y-6">
        
        <section>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">1. Acceptance of Terms</h2>
            <p>
                By accessing and using FaxZen ("the Service"), you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">2. Service Description</h2>
            <p>
                FaxZen provides an online fax transmission service that allows users to send PDF documents and images via fax on a pay-per-transmission basis. Our service includes:
            </p>
            <ul class="list-disc pl-6 space-y-2">
                <li>Document upload and conversion</li>
                <li>Fax transmission to domestic and international numbers</li>
                <li>Real-time delivery status tracking</li>
                <li>Email confirmation of delivery status</li>
                <li>Secure document handling and automatic deletion</li>
            </ul>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">3. User Responsibilities</h2>
            <p>Users of FaxZen agree to:</p>
            <ul class="list-disc pl-6 space-y-2">
                <li>Provide accurate recipient fax numbers</li>
                <li>Only transmit documents they have the legal right to send</li>
                <li>Not use the service for spam, harassment, or illegal activities</li>
                <li>Not transmit copyrighted material without proper authorization</li>
                <li>Comply with all applicable laws and regulations</li>
                <li>Not attempt to circumvent or abuse the service</li>
            </ul>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">4. Prohibited Content</h2>
            <p>Users may not transmit documents containing:</p>
            <ul class="list-disc pl-6 space-y-2">
                <li>Illegal, harmful, threatening, abusive, or defamatory content</li>
                <li>Copyrighted material without proper authorization</li>
                <li>Personal information of third parties without consent</li>
                <li>Spam, unsolicited advertising, or promotional materials</li>
                <li>Malicious code, viruses, or harmful software</li>
                <li>Content that violates any applicable laws or regulations</li>
            </ul>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">5. Payment and Refunds</h2>
            <p>
                Our service operates on a pay-per-use basis with pricing displayed on our website. Payment is processed securely through Stripe before fax transmission begins.
            </p>
            <p>
                <strong>Refund Policy:</strong> Refunds will be automatically issued for faxes that fail to deliver due to technical issues on our end. Refunds will not be issued for failed deliveries due to incorrect recipient numbers, busy signals, or recipient equipment issues.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">6. Privacy and Data Security</h2>
            <p>
                We take your privacy seriously. All documents are:
            </p>
            <ul class="list-disc pl-6 space-y-2">
                <li>Encrypted during transmission using 256-bit SSL encryption</li>
                <li>Automatically deleted from our servers after 24 hours</li>
                <li>Never shared with third parties except as required for fax transmission</li>
                <li>Handled in compliance with applicable privacy laws</li>
            </ul>
            <p>
                For more details, please review our Privacy Policy.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">7. Service Availability</h2>
            <p>
                While we strive to maintain 99.9% uptime, FaxZen does not guarantee uninterrupted service availability. We reserve the right to:
            </p>
            <ul class="list-disc pl-6 space-y-2">
                <li>Perform scheduled maintenance that may temporarily interrupt service</li>
                <li>Suspend service for security or technical reasons</li>
                <li>Modify or discontinue features with reasonable notice</li>
            </ul>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">8. Limitation of Liability</h2>
            <p>
                FaxZen's liability is limited to the amount paid for the specific fax transmission in question. We are not liable for:
            </p>
            <ul class="list-disc pl-6 space-y-2">
                <li>Consequential, indirect, or incidental damages</li>
                <li>Loss of business, profits, or data</li>
                <li>Delays in transmission due to recipient equipment issues</li>
                <li>Third-party actions or failures</li>
                <li>Force majeure events beyond our reasonable control</li>
            </ul>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">9. Intellectual Property</h2>
            <p>
                The FaxZen service, including its design, functionality, and content, is protected by copyright and other intellectual property laws. Users retain ownership of their transmitted documents but grant FaxZen a limited license to process and transmit them as part of the service.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">10. Termination</h2>
            <p>
                We reserve the right to terminate or suspend access to our service immediately, without prior notice, for conduct that we believe violates these Terms of Service or is harmful to other users, us, or third parties.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">11. Changes to Terms</h2>
            <p>
                We reserve the right to modify these terms at any time. Changes will be effective immediately upon posting to our website. Continued use of the service after changes constitutes acceptance of the new terms.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">12. Governing Law</h2>
            <p>
                These terms shall be governed by and construed in accordance with the laws of the jurisdiction where FaxZen operates, without regard to conflict of law principles.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">13. Contact Information</h2>
            <p>
                If you have any questions about these Terms of Service, please contact us through our website at <a href="https://faxzen.com" class="text-purple-600 hover:text-purple-700">faxzen.com</a>.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">14. Severability</h2>
            <p>
                If any provision of these Terms of Service is found to be unenforceable or invalid, that provision will be limited or eliminated to the minimum extent necessary so that the Terms of Service will otherwise remain in full force and effect.
            </p>
        </section>

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