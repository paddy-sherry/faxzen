<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Display the blog index page
     */
    public function index(Request $request)
    {
        $query = Post::published()->orderBy('published_at', 'desc');
        
        // Search functionality
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('excerpt', 'like', "%{$searchTerm}%")
                  ->orWhere('content', 'like', "%{$searchTerm}%");
            });
        }
        
        $posts = $query->paginate(12);
        
        return view('blog.index', compact('posts'));
    }

    /**
     * Display a specific blog post
     */
    public function show(Post $post)
    {
        // Check if post is published
        if (!$post->isPublished()) {
            abort(404);
        }
        
        // Get related posts (same category or recent posts)
        $relatedPosts = Post::published()
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->take(3)
            ->get();
        
        return view('blog.show', compact('post', 'relatedPosts'));
    }
}
