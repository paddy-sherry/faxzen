<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Post::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('excerpt', 'LIKE', "%{$search}%")
                  ->orWhere('author_name', 'LIKE', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'published':
                    $query->published();
                    break;
                case 'scheduled':
                    $query->where('published_at', '>', now());
                    break;
                case 'draft':
                    $query->whereNull('published_at');
                    break;
            }
        }

        // Featured filter
        if ($request->filled('featured')) {
            $query->where('is_featured', $request->featured === 'yes');
        }

        $posts = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.blog.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.blog.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string|max:500',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'featured_image' => 'nullable|url',
            'author_name' => 'required|string|max:100',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        // Auto-generate slug if not provided
        $validated['slug'] = Str::slug($validated['title']);
        
        // Ensure unique slug
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Post::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Auto-generate meta fields if not provided
        if (empty($validated['meta_title'])) {
            $validated['meta_title'] = $validated['title'];
        }
        
        if (empty($validated['meta_description'])) {
            $validated['meta_description'] = Str::limit(strip_tags($validated['excerpt']), 155);
        }

        // Calculate reading time
        $wordCount = str_word_count(strip_tags($validated['content'] . ' ' . $validated['excerpt']));
        $validated['read_time_minutes'] = max(1, ceil($wordCount / 200));

        Post::create($validated);

        return redirect()->route('admin.blog.index')
            ->with('success', 'Blog post created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $post = Post::findOrFail($id);
        return view('admin.blog.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $post = Post::findOrFail($id);
        return view('admin.blog.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string|max:500',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'featured_image' => 'nullable|url',
            'author_name' => 'required|string|max:100',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
            'updated_at' => 'nullable|date',
        ]);

        // Update slug if title changed
        if ($post->title !== $validated['title']) {
            $newSlug = Str::slug($validated['title']);
            
            // Ensure unique slug (excluding current post)
            $originalSlug = $newSlug;
            $counter = 1;
            while (Post::where('slug', $newSlug)->where('id', '!=', $post->id)->exists()) {
                $newSlug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            $validated['slug'] = $newSlug;
        }

        // Auto-generate meta fields if not provided
        if (empty($validated['meta_title'])) {
            $validated['meta_title'] = $validated['title'];
        }
        
        if (empty($validated['meta_description'])) {
            $validated['meta_description'] = Str::limit(strip_tags($validated['excerpt']), 155);
        }

        // Recalculate reading time
        $wordCount = str_word_count(strip_tags($validated['content'] . ' ' . $validated['excerpt']));
        $validated['read_time_minutes'] = max(1, ceil($wordCount / 200));

        // If updated_at is provided, set it explicitly
        if (!empty($validated['updated_at'])) {
            $post->updated_at = Carbon::parse($validated['updated_at']);
        }

        $post->update($validated);

        // If updated_at was set, save it
        if (!empty($validated['updated_at'])) {
            $post->save();
        }

        // Return JSON for AJAX requests
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Blog post updated successfully!']);
        }

        return redirect()->route('admin.blog.index')
            ->with('success', 'Blog post updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return redirect()->route('admin.blog.index')
            ->with('success', 'Blog post deleted successfully!');
    }

    public function publish($id)
    {
        $post = Post::findOrFail($id);
        $post->update(['published_at' => now()]);

        return redirect()->back()
            ->with('success', 'Blog post published successfully!');
    }

    public function unpublish($id)
    {
        $post = Post::findOrFail($id);
        $post->update(['published_at' => null]);

        return redirect()->back()
            ->with('success', 'Blog post unpublished successfully!');
    }

    public function duplicate($id)
    {
        $post = Post::findOrFail($id);
        $newPost = $post->replicate();
        $newPost->title = $post->title . ' (Copy)';
        $newPost->slug = Str::slug($newPost->title);
        $newPost->published_at = null;
        
        // Ensure unique slug
        $originalSlug = $newPost->slug;
        $counter = 1;
        while (Post::where('slug', $newPost->slug)->exists()) {
            $newPost->slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        $newPost->save();

        return redirect()->route('admin.blog.edit', $newPost->id)
            ->with('success', 'Blog post duplicated successfully!');
    }
}
