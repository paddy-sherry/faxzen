<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'featured_image',
        'author_name',
        'is_featured',
        'read_time_minutes',
        'published_at',
    ];

    protected $casts = [
        'meta_keywords' => 'array',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
            
            if (empty($post->meta_title)) {
                $post->meta_title = $post->title;
            }
            
            if (empty($post->meta_description)) {
                $post->meta_description = Str::limit(strip_tags($post->excerpt), 155);
            }
            
            if (empty($post->read_time_minutes)) {
                $post->read_time_minutes = $post->calculateReadTime();
            }
        });

        static::updating(function ($post) {
            if ($post->isDirty('title') && empty($post->getOriginal('slug'))) {
                $post->slug = Str::slug($post->title);
            }
            
            if ($post->isDirty('content') || $post->isDirty('excerpt')) {
                $post->read_time_minutes = $post->calculateReadTime();
            }
        });
    }

    /**
     * Get route key name for model binding
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Scope to get only published posts
     */
    public function scopePublished($query)
    {
        return $query->where('published_at', '<=', now())
                    ->whereNotNull('published_at');
    }

    /**
     * Scope to get featured posts
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Check if post is published
     */
    public function isPublished()
    {
        return $this->published_at && $this->published_at <= now();
    }

    /**
     * Get the post's URL
     */
    public function getUrlAttribute()
    {
        return route('blog.show', $this->slug);
    }

    /**
     * Calculate estimated read time
     */
    public function calculateReadTime()
    {
        $wordCount = str_word_count(strip_tags($this->content . ' ' . $this->excerpt));
        $minutes = ceil($wordCount / 200); // Average reading speed: 200 words per minute
        return max(1, $minutes);
    }

    /**
     * Get formatted published date
     */
    public function getFormattedPublishedDateAttribute()
    {
        return $this->published_at ? $this->published_at->format('M j, Y') : null;
    }

    /**
     * Get reading time text
     */
    public function getReadingTimeAttribute()
    {
        return $this->read_time_minutes . ' min read';
    }

    /**
     * Get content with CTA button inserted
     */
    public function getContentWithCtaAttribute()
    {
        $content = $this->content;
        
        // Find the first </p> tag and the first <h3> tag
        $firstParagraphEnd = strpos($content, '</p>');
        $firstH3Start = strpos($content, '<h3');
        
        // Only insert CTA if both tags exist and paragraph comes before h3
        if ($firstParagraphEnd !== false && $firstH3Start !== false && $firstParagraphEnd < $firstH3Start) {
            $ctaButton = '
<div style="text-align: center; margin: 2rem 0; padding: 1.5rem; background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); border-radius: 12px; border: 1px solid #e5e7eb;">
    <a href="' . url('/') . '" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-bold rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl text-lg">
        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
        </svg>
        Send Fax Now
    </a>
    <p style="margin-top: 0.75rem; color: #6b7280; font-size: 0.875rem;">Start sending faxes online in seconds - No account required</p>
</div>';
            
            // Insert CTA after the first paragraph
            $insertPosition = $firstParagraphEnd + 4; // After </p>
            $content = substr_replace($content, $ctaButton, $insertPosition, 0);
        }
        
        return $content;
    }
}
