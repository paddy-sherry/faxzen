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
        'updated_at',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'meta_keywords' => 'array',
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
     * Get formatted published date with "Updated:" prefix for article pages
     */
    public function getFormattedPublishedDateWithUpdatedAttribute()
    {
        return $this->updated_at ? 'Updated: ' . $this->updated_at->format('M j, Y') : null;
    }

    /**
     * Get reading time text
     */
    public function getReadingTimeAttribute()
    {
        return $this->read_time_minutes . ' min read';
    }

    /**
     * Get content with CTA button inserted, H3 converted to H2, and table of contents added
     */
    public function getContentWithCtaAttribute()
    {
        $content = $this->content;
        
        // First, convert H3 tags to H2 and add IDs
        $content = $this->convertH3ToH2WithIds($content);
        
        // Generate table of contents
        $tableOfContents = $this->generateTableOfContents($content);
        
        // Add table of contents at the beginning if headings exist
        if (!empty($tableOfContents)) {
            $tocHtml = '<div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #6366f1; margin-bottom: 30px;">
                <h3 style="margin-top: 0; margin-bottom: 15px; color: #374151; font-size: 1.125rem; font-weight: 600;">Table of Contents</h3>
                <ul style="margin: 0; padding-left: 20px; list-style-type: none;">
                    ' . implode('', array_map(function($item) {
                        return '<li style="margin-bottom: 8px;"><a href="#' . $item['id'] . '" style="color: #6366f1; text-decoration: none; font-weight: 500; transition: color 0.2s;" onmouseover="this.style.color=\'#4f46e5\'" onmouseout="this.style.color=\'#6366f1\'">' . $item['title'] . '</a></li>';
                    }, $tableOfContents)) . '
                </ul>
            </div>';
            
            $content = $tocHtml . $content;
        }
        
        // Find the first </p> tag and the first <h2> tag (now that we converted h3 to h2)
        $firstParagraphEnd = strpos($content, '</p>');
        $firstH2Start = strpos($content, '<h2');
        
        // Only insert CTA if both tags exist and paragraph comes before h2
        if ($firstParagraphEnd !== false && $firstH2Start !== false && $firstParagraphEnd < $firstH2Start) {
            $ctaButton = '<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 25px; margin: 25px 0; text-align: center; color: white; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
  <h3 style="color: white; margin-bottom: 15px; font-size: 24px;">Ready To Fax?</h3>
  <p style="margin-bottom: 20px; font-size: 18px; opacity: 0.9;">Start sending faxes online in seconds with FaxZen - No account required</p>
  <a href="/send-fax" style="background: #ff6b6b; color: white; padding: 15px 35px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 18px; display: inline-block; transition: all 0.3s ease; box-shadow: 0 5px 15px rgba(255,107,107,0.4);">Send Fax Now 🚀</a>
</div>';
            
            // Insert CTA after the first paragraph
            $insertPosition = $firstParagraphEnd + 4; // After </p>
            $content = substr_replace($content, $ctaButton, $insertPosition, 0);
        }
        
        return $content;
    }

    /**
     * Convert H3 tags to H2 tags with IDs for jump links
     */
    private function convertH3ToH2WithIds($content)
    {
        // Pattern to match H3 tags and capture the content
        $pattern = '/<h3([^>]*)>(.*?)<\/h3>/i';
        
        return preg_replace_callback($pattern, function($matches) {
            $attributes = $matches[1];
            $headingText = $matches[2];
            
            // Generate ID from heading text
            $id = $this->generateHeadingId($headingText);
            
            // Check if ID already exists in attributes
            if (strpos($attributes, 'id=') === false) {
                $attributes .= ' id="' . $id . '"';
            }
            
            return '<h2' . $attributes . '>' . $headingText . '</h2>';
        }, $content);
    }

    /**
     * Generate table of contents from H2 headings
     */
    private function generateTableOfContents($content)
    {
        $tableOfContents = [];
        
        // Pattern to match H2 tags with IDs
        $pattern = '/<h2[^>]*id=["\']([^"\']*)["\'][^>]*>(.*?)<\/h2>/i';
        
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $id = $match[1];
            $title = strip_tags($match[2]);
            
            $tableOfContents[] = [
                'id' => $id,
                'title' => $title
            ];
        }
        
        return $tableOfContents;
    }

    /**
     * Generate a URL-friendly ID from heading text
     */
    private function generateHeadingId($headingText)
    {
        // Strip HTML tags and convert to lowercase
        $text = strip_tags($headingText);
        
        // Remove special characters and convert spaces to hyphens
        $id = preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
        $id = preg_replace('/\s+/', '-', trim($id));
        $id = strtolower($id);
        
        // Remove multiple consecutive hyphens
        $id = preg_replace('/-+/', '-', $id);
        
        // Remove leading/trailing hyphens
        $id = trim($id, '-');
        
        return $id ?: 'heading-' . uniqid();
    }

    /**
     * Mutator for meta_keywords - convert comma-separated string to array
     */
    public function setMetaKeywordsAttribute($value)
    {
        if (is_string($value) && !empty($value)) {
            // Convert comma-separated string to array
            $keywords = array_map('trim', explode(',', $value));
            // Remove empty values
            $keywords = array_filter($keywords, function($keyword) {
                return !empty($keyword);
            });
            $this->attributes['meta_keywords'] = json_encode(array_values($keywords));
        } elseif (is_array($value)) {
            // If it's already an array, just encode it
            $this->attributes['meta_keywords'] = json_encode($value);
        } else {
            // If empty or null, set to null
            $this->attributes['meta_keywords'] = null;
        }
    }
}
