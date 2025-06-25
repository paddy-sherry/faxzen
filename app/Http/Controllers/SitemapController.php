<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate and return the sitemap.xml
     */
    public function index()
    {
        // Get all published blog posts with only needed fields for performance
        $posts = Post::published()
            ->select(['slug', 'updated_at', 'published_at'])
            ->orderBy('published_at', 'desc')
            ->get();

        // Static pages with their priorities and change frequencies
        $staticPages = [
            [
                'url' => '/',
                'lastmod' => '2025-06-25',
                'changefreq' => 'weekly',
                'priority' => '1.0'
            ],
            [
                'url' => '/blog',
                'lastmod' => $posts->count() > 0 ? $posts->first()->updated_at->format('Y-m-d') : '2025-06-25',
                'changefreq' => 'daily',
                'priority' => '0.8'
            ],
            [
                'url' => '/terms',
                'lastmod' => '2025-06-25',
                'changefreq' => 'monthly',
                'priority' => '0.3'
            ],
            [
                'url' => '/contact',
                'lastmod' => '2025-06-25',
                'changefreq' => 'monthly',
                'priority' => '0.5'
            ]
        ];

        // Generate XML content
        $xml = $this->generateSitemapXml($staticPages, $posts);

        return response($xml, 200)
            ->header('Content-Type', 'application/xml')
            ->header('Cache-Control', 'public, max-age=3600'); // Cache for 1 hour
    }

    /**
     * Generate the sitemap XML content
     */
    private function generateSitemapXml($staticPages, $posts)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
        $xml .= '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9' . "\n";
        $xml .= '        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n\n";

        // Add static pages
        foreach ($staticPages as $page) {
            $xml .= "    <url>\n";
            $xml .= "        <loc>https://faxzen.com{$page['url']}</loc>\n";
            $xml .= "        <lastmod>{$page['lastmod']}</lastmod>\n";
            $xml .= "        <changefreq>{$page['changefreq']}</changefreq>\n";
            $xml .= "        <priority>{$page['priority']}</priority>\n";
            $xml .= "    </url>\n\n";
        }

        // Add blog posts
        foreach ($posts as $post) {
            $xml .= "    <url>\n";
            $xml .= "        <loc>https://faxzen.com/blog/{$post->slug}</loc>\n";
            $xml .= "        <lastmod>{$post->updated_at->format('Y-m-d')}</lastmod>\n";
            $xml .= "        <changefreq>monthly</changefreq>\n";
            $xml .= "        <priority>0.7</priority>\n";
            $xml .= "    </url>\n\n";
        }

        $xml .= "</urlset>\n";

        return $xml;
    }
}
