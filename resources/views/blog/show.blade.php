@extends('layouts.app')

@section('title', $post->meta_title ?: $post->title)

@section('meta_description', $post->meta_description ?: Str::limit(strip_tags($post->excerpt), 155))

@push('head')
<link rel="canonical" href="{{ route('blog.show', $post->slug) }}">
@endpush

@push('styles')
<style>
.blog-content p {
    margin-bottom: 1.25rem;
    line-height: 1.7;
}
.blog-content h2 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    font-size: 1.5rem;
    font-weight: 600;
    color: #374151;
}
.blog-content h3 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    font-size: 1.5rem;
    font-weight: 600;
    color: #374151;
}
.blog-content ul {
    margin-bottom: 1.25rem;
    padding-left: 1.5rem;
    list-style-type: disc;
}
.blog-content ol {
    margin-bottom: 1.25rem;
    padding-left: 1.5rem;
    list-style-type: decimal;
}
.blog-content li {
    margin-bottom: 0.5rem;
    display: list-item;
}
.blog-content table {
    margin: 1.5rem 0;
    width: 100%;
    border-collapse: collapse;
}
.blog-content table th,
.blog-content table td {
    border: 1px solid #dee2e6;
    padding: 12px;
    text-align: left;
}
.blog-content table th {
    background-color: #f8f9fa;
    font-weight: 600;
}
.blog-content table tr:nth-child(even) {
    background-color: #f8f9fa;
}
.blog-content div[style*="background-color"] {
    margin: 1.5rem 0;
}

/* Smooth scrolling for jump links */
html {
    scroll-behavior: smooth;
}

/* Offset scroll position to account for any fixed headers */
.blog-content h2[id] {
    scroll-margin-top: 20px;
}

/* Table of contents styling enhancements */
.blog-content a[href^="#"]:hover {
    text-decoration: underline;
}
</style>
@endpush

@section('content')
<div class="bg-white">
    <!-- Breadcrumbs -->
    <div class="bg-gray-50 py-4">
        <div class="container mx-auto px-4">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ url('/') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-purple-600">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                            </svg>
                            Home
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <a href="{{ route('blog.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-purple-600 md:ml-2">Blog</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ Str::limit($post->title, 50) }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <article class="container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto">
            <!-- Article Header -->
            <header class="mb-8">
                @if($post->is_featured)
                    <div class="mb-4">
                        <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm font-medium">Featured Article</span>
                    </div>
                @endif
                
                <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6 leading-tight">
                    {{ $post->title }}
                </h1>
                
                <div class="flex flex-wrap items-center text-gray-600 mb-6">
                    <div class="flex items-center mr-6 mb-2">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span>{{ $post->author_name }}</span>
                    </div>
                    <div class="flex items-center mr-6 mb-2">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>{{ $post->formatted_published_date_with_updated }}</span>
                    </div>
                    <div class="flex items-center mb-2">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ $post->reading_time }}</span>
                    </div>
                </div>

                @if($post->excerpt)
                    <div class="text-xl text-gray-700 leading-relaxed mb-8 p-6 bg-gray-50 rounded-lg border-l-4 border-purple-500">
                        {{ $post->excerpt }}
                    </div>
                @endif
            </header>

            <!-- Featured Image -->
            @if($post->featured_image)
                <div class="mb-8">
                    <img src="{{ $post->featured_image }}" 
                         alt="{{ $post->title }}" 
                         class="w-full rounded-lg shadow-lg">
                </div>
            @endif

            <!-- Article Content -->
            <div class="prose prose-lg max-w-none mb-12 blog-content">
                {!! $post->content_with_cta !!}
            </div>

            <!-- Article Footer -->
            <footer class="border-t border-gray-200 pt-8">
                <!-- Social Sharing -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Share this article</h3>
                    <div class="flex space-x-4">
                        <a href="https://twitter.com/intent/tweet?text={{ urlencode($post->title) }}&url={{ urlencode($post->url) }}" 
                           target="_blank" 
                           class="inline-flex items-center px-4 py-2 bg-blue-400 text-white rounded-md hover:bg-blue-500 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                            Twitter
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($post->url) }}" 
                           target="_blank" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                            LinkedIn
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($post->url) }}" 
                           target="_blank" 
                           class="inline-flex items-center px-4 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            Facebook
                        </a>
                    </div>
                </div>

                <!-- Back to Blog -->
                <div class="text-center">
                    <a href="{{ route('blog.index') }}" 
                       class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold rounded-md transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Blog
                    </a>
                </div>
            </footer>
        </div>
    </article>

    @if($relatedPosts->count() > 0)
        <!-- Related Posts -->
        <section class="bg-gray-50 py-16">
            <div class="container mx-auto px-4">
                <div class="max-w-6xl mx-auto">
                    <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">Related Articles</h2>
                    <div class="grid md:grid-cols-3 gap-8">
                        @foreach($relatedPosts as $relatedPost)
                            <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                                @if($relatedPost->featured_image)
                                    <img src="{{ $relatedPost->featured_image }}" 
                                         alt="{{ $relatedPost->title }}" 
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
                                        <span>{{ $relatedPost->formatted_published_date }}</span>
                                        <span class="mx-2">•</span>
                                        <span>{{ $relatedPost->reading_time }}</span>
                                    </div>
                                    
                                    <h3 class="text-lg font-bold text-gray-800 mb-3 hover:text-purple-600 transition-colors">
                                        <a href="{{ $relatedPost->url }}">{{ $relatedPost->title }}</a>
                                    </h3>
                                    
                                    <p class="text-gray-600 mb-4 leading-relaxed">{{ Str::limit($relatedPost->excerpt, 100) }}</p>
                                    
                                    <a href="{{ $relatedPost->url }}" 
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
                </div>
            </div>
        </section>
    @endif
</div>

<!-- Schema.org structured data for SEO -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BlogPosting",
    "headline": "{{ $post->title }}",
    "description": "{{ $post->meta_description ?: Str::limit(strip_tags($post->excerpt), 155) }}",
    "author": {
        "@type": "Person",
        "name": "{{ $post->author_name }}"
    },
    "publisher": {
        "@type": "Organization",
        "name": "FaxZen",
        "url": "{{ url('/') }}"
    },
    "datePublished": "{{ $post->published_at->toISOString() }}",
    "dateModified": "{{ $post->updated_at->toISOString() }}",
    "url": "{{ $post->url }}",
    @if($post->featured_image)
    "image": "{{ $post->featured_image }}",
    @endif
    "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "{{ $post->url }}"
    }
}
</script>
@endsection 