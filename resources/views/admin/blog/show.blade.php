@extends('admin.layout')

@section('title', $post->title)
@section('page-title', $post->title)
@section('page-description', 'Preview of blog post')

@section('page-actions')
    <div class="flex space-x-3">
        @if($post->isPublished())
            <a href="{{ $post->url }}" target="_blank"
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                </svg>
                View Live
            </a>
        @endif
        <a href="{{ route('admin.blog.edit', $post->id) }}" 
           class="inline-flex items-center px-4 py-2 bg-faxzen-purple text-white text-sm font-medium rounded-md hover:bg-faxzen-purple-dark">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Edit Post
        </a>
        <a href="{{ route('admin.blog.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700">
            ← Back to Posts
        </a>
    </div>
@endsection

@section('content')
    <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Post Meta -->
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span class="text-sm text-gray-600">{{ $post->author_name }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-sm text-gray-600">{{ $post->read_time_minutes }} min read</span>
                            </div>
                            @if($post->published_at)
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="text-sm text-gray-600">
                                        @if($post->isPublished())
                                            Published {{ $post->published_at->format('M j, Y') }}
                                        @else
                                            Scheduled for {{ $post->published_at->format('M j, Y g:i A') }}
                                        @endif
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center space-x-2">
                            @if($post->is_featured)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Featured
                                </span>
                            @endif
                            @if($post->isPublished())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Published
                                </span>
                            @elseif($post->published_at && $post->published_at > now())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Scheduled
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Draft
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Featured Image -->
                @if($post->featured_image)
                    <div class="mb-6">
                        <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-64 object-cover rounded-lg">
                    </div>
                @endif

                <!-- Excerpt -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                    <p class="text-gray-700 font-medium">{{ $post->excerpt }}</p>
                </div>

                <!-- Content Preview -->
                <div class="prose prose-lg max-w-none">
                    <div class="blog-content">
                        {!! $post->contentWithCta !!}
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        @if($post->isPublished())
                            <form method="POST" action="{{ route('admin.blog.unpublish', $post->id) }}">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700">
                                    Unpublish
                                </button>
                            </form>
                        @elseif(!$post->published_at || $post->published_at <= now())
                            <form method="POST" action="{{ route('admin.blog.publish', $post->id) }}">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                    Publish Now
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('admin.blog.duplicate', $post->id) }}">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Duplicate Post
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.blog.destroy', $post->id) }}" 
                              onsubmit="return confirmDelete('Are you sure you want to delete this blog post? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                Delete Post
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Post Details -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Post Details</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <strong class="text-gray-700">Slug:</strong>
                            <div class="mt-1 font-mono text-xs bg-gray-100 p-2 rounded">{{ $post->slug }}</div>
                        </div>
                        <div>
                            <strong class="text-gray-700">URL:</strong>
                            <div class="mt-1 font-mono text-xs bg-gray-100 p-2 rounded break-all">{{ $post->url }}</div>
                        </div>
                        <div>
                            <strong class="text-gray-700">Created:</strong>
                            <div class="text-gray-600">{{ $post->created_at->format('M j, Y g:i A') }}</div>
                        </div>
                        <div>
                            <strong class="text-gray-700">Last Updated:</strong>
                            <div class="text-gray-600">{{ $post->updated_at->format('M j, Y g:i A') }}</div>
                        </div>
                    </div>
                </div>

                <!-- SEO Information -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">SEO Information</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <strong class="text-gray-700">Meta Title:</strong>
                            <div class="text-gray-600 mt-1">{{ $post->meta_title ?: $post->title }}</div>
                            <div class="text-xs text-gray-500 mt-1">{{ strlen($post->meta_title ?: $post->title) }} characters</div>
                        </div>
                        <div>
                            <strong class="text-gray-700">Meta Description:</strong>
                            <div class="text-gray-600 mt-1">{{ $post->meta_description ?: Str::limit(strip_tags($post->excerpt), 155) }}</div>
                            <div class="text-xs text-gray-500 mt-1">{{ strlen($post->meta_description ?: Str::limit(strip_tags($post->excerpt), 155)) }} characters</div>
                        </div>
                        @if($post->meta_keywords)
                            <div>
                                <strong class="text-gray-700">Meta Keywords:</strong>
                                <div class="text-gray-600 mt-1">{{ $post->meta_keywords }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Table of Contents Preview -->
                @if($post->tableOfContents)
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Table of Contents</h3>
                        <div class="text-sm">
                            {!! $post->tableOfContents !!}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .blog-content {
        line-height: 1.7;
    }

    .blog-content p {
        margin-bottom: 1.25rem;
    }

    .blog-content h2 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #374151;
        margin-top: 2rem;
        margin-bottom: 1rem;
        scroll-margin-top: 20px;
    }

    .blog-content ul, .blog-content ol {
        margin-bottom: 1.25rem;
        padding-left: 1.5rem;
    }

    .blog-content ul {
        list-style-type: disc;
    }

    .blog-content ol {
        list-style-type: decimal;
    }

    .blog-content li {
        display: list-item;
        margin-bottom: 0.5rem;
    }

    .blog-content table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.5rem 0;
    }

    .blog-content th,
    .blog-content td {
        border: 1px solid #e5e7eb;
        padding: 0.75rem;
        text-align: left;
    }

    .blog-content th {
        background-color: #f9fafb;
        font-weight: 600;
    }

    .blog-content .callout-box {
        background-color: #f3f4f6;
        border-left: 4px solid #8b5cf6;
        padding: 1rem;
        margin: 1.5rem 0;
        border-radius: 0 0.375rem 0.375rem 0;
    }

    .blog-content .toc-box {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #8b5cf6;
        padding: 1.5rem;
        margin: 2rem 0;
        border-radius: 0.5rem;
    }

    .blog-content .toc-box h3 {
        margin-top: 0;
        margin-bottom: 1rem;
        font-size: 1.125rem;
        color: #1f2937;
    }

    .blog-content .toc-box ul {
        margin-bottom: 0;
        list-style-type: none;
        padding-left: 0;
    }

    .blog-content .toc-box li {
        margin-bottom: 0.5rem;
    }

    .blog-content .toc-box a {
        color: #6366f1;
        text-decoration: none;
        font-weight: 500;
    }

    .blog-content .toc-box a:hover {
        color: #4f46e5;
        text-decoration: underline;
    }
</style>
@endpush 