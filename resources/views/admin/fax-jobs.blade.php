<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Fax Jobs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'faxzen-purple': '#8B5CF6',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">FaxZen Admin Panel</h1>
            <p class="text-gray-600">Monitor fax job progress and status</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
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
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            @php
                $totalJobs = \App\Models\FaxJob::count();
                $sentJobs = \App\Models\FaxJob::where('status', 'sent')->count();
                $deliveredJobs = \App\Models\FaxJob::where('status', 'delivered')->count();
                $failedJobs = \App\Models\FaxJob::where('status', 'failed')->count();
                $pendingJobs = \App\Models\FaxJob::whereIn('status', ['preparing', 'sending'])->count();
            @endphp
            
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <div class="text-2xl font-bold text-gray-800">{{ $totalJobs }}</div>
                <div class="text-sm text-gray-600">Total Jobs</div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <div class="text-2xl font-bold text-green-600">{{ $sentJobs }}</div>
                <div class="text-sm text-gray-600">Sent</div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <div class="text-2xl font-bold text-green-700">{{ $deliveredJobs }}</div>
                <div class="text-sm text-gray-600">Delivered</div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <div class="text-2xl font-bold text-red-600">{{ $failedJobs }}</div>
                <div class="text-sm text-gray-600">Failed</div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <div class="text-2xl font-bold text-orange-600">{{ $pendingJobs }}</div>
                <div class="text-sm text-gray-600">Pending</div>
            </div>
        </div>

        <!-- Fax Jobs Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Fax Jobs ({{ $faxJobs->total() }} total)</h2>
            </div>
            
            @if($faxJobs->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recipient</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sender</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $job->recipient_number }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div>{{ $job->sender_name }}</div>
                                        <div class="text-gray-500 text-xs">{{ $job->sender_email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($job->file_path)
                                            @php
                                                $filename = basename($job->file_path);
                                                try {
                                                    $fileUrl = \Storage::disk('r2')->temporaryUrl($job->file_path, now()->addMinutes(10));
                                                } catch (\Exception $e) {
                                                    $fileUrl = null;
                                                }
                                            @endphp
                                            @if($fileUrl)
                                                <a href="{{ $fileUrl }}" 
                                                   target="_blank" 
                                                   class="text-blue-600 hover:text-blue-800 underline"
                                                   title="Preview file">
                                                    {{ $filename }}
                                                </a>
                                            @else
                                                <span class="text-gray-500" title="File not accessible">{{ $filename }}</span>
                                            @endif
                                        @else
                                            <span class="text-gray-400">No file</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColors = [
                                                'sent' => 'bg-green-100 text-green-800',
                                                'delivered' => 'bg-green-100 text-green-800',
                                                'email_sent' => 'bg-green-100 text-green-800',
                                                'failed' => 'bg-red-100 text-red-800',
                                                'preparing' => 'bg-orange-100 text-orange-800',
                                                'sending' => 'bg-orange-100 text-orange-800',
                                            ];
                                            $colorClass = $statusColors[$job->status] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $colorClass }}">
                                            {{ ucfirst($job->status) }}
                                        </span>
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
                        <p class="mt-1 text-sm text-gray-500">No fax jobs match your current filters.</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Auto-refresh toggle -->
        <div class="mt-6 bg-white rounded-lg shadow-md p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-900">Auto-refresh</h3>
                    <p class="text-sm text-gray-500">Automatically refresh the page every 30 seconds</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="auto-refresh" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-faxzen-purple"></div>
                </label>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh functionality
        let autoRefreshInterval;
        const autoRefreshCheckbox = document.getElementById('auto-refresh');
        
        autoRefreshCheckbox.addEventListener('change', function() {
            if (this.checked) {
                autoRefreshInterval = setInterval(() => {
                    window.location.reload();
                }, 30000);
            } else {
                clearInterval(autoRefreshInterval);
            }
        });
        
        // Clear interval on page unload
        window.addEventListener('beforeunload', () => {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }
        });
    </script>
</body>
</html>
