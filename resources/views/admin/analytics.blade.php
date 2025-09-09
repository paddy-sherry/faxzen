@extends('admin.layout')

@section('title', 'Conversion Analytics')
@section('page-title', 'Conversion Analytics')
@section('page-description', 'Track conversion sources and AdWords performance')

@section('content')
    <div class="p-6 space-y-6">
        
        <!-- Date Range Filter -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Date Range</h2>
            <form method="GET" action="{{ route('admin.analytics') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                    <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-faxzen-purple">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                    <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-faxzen-purple">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-faxzen-purple hover:bg-purple-700 text-white px-4 py-2 rounded-md transition-colors duration-200">
                        Update Report
                    </button>
                </div>
            </form>
        </div>

        <!-- Overall Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Total Conversions</h3>
                <p class="text-3xl font-bold text-faxzen-purple">{{ number_format($totalConversions) }}</p>
                <p class="text-sm text-gray-600">{{ $dateFrom }} to {{ $dateTo }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Total Revenue</h3>
                <p class="text-3xl font-bold text-green-600">${{ number_format($totalRevenue, 2) }}</p>
                <p class="text-sm text-gray-600">{{ $dateFrom }} to {{ $dateTo }}</p>
            </div>
        </div>

        <!-- Traffic Source Comparison -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Conversions by Traffic Source</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach($sourceComparison as $source => $count)
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <h4 class="font-semibold text-gray-700 capitalize">{{ $source === 'adwords' ? 'AdWords' : ucfirst($source) }}</h4>
                        <p class="text-2xl font-bold {{ $source === 'adwords' ? 'text-blue-600' : ($source === 'organic' ? 'text-green-600' : 'text-gray-600') }}">
                            {{ number_format($count) }}
                        </p>
                        @if($totalConversions > 0)
                            <p class="text-sm text-gray-500">{{ number_format(($count / $totalConversions) * 100, 1) }}%</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Daily Status Chart -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">📊 Daily Fax Status Breakdown</h3>
            <div class="relative" style="height: 400px;">
                <canvas id="dailyStatusChart"></canvas>
            </div>
        </div>

        <!-- Detailed Breakdown -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Detailed Source Analysis</h3>
            @if($conversionsBySource->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Traffic Source</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Conversions</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Order Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">% of Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($conversionsBySource as $source)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-medium capitalize {{ $source->traffic_source === 'adwords' ? 'text-blue-600' : ($source->traffic_source === 'organic' ? 'text-green-600' : 'text-gray-900') }}">
                                            {{ $source->traffic_source ?: 'Unknown' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($source->conversions) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($source->revenue, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($source->avg_order_value, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ number_format(($source->conversions / $totalConversions) * 100, 1) }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500">No conversion data available for the selected date range.</p>
            @endif
        </div>

        <!-- AdWords Campaign Performance -->
        @if($topCampaigns->count() > 0)
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">🎯 Top AdWords Campaigns</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-blue-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campaign</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Conversions</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue per Conversion</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($topCampaigns as $campaign)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-blue-600">
                                        {{ $campaign->utm_campaign }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($campaign->conversions) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($campaign->revenue, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($campaign->revenue / $campaign->conversions, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- AdWords Keyword Performance -->
        @if($topKeywords->count() > 0)
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">🔑 Top Converting Keywords</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-green-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keyword</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Conversions</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue per Conversion</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($topKeywords as $keyword)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-green-600">
                                        {{ $keyword->utm_term }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($keyword->conversions) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($keyword->revenue, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($keyword->revenue / $keyword->conversions, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Performance Insights -->
        <div class="bg-blue-50 p-6 rounded-lg">
            <h3 class="text-lg font-semibold text-blue-800 mb-4">📊 Key Insights</h3>
            <div class="space-y-2 text-blue-700">
                @php
                    $adwordsCount = $sourceComparison['adwords'] ?? 0;
                    $organicCount = $sourceComparison['organic'] ?? 0;
                    $adwordsShare = $totalConversions > 0 ? ($adwordsCount / $totalConversions) * 100 : 0;
                    $organicShare = $totalConversions > 0 ? ($organicCount / $totalConversions) * 100 : 0;
                @endphp
                
                <p>• <strong>AdWords Performance:</strong> {{ number_format($adwordsCount) }} conversions ({{ number_format($adwordsShare, 1) }}% of total)</p>
                <p>• <strong>Organic Performance:</strong> {{ number_format($organicCount) }} conversions ({{ number_format($organicShare, 1) }}% of total)</p>
                
                @if($adwordsCount > 0 && $organicCount > 0)
                    <p>• <strong>AdWords vs Organic Ratio:</strong> {{ number_format($adwordsCount / $organicCount, 2) }}:1</p>
                @endif
                
                @if($topKeywords->count() > 0)
                    <p>• <strong>Top Converting Keyword:</strong> "{{ $topKeywords->first()->utm_term }}" ({{ $topKeywords->first()->conversions }} conversions)</p>
                @endif
            </div>
        </div>

        <!-- Navigation Links -->
        <div class="flex space-x-4">
            <a href="{{ route('admin.fax-jobs') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md transition-colors duration-200">
                ← Back to Fax Jobs
            </a>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Process PHP data for Chart.js
    const dailyStatusData = @json($dailyStatusData);
    
    // Get all unique dates in the range
    const startDate = new Date('{{ $dateFrom }}');
    const endDate = new Date('{{ $dateTo }}');
    const dates = [];
    const currentDate = new Date(startDate);
    
    while (currentDate <= endDate) {
        dates.push(currentDate.toISOString().split('T')[0]);
        currentDate.setDate(currentDate.getDate() + 1);
    }
    
    // Status color mapping
    const statusColors = {
        'sent': '#10b981',      // green - delivered
        'paid': '#10b981',      // green - delivered (treating paid as delivered)
        'pending': '#f59e0b',   // yellow - pending
        'payment_pending': '#f97316', // orange - payment pending
        'failed': '#ef4444'     // red - failed
    };
    
    // Status label mapping
    const statusLabels = {
        'sent': 'Delivered',
        'paid': 'Delivered',
        'pending': 'Pending',
        'payment_pending': 'Payment Pending',
        'failed': 'Failed'
    };
    
    // Prepare data for each status
    const statuses = ['sent', 'paid', 'pending', 'payment_pending', 'failed'];
    const datasets = [];
    
    // Group sent and paid together as "delivered"
    const combinedStatuses = ['delivered', 'pending', 'payment_pending', 'failed'];
    
    combinedStatuses.forEach(status => {
        const data = dates.map(date => {
            const dayData = dailyStatusData[date] || [];
            let count = 0;
            
            if (status === 'delivered') {
                // Combine sent and paid counts
                count = dayData.filter(item => item.status === 'sent' || item.status === 'paid')
                              .reduce((sum, item) => sum + item.count, 0);
            } else {
                const statusItem = dayData.find(item => item.status === status);
                count = statusItem ? statusItem.count : 0;
            }
            
            return count;
        });
        
        datasets.push({
            label: status === 'delivered' ? 'Delivered' : statusLabels[status],
            data: data,
            backgroundColor: status === 'delivered' ? statusColors['sent'] : statusColors[status],
            borderColor: status === 'delivered' ? statusColors['sent'] : statusColors[status],
            borderWidth: 1
        });
    });
    
    // Create the chart
    const ctx = document.getElementById('dailyStatusChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: dates.map(date => {
                return new Date(date + 'T00:00:00').toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric'
                });
            }),
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    stacked: true,
                    title: {
                        display: true,
                        text: 'Date'
                    }
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Faxes'
                    },
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Daily Fax Jobs by Status ({{ $dateFrom }} to {{ $dateTo }})'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        footer: function(tooltipItems) {
                            let total = 0;
                            tooltipItems.forEach(function(tooltipItem) {
                                total += tooltipItem.parsed.y;
                            });
                            return 'Total: ' + total + ' faxes';
                        }
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });
});
</script>
@endpush
