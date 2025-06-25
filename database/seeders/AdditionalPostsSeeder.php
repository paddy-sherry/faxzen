<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use Carbon\Carbon;

class AdditionalPostsSeeder extends Seeder
{
    public function run()
    {
        $baseDate = Carbon::create(2025, 6, 26);
        
        $articles = [
            // Week 1 - June 26, 2025
            [
                'title' => 'How to Fax Without a Fax Machine: Complete 2025 Guide',
                'slug' => 'how-to-fax-without-fax-machine-2025-guide',
                'excerpt' => 'Learn how to send faxes without owning a physical fax machine. Discover online fax services, email-to-fax solutions, and mobile apps that make faxing simple and affordable.',
                'meta_title' => 'How to Fax Without a Fax Machine - Online Fax Solutions 2025',
                'meta_description' => 'Send faxes without a fax machine using online services, email, or mobile apps. Complete guide to modern fax solutions for home and business use.',
                'meta_keywords' => 'fax without fax machine, online fax service, email to fax, mobile fax app',
                'featured_image' => 'https://images.unsplash.com/photo-1551434678-e076c223a692?w=800&h=400&fit=crop&crop=center',
                'author_name' => 'Sarah Chen',
                'content' => '<p>Gone are the days when sending a fax required owning a bulky fax machine. Today\'s digital world offers numerous convenient alternatives that allow you to send faxes from your computer, smartphone, or email without any physical hardware.</p><h2 id="online-fax-services">Online Fax Services: The Modern Solution</h2><p>Online fax services have revolutionized document transmission by eliminating the need for traditional fax machines. Popular services include FaxZen, which allows you to send a fax for just $3 with no monthly commitments. The process is straightforward: upload your PDF document, enter the recipient\'s fax number, and click send.</p><h2 id="email-to-fax-solutions">Email-to-Fax Solutions</h2><p>Many online fax services offer email-to-fax functionality, allowing you to send faxes directly from your email client. Simply attach your document to an email and send it to a special email address format. This method integrates seamlessly with your existing email workflow.</p><h2 id="mobile-fax-apps">Mobile Fax Apps</h2><p>Smartphone apps have made faxing even more accessible. Apps like CamScanner allow you to photograph documents with your phone\'s camera and send them as faxes immediately. These apps often include document enhancement features that improve image quality automatically.</p><h2 id="choosing-best-method">Choosing the Best Method</h2><p>Consider these factors when selecting a fax method: frequency of use, document types, security requirements, and delivery confirmation needs. For businesses sending fewer than 10 faxes monthly, pay-per-use services like FaxZen offer the most economical solution.</p>',
                'read_time_minutes' => 4,
                'is_featured' => false,
                'published_at' => $baseDate->copy(),
            ],
        ];

        // Add remaining 19 articles
        $this->addAllArticles($articles, $baseDate);

        foreach ($articles as $articleData) {
            Post::create($articleData);
        }
    }

    private function addAllArticles(&$articles, $baseDate)
    {
        $additionalArticles = [
            [
                'title' => 'How to Fax from iPhone: Complete Step-by-Step Guide',
                'slug' => 'how-to-fax-from-iphone-complete-guide',
                'excerpt' => 'Transform your iPhone into a powerful fax machine. Learn the best apps, methods, and tips for sending professional faxes directly from your iOS device.',
                'keywords' => 'fax from iPhone, iPhone fax app, iOS fax',
                'week' => 1,
            ],
            [
                'title' => 'How to Send a Fax via Email: The Ultimate Guide',
                'slug' => 'how-to-send-fax-via-email-ultimate-guide',
                'excerpt' => 'Discover how to send faxes directly from your email inbox. Learn about email-to-fax services, setup instructions, and tips for successful transmission.',
                'keywords' => 'send fax via email, email to fax, fax by email',
                'week' => 2,
            ],
            [
                'title' => 'Windows Fax and Scan: Complete Setup and Usage Guide',
                'slug' => 'windows-fax-and-scan-complete-guide',
                'excerpt' => 'Master Windows built-in fax capabilities with Fax and Scan. Learn setup, configuration, and advanced features for sending faxes from your PC.',
                'keywords' => 'Windows Fax and Scan, Windows fax, PC fax',
                'week' => 3,
            ],
            [
                'title' => 'How to Send Fax from Computer: 5 Easy Methods',
                'slug' => 'how-to-send-fax-from-computer-easy-methods',
                'excerpt' => 'Learn multiple ways to send faxes directly from your computer. Compare online services, software solutions, and built-in options for PC and Mac.',
                'keywords' => 'send fax from computer, computer fax, PC fax',
                'week' => 4,
            ],
            [
                'title' => 'Can You Fax from a Computer? Complete Guide',
                'slug' => 'can-you-fax-from-computer-complete-guide',
                'excerpt' => 'Yes, you can fax from any computer! Discover all the methods available for sending faxes from Windows, Mac, and Linux systems.',
                'keywords' => 'fax from computer, computer faxing, digital fax',
                'week' => 5,
            ],
            [
                'title' => 'How to Fax from Email: Step-by-Step Instructions',
                'slug' => 'how-to-fax-from-email-step-by-step',
                'excerpt' => 'Transform your email into a fax machine. Learn how to send faxes from Gmail, Outlook, and other email clients using email-to-fax services.',
                'keywords' => 'fax from email, email fax, email to fax',
                'week' => 6,
            ],
            [
                'title' => 'Fax from PC: Best Software and Online Solutions',
                'slug' => 'fax-from-pc-best-software-solutions',
                'excerpt' => 'Discover the best ways to send faxes from your PC. Compare free and paid software options, online services, and built-in Windows solutions.',
                'keywords' => 'fax from PC, PC fax software, Windows fax',
                'week' => 7,
            ],
            [
                'title' => 'How to Fax a PDF: Complete Guide for All Devices',
                'slug' => 'how-to-fax-pdf-complete-guide',
                'excerpt' => 'Learn how to fax PDF documents from any device. Step-by-step instructions for computers, smartphones, and online services.',
                'keywords' => 'fax PDF, PDF fax, send PDF fax',
                'week' => 8,
            ],
            [
                'title' => 'Fax from Phone: Mobile Faxing Made Simple',
                'slug' => 'fax-from-phone-mobile-faxing-simple',
                'excerpt' => 'Turn your smartphone into a portable fax machine. Learn the best apps and methods for sending faxes from iPhone and Android devices.',
                'keywords' => 'fax from phone, mobile fax, smartphone fax',
                'week' => 9,
            ],
            [
                'title' => 'Fax Without Phone Line: Modern Solutions Explained',
                'slug' => 'fax-without-phone-line-modern-solutions',
                'excerpt' => 'Discover how to send faxes without a traditional phone line. Explore internet fax services, VoIP solutions, and mobile alternatives.',
                'keywords' => 'fax without phone line, internet fax, VoIP fax',
                'week' => 10,
            ],
            [
                'title' => 'UPS Fax Near Me: Store Locations and Services',
                'slug' => 'ups-fax-near-me-store-locations-services',
                'excerpt' => 'Find UPS stores near you that offer fax services. Learn about pricing, hours, and how to use UPS fax services for your business needs.',
                'keywords' => 'UPS fax near me, UPS fax service, UPS store fax',
                'week' => 11,
            ],
            [
                'title' => 'Can You Fax Without a Phone Line? Yes, Here\'s How',
                'slug' => 'can-you-fax-without-phone-line-yes-how',
                'excerpt' => 'Yes, you can fax without a phone line! Learn about internet-based fax services, mobile apps, and other modern alternatives to traditional faxing.',
                'keywords' => 'fax without phone line, internet fax, online fax',
                'week' => 12,
            ],
            [
                'title' => 'Can You Fax from Gmail? Complete Setup Guide',
                'slug' => 'can-you-fax-from-gmail-setup-guide',
                'excerpt' => 'Learn how to send faxes directly from your Gmail account. Step-by-step instructions for using email-to-fax services with Google\'s email platform.',
                'keywords' => 'fax from Gmail, Gmail fax, Google fax',
                'week' => 13,
            ],
            [
                'title' => 'Fax with Gmail: Easy Email-to-Fax Setup',
                'slug' => 'fax-with-gmail-email-to-fax-setup',
                'excerpt' => 'Transform Gmail into your fax solution. Detailed guide on setting up and using Gmail for sending faxes to any fax number worldwide.',
                'keywords' => 'fax with Gmail, Gmail to fax, email fax Gmail',
                'week' => 14,
            ],
            [
                'title' => 'Can I Fax from My iPhone? Yes, Here Are 5 Ways',
                'slug' => 'can-i-fax-from-my-iphone-five-ways',
                'excerpt' => 'Discover five different methods to send faxes from your iPhone. Compare apps, online services, and email solutions for iOS faxing.',
                'keywords' => 'fax from iPhone, iPhone fax, iOS fax app',
                'week' => 15,
            ],
            [
                'title' => 'Can You Email to a Fax Number? Complete Guide',
                'slug' => 'can-you-email-to-fax-number-complete-guide',
                'excerpt' => 'Yes, you can email to a fax number! Learn how email-to-fax services work and how to send documents from any email client to fax machines.',
                'keywords' => 'email to fax number, email to fax, send email to fax',
                'week' => 16,
            ],
            [
                'title' => 'Fax from Mac: Best Methods for macOS Users',
                'slug' => 'fax-from-mac-best-methods-macos',
                'excerpt' => 'Learn how to send faxes from your Mac computer. Explore built-in options, third-party software, and online services for macOS faxing.',
                'keywords' => 'fax from Mac, Mac fax, macOS fax software',
                'week' => 17,
            ],
            [
                'title' => 'Fax from Outlook: Microsoft Email Fax Integration',
                'slug' => 'fax-from-outlook-microsoft-email-integration',
                'excerpt' => 'Send faxes directly from Microsoft Outlook. Learn about add-ins, email-to-fax services, and built-in fax features for Outlook users.',
                'keywords' => 'fax from Outlook, Outlook fax, Microsoft fax',
                'week' => 18,
            ],
            [
                'title' => 'Gmail to Fax: Send Faxes from Google Email',
                'slug' => 'gmail-to-fax-send-faxes-google-email',
                'excerpt' => 'Master Gmail-to-fax transmission with our comprehensive guide. Learn setup, troubleshooting, and best practices for Google email faxing.',
                'keywords' => 'Gmail to fax, Google email fax, Gmail fax service',
                'week' => 19,
            ],
        ];

        foreach ($additionalArticles as $index => $article) {
            $fullArticle = [
                'title' => $article['title'],
                'slug' => $article['slug'],
                'excerpt' => $article['excerpt'],
                'meta_title' => $article['title'] . ' | FaxZen',
                'meta_description' => $article['excerpt'],
                'meta_keywords' => $article['keywords'],
                'featured_image' => 'https://images.unsplash.com/photo-' . (1551434678 + $index) . '-e076c223a692?w=800&h=400&fit=crop&crop=center',
                'author_name' => ['Sarah Chen', 'Michael Rodriguez', 'Jennifer Walsh', 'Robert Kim', 'Lisa Thompson'][array_rand(['Sarah Chen', 'Michael Rodriguez', 'Jennifer Walsh', 'Robert Kim', 'Lisa Thompson'])],
                'content' => $this->generateContent($article['title'], $article['keywords']),
                'read_time_minutes' => rand(3, 5),
                'is_featured' => false,
                'published_at' => $baseDate->copy()->addWeeks($article['week']),
            ];
            
            $articles[] = $fullArticle;
        }
    }

    private function generateContent($title, $keywords)
    {
        return '<p>This comprehensive guide covers everything you need to know about ' . strtolower(str_replace(['How to ', 'Can You ', 'Can I '], '', $title)) . '. Modern fax solutions have evolved significantly, offering convenient alternatives to traditional fax machines.</p><h2 id="getting-started">Getting Started</h2><p>The process is straightforward and can be completed in just a few minutes. Whether you\'re sending business documents, contracts, or personal papers, these methods provide reliable transmission with delivery confirmation.</p><h2 id="step-by-step-guide">Step-by-Step Instructions</h2><p>Follow these detailed steps to successfully send your fax. Most services offer user-friendly interfaces that make the process intuitive even for first-time users. FaxZen provides an excellent solution at just $3 per fax.</p><h2 id="best-practices">Best Practices and Tips</h2><p>To ensure successful transmission, follow these proven strategies. Proper document formatting and recipient number verification significantly improve delivery rates. Always use high-quality scans or PDF files for best results.</p><h2 id="troubleshooting">Common Issues and Solutions</h2><p>If you encounter problems, these troubleshooting tips will help resolve most common issues quickly. Check your internet connection, verify the recipient\'s fax number, and ensure your document is in a supported format.</p><h2 id="cost-comparison">Cost and Value Analysis</h2><p>Compare different options to find the most cost-effective solution for your needs. Online services like FaxZen offer competitive pricing with no monthly commitments, making them ideal for occasional users who want reliable fax transmission.</p>';
    }
}
