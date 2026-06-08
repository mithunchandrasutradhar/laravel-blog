<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Roles & permissions (must run before any user is created)
        $this->call(RoleSeeder::class);

        // 2. Site settings
        $this->call(SettingsSeeder::class);

        // 3. Users
        $this->seedUsers();

        // 4. Categories
        $this->seedCategories();

        // 5. Tags
        $this->seedTags();

        // 6. Sample posts (only in local / testing environments)
        if (app()->environment(['local', 'testing'])) {
            $this->seedSamplePosts();
        }

        $this->command->info('Database seeded successfully.');
    }

    // -------------------------------------------------------------------------
    // Users
    // -------------------------------------------------------------------------

    private function seedUsers(): void
    {
        // Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@myblog.com'],
            [
                'name'              => 'Admin User',
                'password'          => Hash::make('password'),
                'bio'               => 'Site administrator.',
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        // Demo author
        $author = User::firstOrCreate(
            ['email' => 'author@myblog.com'],
            [
                'name'              => 'Jane Doe',
                'password'          => Hash::make('password'),
                'bio'               => 'Content creator and tech enthusiast.',
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );
        $author->assignRole('author');

        // Demo regular user
        $user = User::firstOrCreate(
            ['email' => 'user@myblog.com'],
            [
                'name'              => 'John Smith',
                'password'          => Hash::make('password'),
                'bio'               => 'Avid reader.',
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );
        $user->assignRole('user');

        $this->command->info('Users seeded: admin@myblog.com / author@myblog.com / user@myblog.com (password: password)');
    }

    // -------------------------------------------------------------------------
    // Categories
    // -------------------------------------------------------------------------

    private function seedCategories(): void
    {
        $topLevel = [
            [
                'name'             => 'Technology',
                'description'      => 'Articles about software, hardware, and emerging tech.',
                'meta_title'       => 'Technology – My Blog',
                'meta_description' => 'Read the latest technology articles on My Blog.',
                'children'         => [
                    ['name' => 'Web Development',  'description' => 'Frontend, backend, and full-stack development.'],
                    ['name' => 'Mobile Apps',       'description' => 'iOS, Android, and cross-platform development.'],
                    ['name' => 'Artificial Intelligence', 'description' => 'Machine learning, AI tools, and research.'],
                    ['name' => 'DevOps',            'description' => 'CI/CD, containers, and cloud infrastructure.'],
                ],
            ],
            [
                'name'             => 'Design',
                'description'      => 'UI/UX design, graphic design, and creative inspiration.',
                'meta_title'       => 'Design – My Blog',
                'meta_description' => 'Explore design tips, tutorials, and inspiration.',
                'children'         => [
                    ['name' => 'UI/UX Design',   'description' => 'User interface and user experience design.'],
                    ['name' => 'Typography',      'description' => 'Fonts, type systems, and readability.'],
                    ['name' => 'Color Theory',    'description' => 'Using colour effectively in design.'],
                ],
            ],
            [
                'name'             => 'Business',
                'description'      => 'Entrepreneurship, marketing, productivity, and career advice.',
                'meta_title'       => 'Business – My Blog',
                'meta_description' => 'Business insights, tips, and success stories.',
                'children'         => [
                    ['name' => 'Entrepreneurship', 'description' => 'Starting and growing a business.'],
                    ['name' => 'Marketing',         'description' => 'Digital marketing, SEO, and growth hacking.'],
                    ['name' => 'Productivity',      'description' => 'Tools and habits for getting more done.'],
                ],
            ],
            [
                'name'        => 'Lifestyle',
                'description' => 'Health, travel, food, and personal development.',
                'children'    => [
                    ['name' => 'Travel',            'description' => 'Destinations, tips, and travel stories.'],
                    ['name' => 'Health & Wellness', 'description' => 'Physical and mental health advice.'],
                ],
            ],
            [
                'name'        => 'Tutorials',
                'description' => 'Step-by-step guides and how-tos.',
            ],
            [
                'name'        => 'News',
                'description' => 'Industry news and announcements.',
            ],
        ];

        foreach ($topLevel as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            $parent = Category::firstOrCreate(
                ['slug' => Str::slug($categoryData['name'])],
                $categoryData
            );

            foreach ($children as $childData) {
                Category::firstOrCreate(
                    ['slug' => Str::slug($childData['name'])],
                    array_merge($childData, ['parent_id' => $parent->id])
                );
            }
        }

        $this->command->info('Categories seeded: ' . Category::count() . ' total.');
    }

    // -------------------------------------------------------------------------
    // Tags
    // -------------------------------------------------------------------------

    private function seedTags(): void
    {
        $tags = [
            // Tech
            'Laravel', 'PHP', 'JavaScript', 'TypeScript', 'Vue.js', 'React',
            'Node.js', 'Python', 'Docker', 'Kubernetes', 'AWS', 'Linux',
            'MySQL', 'PostgreSQL', 'Redis', 'GraphQL', 'REST API',
            // Design
            'Figma', 'Tailwind CSS', 'Bootstrap', 'CSS', 'HTML',
            // Business
            'SEO', 'Content Marketing', 'Social Media', 'Analytics',
            // General
            'Tutorial', 'Opinion', 'News', 'Review', 'Open Source',
            'Career', 'Productivity', 'Tools', 'Tips & Tricks',
        ];

        foreach ($tags as $tagName) {
            Tag::firstOrCreate(
                ['slug' => Str::slug($tagName)],
                ['name' => $tagName]
            );
        }

        $this->command->info('Tags seeded: ' . Tag::count() . ' total.');
    }

    // -------------------------------------------------------------------------
    // Sample Posts (local/testing only)
    // -------------------------------------------------------------------------

    private function seedSamplePosts(): void
    {
        $author   = User::whereHas('roles', fn ($q) => $q->where('name', 'author'))->first();
        $webDevCat = Category::where('slug', 'web-development')->first()
                   ?? Category::first();

        if (! $author || ! $webDevCat) {
            return;
        }

        $samplePosts = [
            [
                'title'             => 'Getting Started with Laravel 12',
                'short_description' => 'A comprehensive introduction to the latest features in Laravel 12 and how to build modern web applications.',
                'content'           => $this->loremContent('Laravel 12'),
                'status'            => 'published',
                'published_at'      => now()->subDays(5),
                'tags'              => ['Laravel', 'PHP', 'Tutorial'],
            ],
            [
                'title'             => 'Mastering Tailwind CSS: A Practical Guide',
                'short_description' => 'Learn how to build beautiful, responsive interfaces using Tailwind CSS utility classes.',
                'content'           => $this->loremContent('Tailwind CSS'),
                'status'            => 'published',
                'published_at'      => now()->subDays(3),
                'tags'              => ['Tailwind CSS', 'CSS', 'Tutorial'],
            ],
            [
                'title'             => 'Building RESTful APIs with Laravel Sanctum',
                'short_description' => 'A step-by-step guide to authenticating SPA and mobile clients using Laravel Sanctum.',
                'content'           => $this->loremContent('Laravel Sanctum'),
                'status'            => 'published',
                'published_at'      => now()->subDays(1),
                'tags'              => ['Laravel', 'REST API', 'PHP'],
            ],
            [
                'title'             => 'An Introduction to Docker for PHP Developers',
                'short_description' => 'Containerise your PHP applications and streamline your development workflow with Docker.',
                'content'           => $this->loremContent('Docker'),
                'status'            => 'published',
                'published_at'      => now()->subHours(12),
                'tags'              => ['Docker', 'PHP', 'DevOps'],
            ],
            [
                'title'             => 'Vue.js 3 Composition API Deep Dive',
                'short_description' => 'Everything you need to know about the Composition API introduced in Vue 3.',
                'content'           => $this->loremContent('Vue.js 3'),
                'status'            => 'draft',
                'published_at'      => null,
                'tags'              => ['Vue.js', 'JavaScript'],
            ],
        ];

        foreach ($samplePosts as $postData) {
            $tagNames = $postData['tags'] ?? [];
            unset($postData['tags']);

            $post = Post::firstOrCreate(
                ['slug' => Post::generateUniqueSlug($postData['title'])],
                array_merge($postData, [
                    'user_id'     => $author->id,
                    'category_id' => $webDevCat->id,
                    'meta_title'  => $postData['title'],
                ])
            );

            if (! empty($tagNames)) {
                $tagIds = Tag::whereIn('name', $tagNames)->pluck('id');
                $post->tags()->syncWithoutDetaching($tagIds);
            }
        }

        $this->command->info('Sample posts seeded: ' . Post::count() . ' total.');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Return a multi-paragraph placeholder content body for sample posts.
     */
    private function loremContent(string $subject): string
    {
        return <<<HTML
<h2>Introduction to {$subject}</h2>
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>

<h2>Why {$subject} Matters</h2>
<p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.</p>

<h2>Getting Started</h2>
<p>Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet.</p>

<pre><code>// Example code snippet for {$subject}
\$example = new Example();
\$example->run();
</code></pre>

<h2>Advanced Techniques</h2>
<p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident.</p>

<h2>Conclusion</h2>
<p>Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus.</p>
HTML;
    }
}
