@extends('layouts.app')

@section('title', 'Blog - Fax Tips, Guides & Industry News | FaxZen')

@section('meta_description', 'Stay updated with the latest fax tips, business guides, and industry news. Learn how to send faxes online efficiently and discover best practices for document transmission.')

@push('head')
<link rel="canonical" href="{{ route('blog.index') }}">
@endpush

@push('styles')
@endpush

@section('content')
<div class="bg-white">
    <!-- Hero Section -->
    <div class="bg-gradient-to-br from-purple-50 to-purple-100 py-16">
        <div class="container mx-auto px-4">
            <div class="text-center">
                <h1 class="text-4xl lg:text-5xl font-bold text-gray-800 mb-4">
                    FaxZen Blog
                </h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Expert insights, tips, and guides for modern fax communication. Stay ahead with the latest in digital document transmission.
                </p>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-12">
        @if(request('search'))
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">
                    Search Results for "{{ request('search') }}"
                </h2>
                @if($posts->count() == 0)
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No articles found</h3>
                        <p class="text-gray-500">Try searching with different keywords.</p>
                        <a href="{{ route('blog.index') }}" 
                           class="mt-4 inline-block bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition-colors">
                            View All Articles
                        </a>
                    </div>
                @endif
            </div>
        @else
            <!-- All Posts Section -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Latest Articles</h2>
            </div>
        @endif

        @if($posts->count() > 0)
            <!-- Posts Grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                @foreach($posts as $post)
                    <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                        @if($post->featured_image)
                            <img src="{{ $post->featured_image }}" 
                                 alt="{{ $post->title }}" 
                                 class="w-full h-48 object-cover">
                        @else
                            <div class="w-full h-48 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        @endif
                        
                        <div class="p-6">
                            <div class="flex items-center text-sm text-gray-500 mb-3">
                                <span>{{ $post->formatted_published_date }}</span>
                                <span class="mx-2">•</span>
                                <span>{{ $post->reading_time }}</span>
                                @if($post->is_featured)
                                    <span class="ml-2 bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs font-medium">Featured</span>
                                @endif
                            </div>
                            
                            <h3 class="text-lg font-bold text-gray-800 mb-3 hover:text-purple-600 transition-colors">
                                <a href="{{ $post->url }}">{{ $post->title }}</a>
                            </h3>
                            
                            <p class="text-gray-600 mb-4 leading-relaxed">{{ Str::limit($post->excerpt, 120) }}</p>
                            
                            <a href="{{ $post->url }}" 
                               class="inline-flex items-center text-purple-600 hover:text-purple-800 font-medium transition-colors">
                                Read More
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="flex justify-center">
                {{ $posts->appends(request()->query())->links() }}
            </div>
        @elseif(!request('search'))
            <!-- No Posts Yet -->
            <div class="text-center py-16">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="text-xl font-medium text-gray-900 mb-2">Coming Soon!</h3>
                <p class="text-gray-500 max-w-md mx-auto">
                    We're working on creating valuable content about fax technology, business tips, and industry insights. Check back soon for our latest articles.
                </p>
            </div>
        @endif
    </div>
</div>

<!-- Schema.org structured data for SEO -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Blog",
    "name": "FaxZen Blog",
    "description": "Expert insights, tips, and guides for modern fax communication",
    "url": "{{ route('blog.index') }}",
    "publisher": {
        "@type": "Organization",
        "name": "FaxZen",
        "url": "{{ url('/') }}"
    }
}
</script>
@endsection 