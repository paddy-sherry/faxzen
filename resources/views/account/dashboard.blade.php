@extends('layouts.app')

@section('title', 'My Account - FaxZen')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    <!-- Account Header -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ $user->name }}!</h1>
                <p class="text-gray-600 mt-1">{{ $user->email }}</p>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-faxzen-blue">{{ $user->fax_credits }}</div>
                <div class="text-sm text-gray-500">Fax Credits Remaining</div>
                @if($user->fax_credits <= 3)
                    <div class="mt-2">
                        <a href="{{ route('fax.step1') }}" class="text-sm bg-orange-100 text-orange-800 px-3 py-1 rounded-full">
                            Buy More Credits
                        </a>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="mt-4 bg-green-50 border border-green-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-600">
                        <strong>🛡️ Passwordless & Secure:</strong> 
                        Your account uses magic link authentication - no passwords to remember or lose!
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-faxzen-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">Send New Fax</h3>
                    <p class="text-sm text-gray-500">Use your credits to send another fax</p>
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('fax.step1') }}" class="w-full bg-faxzen-blue text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-faxzen-blue transition-colors font-semibold text-center block">
                    Send Fax
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">Account Settings</h3>
                    <p class="text-sm text-gray-500">Update your profile and preferences</p>
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('account.settings') }}" class="w-full bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors font-semibold text-center block">
                    Settings
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">Buy More Credits</h3>
                    <p class="text-sm text-gray-500">Get 20 more fax credits for $20</p>
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('fax.step1') }}" class="w-full bg-purple-600 text-white py-2 px-4 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-600 transition-colors font-semibold text-center block">
                    Buy Credits
                </a>
            </div>
        </div>
    </div>

    <!-- Fax History -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Fax History</h2>
            <p class="text-sm text-gray-600">Your recent fax transmissions</p>
        </div>
        
        @if($faxJobs->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recipient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sent</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($faxJobs as $faxJob)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $faxJob->file_original_name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $faxJob->recipient_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($faxJob->status === 'delivered')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Delivered</span>
                                    @elseif($faxJob->status === 'failed')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Failed</span>
                                    @elseif($faxJob->status === 'sent')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Sent</span>
                                    @elseif($faxJob->status === 'paid')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Processing</span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($faxJob->status) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $faxJob->created_at->format('M j, Y g:i A') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('account.fax-job', $faxJob) }}" class="text-faxzen-blue hover:text-blue-700">View Details</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($faxJobs->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $faxJobs->links() }}
                </div>
            @endif
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-4 text-sm font-medium text-gray-900">No faxes sent yet</h3>
                <p class="mt-2 text-sm text-gray-500">Get started by sending your first fax!</p>
                <div class="mt-6">
                    <a href="{{ route('fax.step1') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-faxzen-blue hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-faxzen-blue">
                        Send Your First Fax
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection