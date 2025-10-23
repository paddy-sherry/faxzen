@extends('admin.layout')

@section('title', 'Fax Jobs')
@section('page-title', 'Fax Jobs')
@section('page-description', 'Monitor fax job progress and status')

@section('content')
    <div class="p-6 space-y-6">
        
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif
        <!-- Filters -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Filters</h2>
            <form method="GET" action="{{ route('admin.fax-jobs') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-faxzen-purple">
                        <option value="">All Statuses</option>
                        <option value="preparing" {{ request('status') === 'preparing' ? 'selected' : '' }}>Preparing</option>
                        <option value="sending" {{ request('status') === 'sending' ? 'selected' : '' }}>Sending</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="email_sent" {{ request('status') === 'email_sent' ? 'selected' : '' }}>Email Sent</option>
                    </select>
                </div>
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-faxzen-purple">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-faxzen-purple">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-faxzen-purple hover:bg-purple-700 text-white px-4 py-2 rounded-md transition-colors duration-200">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Stats Summary -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            @php
                $totalJobs = \App\Models\FaxJob::count();
                $sentJobs = \App\Models\FaxJob::where('status', 'sent')->count();
                $deliveredJobs = \App\Models\FaxJob::where('status', 'delivered')->count();
                $failedJobs = \App\Models\FaxJob::where('status', 'failed')->count();
                $pendingJobs = \App\Models\FaxJob::whereIn('status', ['preparing', 'sending'])->count();
            @endphp
            
            <div class="bg-gray-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-gray-800">{{ $totalJobs }}</div>
                <div class="text-sm text-gray-600">Total Jobs</div>
            </div>
            <div class="bg-green-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-green-600">{{ $sentJobs }}</div>
                <div class="text-sm text-gray-600">Sent</div>
            </div>
            <div class="bg-green-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-green-700">{{ $deliveredJobs }}</div>
                <div class="text-sm text-gray-600">Delivered</div>
            </div>
            <div class="bg-red-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-red-600">{{ $failedJobs }}</div>
                <div class="text-sm text-gray-600">Failed</div>
            </div>
            <div class="bg-orange-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-orange-600">{{ $pendingJobs }}</div>
                <div class="text-sm text-gray-600">Pending</div>
            </div>
        </div>

        <!-- Fax Jobs Table -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Fax Jobs ({{ $faxJobs->total() }} total)</h2>
            </div>
            
            @if($faxJobs->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">To Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sender</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telnyx ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($faxJobs as $job)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $job->id }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 font-mono" style="max-width: 100px;">
                                        <div class="truncate" title="{{ config('services.telnyx.from_number', '+1800FAXZEN') }}">
                                            {{ config('services.telnyx.from_number', '+1800FAXZEN') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">
                                        {{ $job->recipient_number }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div class="text-gray-500 text-xs">{{ $job->sender_email }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900" style="max-width: 300px;">
                                        @if($job->file_path)
                                            @php
                                                $filename = basename($job->file_path);
                                            @endphp
                                            <a href="{{ route('admin.fax-jobs.file', $job->id) }}" 
                                               target="_blank" 
                                               class="text-blue-600 hover:text-blue-800 underline block truncate"
                                               title="{{ $filename }}">
                                                {{ $filename }}
                                            </a>
                                        @else
                                            <span class="text-gray-400">No file</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColors = [
                                                'delivered' => 'bg-green-100 text-green-800',
                                                'failed' => 'bg-red-100 text-red-800',
                                                'sending' => 'bg-orange-100 text-orange-800',
                                                'queued' => 'bg-blue-100 text-blue-800',
                                                'media.processed' => 'bg-yellow-100 text-yellow-800',
                                            ];
                                            
                                            // Use telnyx_status if available, fallback to status
                                            $displayStatus = $job->telnyx_status ?? $job->status;
                                            $colorClass = $statusColors[$displayStatus] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $colorClass }}">
                                            {{ $displayStatus ? ucfirst(str_replace(['_', '.'], [' ', ' '], $displayStatus)) : 'Unknown' }}
                                        </span>
                                        @if($job->status === 'failed' && $job->error_message)
                                            @php
                                                $isRetryableError = in_array($job->error_message, [
                                                    'receiver_call_dropped',
                                                    'sender_call_dropped', 
                                                    'timeout',
                                                    'busy'
                                                ]);
                                                $isEcmError = str_contains(strtolower($job->error_message), 'ecm') || 
                                                              str_contains(strtolower($job->error_message), 'error_correction');
                                            @endphp
                                            <div class="text-xs mt-1 {{ $isEcmError ? 'text-purple-600' : ($isRetryableError ? 'text-orange-600' : 'text-red-600') }}" title="{{ $job->error_message }}">
                                                {{ Str::limit($job->error_message, 30) }}
                                                @if($isEcmError)
                                                    <span class="text-purple-500" title="ECM Compatibility Issue">🔧</span>
                                                @elseif($isRetryableError)
                                                    <span class="text-orange-500">⟲</span>
                                                @endif
                                            </div>
                                        @endif
                                        @if($job->retry_attempts > 0)
                                            <div class="text-xs text-gray-500 mt-1">
                                                Retries: {{ $job->retry_attempts }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex gap-2">
                                            @if($job->telnyx_fax_id)
                                                <button type="button" 
                                                        class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-xs font-medium transition-colors duration-200 check-status-btn"
                                                        data-job-id="{{ $job->id }}"
                                                        data-telnyx-id="{{ $job->telnyx_fax_id }}">
                                                    📊 Check Status
                                                </button>
                                            @endif
                                            
                                            @if($job->status === 'failed')
                                                <form method="POST" action="{{ route('admin.fax-jobs.retry', $job->id) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs font-medium transition-colors duration-200"
                                                            data-job-id="{{ $job->id }}">
                                                        🔄 Retry
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if(!$job->telnyx_fax_id && $job->status !== 'failed')
                                                <span class="text-gray-400 text-xs">-</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">
                                        @if($job->telnyx_fax_id)
                                            <span title="{{ $job->telnyx_fax_id }}">
                                                {{ Str::limit($job->telnyx_fax_id, 20, '...') }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">Not assigned</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div>{{ $job->created_at->format('M j, Y') }}</div>
                                        <div class="text-gray-500 text-xs">{{ $job->created_at->format('g:i A') }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $faxJobs->appends(request()->query())->links() }}
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <div class="text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No fax jobs found</h3>
                        <p class="mt-1 text-sm text-gray-500">Try adjusting your filters to see more results.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle check status button clicks
    document.querySelectorAll('.check-status-btn').forEach(button => {
        button.addEventListener('click', function() {
            const jobId = this.dataset.jobId;
            const telnyxId = this.dataset.telnyxId;
            const originalText = this.innerHTML;
            const originalClass = this.className;
            
            // Disable button and show loading state
            this.disabled = true;
            this.innerHTML = '⏳ Checking...';
            this.className = this.className.replace('bg-purple-600 hover:bg-purple-700', 'bg-gray-400');
            
            // Make AJAX request to check status
            fetch(`/admin/fax-jobs/${jobId}/check-status`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showNotification('success', `Status updated! ${data.old_status || 'Unknown'} → ${data.new_status}`);
                    
                    // Update the row with new data
                    updateJobRow(jobId, data.updated_data);
                    
                    // If status changed significantly, consider refreshing the page
                    if (data.updated_data.status !== data.old_status && 
                        (data.new_status === 'delivered' || data.new_status === 'failed')) {
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    }
                } else {
                    showNotification('error', data.error || 'Failed to check status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', 'Network error while checking status');
            })
            .finally(() => {
                // Re-enable button
                this.disabled = false;
                this.innerHTML = originalText;
                this.className = originalClass;
            });
        });
    });
    
    // Handle retry form submissions to prevent page scroll to top
    document.querySelectorAll('form[action*="retry"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const jobId = this.action.split('/').pop();
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            const originalClass = submitButton.className;
            
            // Disable button and show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '⏳ Retrying...';
            submitButton.className = submitButton.className.replace('bg-blue-600 hover:bg-blue-700', 'bg-gray-400');
            
            // Make AJAX request to retry
            console.log('Making retry request to:', this.action);
            console.log('Form data:', Object.fromEntries(formData));
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': formData.get('_token')
                },
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', Object.fromEntries(response.headers.entries()));
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                console.log('Content-Type:', contentType);
                
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    // If not JSON, it might be a redirect or HTML response
                    console.warn('Response is not JSON, might be a redirect');
                    return response.text().then(text => {
                        console.log('Response text:', text);
                        throw new Error('Server returned non-JSON response (likely a redirect)');
                    });
                }
            })
            .then(data => {
                console.log('Response data:', data);
                console.log('Response data type:', typeof data);
                console.log('Response data keys:', data ? Object.keys(data) : 'null/undefined');
                
                if (data && data.success) {
                    showNotification('success', data.success);
                    
                    // Update the UI with new data instead of refreshing
                    if (data.updated_data) {
                        console.log('Updating job row with data:', data.updated_data);
                        updateJobRow(jobId, data.updated_data);
                    }
                } else if (data && data.error) {
                    showNotification('error', data.error);
                } else {
                    console.error('Unexpected response format:', data);
                    showNotification('error', 'Unexpected response format from server');
                }
            })
            .catch(error => {
                console.error('Retry request failed:', error);
                console.error('Error details:', {
                    message: error.message,
                    stack: error.stack,
                    name: error.name
                });
                showNotification('error', 'Network error while retrying fax job: ' + error.message);
            })
            .finally(() => {
                // Re-enable button
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
                submitButton.className = originalClass;
            });
        });
    });
    
    function updateJobRow(jobId, updatedData) {
        // Try multiple ways to find the row
        let row = document.querySelector(`[data-job-id="${jobId}"]`)?.closest('tr');
        
        // If not found, try to find by the retry form action URL
        if (!row) {
            const retryForm = document.querySelector(`form[action*="/retry/${jobId}"]`);
            row = retryForm?.closest('tr');
        }
        
        // If still not found, try to find by any element containing the job ID
        if (!row) {
            const elements = document.querySelectorAll(`*[data-job-id="${jobId}"]`);
            if (elements.length > 0) {
                row = elements[0].closest('tr');
            }
        }
        
        if (!row) {
            console.warn(`Could not find row for job ID: ${jobId}`);
            return;
        }
        
        // Update status badge
        const statusCell = row.querySelector('td:nth-child(6)'); // Status column
        if (statusCell) {
            const statusBadge = statusCell.querySelector('.px-2');
            if (statusBadge) {
                // Update status text - use telnyx_status if available, otherwise use status
                const displayStatus = updatedData.telnyx_status || updatedData.status;
                if (displayStatus) {
                    statusBadge.textContent = displayStatus.charAt(0).toUpperCase() + displayStatus.slice(1);
                    
                    // Update status color based on new status
                    statusBadge.className = statusBadge.className.replace(/bg-\w+-\d+\s+text-\w+-\d+/, '');
                    if (displayStatus === 'delivered') {
                        statusBadge.className += ' bg-green-100 text-green-800';
                    } else if (displayStatus === 'failed') {
                        statusBadge.className += ' bg-red-100 text-red-800';
                    } else if (displayStatus === 'sending') {
                        statusBadge.className += ' bg-orange-100 text-orange-800';
                    } else if (displayStatus === 'paid') {
                        statusBadge.className += ' bg-blue-100 text-blue-800';
                    } else {
                        statusBadge.className += ' bg-gray-100 text-gray-800';
                    }
                }
            }
            
            // Show error message if status is failed
            if (updatedData.error_message) {
                let errorDiv = statusCell.querySelector('.text-red-600');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'text-xs text-red-600 mt-1';
                    statusCell.appendChild(errorDiv);
                }
                errorDiv.textContent = updatedData.error_message.substring(0, 30) + (updatedData.error_message.length > 30 ? '...' : '');
                errorDiv.title = updatedData.error_message;
            } else {
                // Remove error message if status is no longer failed
                const errorDiv = statusCell.querySelector('.text-red-600');
                if (errorDiv) {
                    errorDiv.remove();
                }
            }
        }
        
        // Update retry button visibility based on status
        const actionsCell = row.querySelector('td:nth-child(7)'); // Actions column
        if (actionsCell) {
            const retryForm = actionsCell.querySelector('form[action*="retry"]');
            if (retryForm) {
                if (updatedData.status === 'paid' || updatedData.status === 'sending') {
                    // Hide retry button for non-failed statuses
                    retryForm.style.display = 'none';
                } else if (updatedData.status === 'failed') {
                    // Show retry button for failed status
                    retryForm.style.display = 'inline';
                }
            }
        }
    }
    
    function showNotification(type, message) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-md shadow-lg transition-all duration-300 transform translate-x-full`;
        notification.style.minWidth = '300px';
        
        if (type === 'success') {
            notification.className += ' bg-green-100 border border-green-400 text-green-700';
        } else {
            notification.className += ' bg-red-100 border border-red-400 text-red-700';
        }
        
        notification.innerHTML = `
            <div class="flex">
                <div class="flex-1">
                    <span class="block sm:inline">${message}</span>
                </div>
                <div class="ml-4">
                    <button type="button" class="close-notification text-gray-400 hover:text-gray-600">
                        <span class="sr-only">Close</span>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            removeNotification(notification);
        }, 5000);
        
        // Handle close button
        notification.querySelector('.close-notification').addEventListener('click', () => {
            removeNotification(notification);
        });
    }
    
    function removeNotification(notification) {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }
});
</script>
@endpush
