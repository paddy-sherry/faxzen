@extends('admin.layout')

@section('title', 'Edit Blog Post')
@section('page-title', 'Edit Blog Post')
@section('page-description', 'Edit "' . Str::limit($post->title, 50) . '"')

@section('page-actions')
    <div class="flex space-x-3">
        @if($post->isPublished())
            <a href="{{ $post->url }}" target="_blank"
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                </svg>
                View Post
            </a>
        @endif
        <a href="{{ route('admin.blog.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700">
            ← Back to Posts
        </a>
    </div>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.blog.update', $post->id) }}" class="p-6 space-y-6">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="title" 
                           id="title"
                           value="{{ old('title', $post->title) }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-faxzen-purple focus:border-faxzen-purple">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Current slug: <code>{{ $post->slug }}</code></p>
                </div>

                <!-- Excerpt -->
                <div>
                    <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-1">
                        Excerpt <span class="text-red-500">*</span>
                    </label>
                    <textarea name="excerpt" 
                              id="excerpt"
                              rows="3"
                              required
                              placeholder="Brief description of the post (max 500 characters)"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-faxzen-purple focus:border-faxzen-purple">{{ old('excerpt', $post->excerpt) }}</textarea>
                    @error('excerpt')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">This will appear in search results and post previews.</p>
                </div>

                <!-- Content -->
                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-1">
                        Content <span class="text-red-500">*</span>
                    </label>
                    <textarea name="content" 
                              id="content"
                              rows="20"
                              required
                              placeholder="Write your blog post content here. You can use HTML tags for formatting."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-faxzen-purple focus:border-faxzen-purple content-editor">{{ old('content', $post->content) }}</textarea>
                    @error('content')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <div class="mt-2 text-sm text-gray-500">
                        <p class="font-medium">Formatting Tips:</p>
                        <ul class="mt-1 list-disc list-inside space-y-1">
                            <li>Use <code>&lt;h2&gt;</code> for section headings (will auto-generate table of contents)</li>
                            <li>Use <code>&lt;p&gt;</code> for paragraphs</li>
                            <li>Use <code>&lt;ul&gt;</code> and <code>&lt;li&gt;</code> for bullet points</li>
                            <li>Use <code>&lt;table&gt;</code> for comparison tables</li>
                            <li>CTA button will be automatically inserted after first paragraph</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Post Info -->
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Post Information</h3>
                    <div class="space-y-2 text-sm">
                        <div><strong>Created:</strong> {{ $post->created_at->format('M j, Y g:i A') }}</div>
                        <div><strong>Updated:</strong> {{ $post->updated_at->format('M j, Y g:i A') }}</div>
                        <div><strong>Reading Time:</strong> {{ $post->read_time_minutes }} minutes</div>
                        <div><strong>Status:</strong> 
                            @if($post->isPublished())
                                <span class="text-green-600">Published</span>
                            @elseif($post->published_at && $post->published_at > now())
                                <span class="text-blue-600">Scheduled</span>
                            @else
                                <span class="text-gray-600">Draft</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Publishing Options -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Publishing</h3>
                    
                    <!-- Publish Date -->
                    <div class="mb-4">
                        <label for="published_at" class="block text-sm font-medium text-gray-700 mb-1">
                            Publish Date & Time
                        </label>
                        <input type="datetime-local" 
                               name="published_at" 
                               id="published_at"
                               value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-faxzen-purple focus:border-faxzen-purple">
                        <p class="mt-1 text-sm text-gray-500">Leave blank to save as draft</p>
                    </div>

                    <!-- Featured -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="is_featured" 
                                   value="1"
                                   {{ old('is_featured', $post->is_featured) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-faxzen-purple focus:ring-faxzen-purple">
                            <span class="ml-2 text-sm text-gray-700">Featured Post</span>
                        </label>
                        <p class="mt-1 text-sm text-gray-500">Featured posts get special highlighting</p>
                    </div>
                </div>

                <!-- Author -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Author</h3>
                    <div>
                        <label for="author_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Author Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="author_name" 
                               id="author_name"
                               value="{{ old('author_name', $post->author_name) }}"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-faxzen-purple focus:border-faxzen-purple">
                    </div>
                </div>

                <!-- Featured Image -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Featured Image</h3>
                    <div>
                        <label for="featured_image" class="block text-sm font-medium text-gray-700 mb-1">
                            Image URL
                        </label>
                        <input type="url" 
                               name="featured_image" 
                               id="featured_image"
                               value="{{ old('featured_image', $post->featured_image) }}"
                               placeholder="https://example.com/image.jpg"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-faxzen-purple focus:border-faxzen-purple">
                        <p class="mt-1 text-sm text-gray-500">Recommended: High-quality image, 1200x630px</p>
                        @if($post->featured_image)
                            <div class="mt-2">
                                <img src="{{ $post->featured_image }}" alt="Featured image preview" class="w-full h-32 object-cover rounded border">
                            </div>
                        @endif
                    </div>
                </div>

                <!-- SEO Settings -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">SEO Settings</h3>
                    
                    <!-- Meta Title -->
                    <div class="mb-4">
                        <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-1">
                            Meta Title
                        </label>
                        <input type="text" 
                               name="meta_title" 
                               id="meta_title"
                               value="{{ old('meta_title', $post->meta_title) }}"
                               maxlength="255"
                               placeholder="Leave blank to use post title"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-faxzen-purple focus:border-faxzen-purple">
                        <p class="mt-1 text-sm text-gray-500">Recommended: 50-60 characters</p>
                    </div>

                    <!-- Meta Description -->
                    <div class="mb-4">
                        <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-1">
                            Meta Description
                        </label>
                        <textarea name="meta_description" 
                                  id="meta_description"
                                  rows="3"
                                  maxlength="255"
                                  placeholder="Leave blank to use excerpt"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-faxzen-purple focus:border-faxzen-purple">{{ old('meta_description', $post->meta_description) }}</textarea>
                        <p class="mt-1 text-sm text-gray-500">Recommended: 150-160 characters</p>
                    </div>

                    <!-- Meta Keywords -->
                    <div>
                        <label for="meta_keywords" class="block text-sm font-medium text-gray-700 mb-1">
                            Meta Keywords
                        </label>
                        <input type="text" 
                               name="meta_keywords" 
                               id="meta_keywords"
                               value="{{ old('meta_keywords', is_array($post->meta_keywords) ? implode(', ', $post->meta_keywords) : $post->meta_keywords) }}"
                               placeholder="fax, online fax, send fax"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-faxzen-purple focus:border-faxzen-purple">
                        <p class="mt-1 text-sm text-gray-500">Comma-separated keywords</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-between pt-6 border-t border-gray-200">
            <div class="flex space-x-3">
                <a href="{{ route('admin.blog.index') }}" 
                   class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    Cancel
                </a>
                
                <!-- Duplicate Button -->
                <button type="button" 
                        onclick="duplicatePost()"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Duplicate
                </button>
            </div>
            <div class="space-x-3">
                <button type="submit" 
                        class="px-6 py-2 bg-faxzen-purple text-white rounded-md hover:bg-faxzen-purple-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-faxzen-purple">
                    Update Post
                </button>
            </div>
        </div>
    </form>

    <!-- Hidden duplicate form -->
    <form id="duplicate-form" method="POST" action="{{ route('admin.blog.duplicate', $post->id) }}" style="display: none;">
        @csrf
    </form>
@endsection

@push('scripts')
<script>
    // Character counters
    function updateCharCount(inputId, countId, maxLength) {
        const input = document.getElementById(inputId);
        const counter = document.getElementById(countId);
        if (input && counter) {
            const remaining = maxLength - input.value.length;
            counter.textContent = `${remaining} characters remaining`;
            counter.className = remaining < 0 ? 'text-red-500 text-sm' : 'text-gray-500 text-sm';
        }
    }

    // Duplicate post function
    function duplicatePost() {
        if (confirm('This will create a copy of this post as a draft. Continue?')) {
            document.getElementById('duplicate-form').submit();
        }
    }

    // Add character counters
    document.addEventListener('DOMContentLoaded', function() {
        // Meta title counter
        const metaTitle = document.getElementById('meta_title');
        if (metaTitle) {
            const titleCounter = document.createElement('p');
            titleCounter.id = 'meta_title_count';
            titleCounter.className = 'mt-1 text-sm text-gray-500';
            metaTitle.parentNode.insertBefore(titleCounter, metaTitle.nextSibling);
            
            metaTitle.addEventListener('input', () => updateCharCount('meta_title', 'meta_title_count', 60));
            updateCharCount('meta_title', 'meta_title_count', 60);
        }

        // Meta description counter
        const metaDesc = document.getElementById('meta_description');
        if (metaDesc) {
            const descCounter = document.createElement('p');
            descCounter.id = 'meta_desc_count';
            descCounter.className = 'mt-1 text-sm text-gray-500';
            metaDesc.parentNode.insertBefore(descCounter, metaDesc.nextSibling.nextSibling);
            
            metaDesc.addEventListener('input', () => updateCharCount('meta_description', 'meta_desc_count', 160));
            updateCharCount('meta_description', 'meta_desc_count', 160);
        }

        // Excerpt counter
        const excerpt = document.getElementById('excerpt');
        if (excerpt) {
            const excerptCounter = document.createElement('p');
            excerptCounter.id = 'excerpt_count';
            excerptCounter.className = 'mt-1 text-sm text-gray-500';
            excerpt.parentNode.insertBefore(excerptCounter, excerpt.nextSibling);
            
            excerpt.addEventListener('input', () => updateCharCount('excerpt', 'excerpt_count', 500));
            updateCharCount('excerpt', 'excerpt_count', 500);
        }
    });
</script>
@endpush 