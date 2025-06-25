@extends('admin.layout')

@section('title', 'Blog Posts')
@section('page-title', 'Blog Posts')
@section('page-description', 'Manage your blog posts, create new content, and schedule publications')

@section('page-actions')
    <a href="{{ route('admin.blog.create') }}" 
       class="inline-flex items-center px-4 py-2 bg-faxzen-purple text-white text-sm font-medium rounded-md hover:bg-faxzen-purple-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-faxzen-purple">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        New Post
    </a>
@endsection

@section('content')
    <div class="p-6">
        <!-- Filters and Search -->
        <div class="mb-6 bg-gray-50 p-4 rounded-lg">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" 
                           name="search" 
                           id="search"
                           value="{{ request('search') }}"
                           placeholder="Title, excerpt, author..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-faxzen-purple focus:border-faxzen-purple">
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-faxzen-purple focus:border-faxzen-purple">
                        <option value="">All Statuses</option>
                        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>

                <!-- Featured Filter -->
                <div>
                    <label for="featured" class="block text-sm font-medium text-gray-700 mb-1">Featured</label>
                    <select name="featured" id="featured" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-faxzen-purple focus:border-faxzen-purple">
                        <option value="">All Posts</option>
                        <option value="yes" {{ request('featured') === 'yes' ? 'selected' : '' }}>Featured Only</option>
                        <option value="no" {{ request('featured') === 'no' ? 'selected' : '' }}>Not Featured</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex items-end space-x-2">
                    <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        Filter
                    </button>
                    <a href="{{ route('admin.blog.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Posts Table -->
        @if($posts->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Post</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Published</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($posts as $post)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="flex items-center">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ Str::limit($post->title, 50) }}
                                                </div>
                                                @if($post->is_featured)
                                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        Featured
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ Str::limit($post->excerpt, 80) }}
                                            </div>
                                            <div class="text-xs text-gray-400 mt-1">
                                                {{ $post->read_time_minutes }} min read • Created {{ $post->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $post->author_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
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
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($post->published_at)
                                        {{ $post->published_at->format('M j, Y') }}
                                        <br>
                                        <span class="text-xs">{{ $post->published_at->format('g:i A') }}</span>
                                    @else
                                        <span class="text-gray-400">Not scheduled</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <!-- View -->
                                        @if($post->isPublished())
                                            <a href="{{ $post->url }}" target="_blank" 
                                               class="text-blue-600 hover:text-blue-900" title="View Post">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>
                                        @endif

                                        <!-- Edit -->
                                        <a href="{{ route('admin.blog.edit', $post->id) }}" 
                                           class="text-indigo-600 hover:text-indigo-900" title="Edit Post">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>

                                        <!-- Publish/Unpublish -->
                                        @if($post->isPublished())
                                            <form method="POST" action="{{ route('admin.blog.unpublish', $post->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-orange-600 hover:text-orange-900" title="Unpublish">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @elseif(!$post->published_at || $post->published_at <= now())
                                            <form method="POST" action="{{ route('admin.blog.publish', $post->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-900" title="Publish Now">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Duplicate -->
                                        <form method="POST" action="{{ route('admin.blog.duplicate', $post->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-gray-600 hover:text-gray-900" title="Duplicate">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                </svg>
                                            </button>
                                        </form>

                                        <!-- Delete -->
                                        <form method="POST" action="{{ route('admin.blog.destroy', $post->id) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    onclick="return confirmDelete('Are you sure you want to delete this blog post? This action cannot be undone.')"
                                                    class="text-red-600 hover:text-red-900" title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $posts->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No blog posts found</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if(request()->hasAny(['search', 'status', 'featured']))
                        Try adjusting your filters or search terms.
                    @else
                        Get started by creating your first blog post.
                    @endif
                </p>
                <div class="mt-6">
                    <a href="{{ route('admin.blog.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-faxzen-purple text-white text-sm font-medium rounded-md hover:bg-faxzen-purple-dark">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Create Blog Post
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection 