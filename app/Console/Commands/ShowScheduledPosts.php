<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class ShowScheduledPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blog:scheduled {--future : Show only future posts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show scheduled blog posts and their publication status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📅 Blog Post Schedule');
        $this->info('=====================');

        // Get posts based on the --future flag
        $query = Post::orderBy('published_at', 'asc');
        
        if ($this->option('future')) {
            $query->where('published_at', '>', now());
            $this->info('Showing only future posts:');
        } else {
            $this->info('Showing all posts:');
        }

        $posts = $query->get();

        if ($posts->isEmpty()) {
            $this->warn('No posts found.');
            return;
        }

        // Create table data
        $tableData = [];
        foreach ($posts as $post) {
            $status = $post->isPublished() ? '✅ Published' : '⏰ Scheduled';
            $publishDate = $post->published_at->format('Y-m-d H:i');
            
            if ($post->published_at->isFuture()) {
                $daysUntil = now()->diffInDays($post->published_at, false);
                $status .= " (in {$daysUntil} days)";
            } elseif ($post->published_at->isToday()) {
                $status .= ' (today)';
            }

            $tableData[] = [
                'ID' => $post->id,
                'Title' => substr($post->title, 0, 50) . (strlen($post->title) > 50 ? '...' : ''),
                'Slug' => $post->slug,
                'Publish Date' => $publishDate,
                'Status' => $status,
            ];
        }

        $this->table(
            ['ID', 'Title', 'Slug', 'Publish Date', 'Status'],
            $tableData
        );

        // Summary
        $published = $posts->filter(fn($post) => $post->isPublished())->count();
        $scheduled = $posts->count() - $published;

        $this->info("\n📊 Summary:");
        $this->info("Published: {$published}");
        $this->info("Scheduled: {$scheduled}");
        $this->info("Total: {$posts->count()}");

        // Show sitemap info
        $this->info("\n🔗 Sitemap Info:");
        $this->info("Only published posts appear in sitemap.xml");
        $this->info("Current sitemap URL count: " . ($published + 4) . " (4 static pages + {$published} blog posts)");
    }
}
