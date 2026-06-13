<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->first();
        $author = User::whereHas('roles', fn ($q) => $q->where('name', 'author'))->first() ?? $admin;

        $posts = $this->posts();

        foreach ($posts as $catSlug => $catPosts) {
            $category = Category::where('slug', $catSlug)->first();
            if (! $category) {
                continue;
            }

            foreach ($catPosts as $i => $data) {
                $slug = Post::generateUniqueSlug($data['title']);
                $user = $i % 2 === 0 ? $author : $admin;

                $post = Post::firstOrCreate(
                    ['slug' => Str::slug($data['title'])],
                    [
                        'title'             => $data['title'],
                        'slug'              => $slug,
                        'short_description' => $data['excerpt'],
                        'content'           => $this->buildContent($data['title'], $data['excerpt'], $catSlug),
                        'featured_image'    => $data['image'],
                        'category_id'       => $category->id,
                        'user_id'           => $user->id,
                        'status'            => 'published',
                        'published_at'      => now()->subDays(rand(1, 60)),
                        'views_count'       => rand(120, 4800),
                        'reading_time'      => rand(3, 10),
                        'meta_title'        => $data['title'],
                        'meta_description'  => $data['excerpt'],
                    ]
                );

                if (! empty($data['tags'])) {
                    $tagIds = Tag::whereIn('name', $data['tags'])->pluck('id');
                    $post->tags()->syncWithoutDetaching($tagIds);
                }
            }
        }

        $this->command->info('Demo posts seeded: ' . Post::published()->count() . ' published.');
    }

    private function posts(): array
    {
        return [
            'web-development' => [
                [
                    'title'  => 'The Complete Guide to CSS Grid in 2025',
                    'excerpt' => 'CSS Grid has transformed how we build layouts. This comprehensive guide covers everything from basic grid lines to complex multi-area layouts with real-world examples.',
                    'image'  => 'https://picsum.photos/seed/cssgrid/800/450',
                    'tags'   => ['CSS', 'HTML', 'Tutorial'],
                ],
                [
                    'title'  => 'Building a Full-Stack App with Laravel and Vue 3',
                    'excerpt' => 'Combine the power of Laravel\'s backend elegance with Vue 3\'s reactive frontend to build production-ready full-stack applications step by step.',
                    'image'  => 'https://picsum.photos/seed/laravelvue/800/450',
                    'tags'   => ['Laravel', 'Vue.js', 'JavaScript'],
                ],
                [
                    'title'  => 'TypeScript Best Practices Every Developer Should Know',
                    'excerpt' => 'TypeScript is no longer optional for serious projects. Explore the patterns, utilities, and strict configuration options that make your codebase robust and maintainable.',
                    'image'  => 'https://picsum.photos/seed/typescript/800/450',
                    'tags'   => ['TypeScript', 'JavaScript'],
                ],
            ],
            'mobile-apps' => [
                [
                    'title'  => 'React Native vs Flutter: Which Should You Choose in 2025?',
                    'excerpt' => 'Both React Native and Flutter are powerful cross-platform frameworks. We compare performance, ecosystem, developer experience, and real-world use cases to help you decide.',
                    'image'  => 'https://picsum.photos/seed/mobiledv/800/450',
                    'tags'   => ['React', 'Tutorial'],
                ],
                [
                    'title'  => 'Building Offline-First Mobile Apps',
                    'excerpt' => 'Offline-first architecture ensures your app remains functional without internet. Learn sync strategies, conflict resolution, and local storage patterns that delight users.',
                    'image'  => 'https://picsum.photos/seed/offlinefirst/800/450',
                    'tags'   => ['Tips & Tricks'],
                ],
                [
                    'title'  => 'Mastering Mobile App Performance Optimization',
                    'excerpt' => 'Slow apps lose users. Discover proven techniques for reducing render times, optimizing images, managing memory, and profiling bottlenecks in mobile applications.',
                    'image'  => 'https://picsum.photos/seed/mobileperf/800/450',
                    'tags'   => ['Tips & Tricks', 'Tutorial'],
                ],
            ],
            'artificial-intelligence' => [
                [
                    'title'  => 'Understanding Large Language Models: A Developer\'s Primer',
                    'excerpt' => 'LLMs are reshaping software development. This practical guide demystifies transformers, tokenization, embeddings, and how to integrate AI capabilities into your applications.',
                    'image'  => 'https://picsum.photos/seed/llmguide/800/450',
                    'tags'   => ['Tutorial', 'News'],
                ],
                [
                    'title'  => 'Building a RAG System with Python and LangChain',
                    'excerpt' => 'Retrieval Augmented Generation lets you ground AI responses in your own data. Build a production-ready RAG pipeline from document ingestion to intelligent query response.',
                    'image'  => 'https://picsum.photos/seed/ragpython/800/450',
                    'tags'   => ['Python', 'Tutorial'],
                ],
                [
                    'title'  => 'AI-Powered Code Review: Tools and Best Practices',
                    'excerpt' => 'AI code review tools can catch bugs, enforce style, and suggest improvements. Explore the leading tools, their strengths, and how to integrate them into your CI/CD pipeline.',
                    'image'  => 'https://picsum.photos/seed/aicoderev/800/450',
                    'tags'   => ['Open Source', 'Tools'],
                ],
            ],
            'devops' => [
                [
                    'title'  => 'Kubernetes for Developers: From Zero to Production',
                    'excerpt' => 'Kubernetes can seem overwhelming at first. This hands-on guide walks you through deployments, services, ingress, scaling, and monitoring without the jargon.',
                    'image'  => 'https://picsum.photos/seed/k8sguide/800/450',
                    'tags'   => ['Kubernetes', 'Docker', 'Tutorial'],
                ],
                [
                    'title'  => 'Building a Zero-Downtime Deployment Pipeline',
                    'excerpt' => 'Downtime costs money and trust. Learn how to implement blue-green deployments, canary releases, and automated rollbacks using GitHub Actions and AWS.',
                    'image'  => 'https://picsum.photos/seed/cicdpipe/800/450',
                    'tags'   => ['AWS', 'Docker'],
                ],
                [
                    'title'  => 'Infrastructure as Code with Terraform: A Practical Guide',
                    'excerpt' => 'Stop clicking through cloud consoles. Terraform lets you define, version, and automate your entire infrastructure. This guide covers modules, state management, and multi-cloud deployments.',
                    'image'  => 'https://picsum.photos/seed/terraform/800/450',
                    'tags'   => ['AWS', 'Linux'],
                ],
            ],
            'ui-ux-design' => [
                [
                    'title'  => 'The Psychology of Color in UI Design',
                    'excerpt' => 'Color is not just decoration — it guides attention, sets mood, and drives action. Understand how color psychology applies to buttons, backgrounds, and brand identity.',
                    'image'  => 'https://picsum.photos/seed/colorpsych/800/450',
                    'tags'   => ['Figma'],
                ],
                [
                    'title'  => 'Designing for Accessibility: A Complete WCAG 2.2 Checklist',
                    'excerpt' => 'Accessible design is good design. Walk through the WCAG 2.2 success criteria with practical examples, contrast tools, and patterns that work for all users.',
                    'image'  => 'https://picsum.photos/seed/a11ydesign/800/450',
                    'tags'   => ['Tutorial'],
                ],
                [
                    'title'  => 'Micro-Interactions That Delight: A Motion Design Handbook',
                    'excerpt' => 'Subtle animations make interfaces feel alive. Explore the principles of motion design, timing functions, and how to implement delightful micro-interactions without overdoing it.',
                    'image'  => 'https://picsum.photos/seed/microux/800/450',
                    'tags'   => ['Figma', 'Tips & Tricks'],
                ],
            ],
            'typography' => [
                [
                    'title'  => 'Fluid Typography with CSS clamp(): A Modern Approach',
                    'excerpt' => 'Fluid type scales with the viewport without media query breakpoints. Master the CSS clamp() function to create responsive typography systems that look perfect at any screen size.',
                    'image'  => 'https://picsum.photos/seed/fluidtype/800/450',
                    'tags'   => ['CSS', 'Tutorial'],
                ],
                [
                    'title'  => 'Pairing Fonts Like a Pro: Rules and Beautiful Examples',
                    'excerpt' => 'Great font pairings elevate design from ordinary to extraordinary. Learn the principles of contrast, harmony, and hierarchy with curated examples from world-class typography.',
                    'image'  => 'https://picsum.photos/seed/fontpair/800/450',
                    'tags'   => ['Tips & Tricks'],
                ],
                [
                    'title'  => 'Variable Fonts: The Future of Web Typography',
                    'excerpt' => 'Variable fonts pack infinite type variations into a single file. Discover how to leverage axes like weight, width, and optical size to build expressive, performant type systems.',
                    'image'  => 'https://picsum.photos/seed/varfont/800/450',
                    'tags'   => ['CSS', 'HTML'],
                ],
            ],
            'color-theory' => [
                [
                    'title'  => 'Building a Design System Color Palette from Scratch',
                    'excerpt' => 'A well-crafted color palette is the foundation of every great design system. Learn how to choose base hues, generate accessible tints and shades, and document your system.',
                    'image'  => 'https://picsum.photos/seed/designcolor/800/450',
                    'tags'   => ['Figma'],
                ],
                [
                    'title'  => 'Dark Mode Design: Color Challenges and Solutions',
                    'excerpt' => 'Dark mode is not just an inverted light theme. Explore elevation, surface colors, vibrant vs. muted palettes, and how to adapt illustrations and icons for dark backgrounds.',
                    'image'  => 'https://picsum.photos/seed/darkmode/800/450',
                    'tags'   => ['CSS', 'Tips & Tricks'],
                ],
                [
                    'title'  => 'OKLCH: The New Color Space for Modern CSS',
                    'excerpt' => 'OKLCH offers perceptually uniform color manipulation that HSL cannot match. Understand the color space, build harmonious palettes, and start using it in production CSS today.',
                    'image'  => 'https://picsum.photos/seed/oklch/800/450',
                    'tags'   => ['CSS'],
                ],
            ],
            'entrepreneurship' => [
                [
                    'title'  => 'From Idea to $10K MRR: Lessons from a Solo Founder',
                    'excerpt' => 'Building a profitable SaaS product alone is hard. This honest account covers validating ideas, acquiring first customers, handling churn, and the mental challenges of solo entrepreneurship.',
                    'image'  => 'https://picsum.photos/seed/solofounder/800/450',
                    'tags'   => ['Opinion', 'Career'],
                ],
                [
                    'title'  => 'How to Write a Business Plan Investors Actually Read',
                    'excerpt' => 'Most business plans get skimmed in 3 minutes. Learn the structure, narrative, and financial models that capture investor attention and clearly communicate your vision and traction.',
                    'image'  => 'https://picsum.photos/seed/bizplan/800/450',
                    'tags'   => ['Tips & Tricks'],
                ],
                [
                    'title'  => 'Product-Market Fit: How to Know When You\'ve Found It',
                    'excerpt' => 'Product-market fit is the most important milestone for any startup. Learn the signals, metrics, and qualitative indicators that separate real traction from false positives.',
                    'image'  => 'https://picsum.photos/seed/pmfit/800/450',
                    'tags'   => ['Opinion'],
                ],
            ],
            'marketing' => [
                [
                    'title'  => 'The Complete SEO Strategy for Technical Blogs in 2025',
                    'excerpt' => 'Technical content has unique SEO challenges. Master keyword research for developers, topical authority, Core Web Vitals, and link building strategies that actually work for niche blogs.',
                    'image'  => 'https://picsum.photos/seed/seostrat/800/450',
                    'tags'   => ['SEO', 'Content Marketing'],
                ],
                [
                    'title'  => 'Email Marketing That Converts: Beyond Open Rate Vanity Metrics',
                    'excerpt' => 'Open rates are dying. Focus on what matters: click-through rates, conversion rates, and revenue per subscriber. Build sequences that nurture, educate, and sell without annoying.',
                    'image'  => 'https://picsum.photos/seed/emailmkt/800/450',
                    'tags'   => ['Analytics', 'Tips & Tricks'],
                ],
                [
                    'title'  => 'Content Marketing on a Zero Budget: A Growth Playbook',
                    'excerpt' => 'You don\'t need a content agency to drive organic growth. This playbook covers topic ideation, repurposing, distribution, and community-led strategies that punch above their weight.',
                    'image'  => 'https://picsum.photos/seed/contentmkt/800/450',
                    'tags'   => ['Content Marketing', 'Social Media'],
                ],
            ],
            'productivity' => [
                [
                    'title'  => 'Deep Work in the Age of Constant Notifications',
                    'excerpt' => 'Distractions are the enemy of great work. Explore the science of focused attention, environment design, scheduling strategies, and tools that help you reclaim your most valuable resource.',
                    'image'  => 'https://picsum.photos/seed/deepwork/800/450',
                    'tags'   => ['Productivity', 'Tools'],
                ],
                [
                    'title'  => 'My Second Brain: How I Manage Knowledge with Obsidian',
                    'excerpt' => 'Managing information overload is a superpower. Learn how to build a personal knowledge management system in Obsidian using the PARA method, maps of content, and daily notes.',
                    'image'  => 'https://picsum.photos/seed/obsidian/800/450',
                    'tags'   => ['Tools', 'Tips & Tricks'],
                ],
                [
                    'title'  => 'The Developer\'s Guide to Time Blocking',
                    'excerpt' => 'Reactive work kills focus. Time blocking gives your day intentional structure. See how developers can protect deep work sessions, handle interruptions, and actually finish projects.',
                    'image'  => 'https://picsum.photos/seed/timeblock/800/450',
                    'tags'   => ['Productivity', 'Career'],
                ],
            ],
            'travel' => [
                [
                    'title'  => 'Remote Work from Bali: A 3-Month Digital Nomad Diary',
                    'excerpt' => 'Bali has become the world\'s most popular remote work destination. An honest account of co-working spaces, internet reliability, cost of living, community, and what nobody tells you before you go.',
                    'image'  => 'https://picsum.photos/seed/balitravel/800/450',
                    'tags'   => ['Opinion'],
                ],
                [
                    'title'  => 'The Ultimate Minimalist Packing List for Long-Term Travel',
                    'excerpt' => 'Overpacking is the number-one mistake new travelers make. After 50 countries and 5 years of travel, here is the exact 35-liter carry-on system that handles any climate and occasion.',
                    'image'  => 'https://picsum.photos/seed/packinglist/800/450',
                    'tags'   => ['Tips & Tricks'],
                ],
                [
                    'title'  => 'Hidden Gems of Southeast Asia Beyond the Tourist Trail',
                    'excerpt' => 'Skip the crowded beaches and Instagram spots. Discover the lesser-known towns, temples, and experiences across Vietnam, Cambodia, Laos, and Myanmar that reward curious travelers.',
                    'image'  => 'https://picsum.photos/seed/seasia/800/450',
                    'tags'   => ['Opinion'],
                ],
            ],
            'health-wellness' => [
                [
                    'title'  => 'The Science of Sleep: How to Optimize Your Rest as a Developer',
                    'excerpt' => 'Poor sleep is silently destroying your productivity and health. Understand sleep architecture, circadian rhythms, and the evidence-backed habits that lead to consistently restorative rest.',
                    'image'  => 'https://picsum.photos/seed/sleepscience/800/450',
                    'tags'   => ['Tips & Tricks'],
                ],
                [
                    'title'  => 'Ergonomics for Developers: Preventing RSI and Back Pain',
                    'excerpt' => 'Repetitive strain injuries are career-ending if ignored. Learn how to set up your workstation, choose the right peripherals, and build movement habits that protect your body long-term.',
                    'image'  => 'https://picsum.photos/seed/ergonomics/800/450',
                    'tags'   => ['Tools', 'Tips & Tricks'],
                ],
                [
                    'title'  => 'Mental Health in Tech: Breaking the Stigma of Burnout',
                    'excerpt' => 'Burnout in software engineering is endemic and often invisible until it is too late. Recognize the warning signs, understand the stages, and build a sustainable relationship with your work.',
                    'image'  => 'https://picsum.photos/seed/techmental/800/450',
                    'tags'   => ['Opinion', 'Career'],
                ],
            ],
            'tutorials' => [
                [
                    'title'  => 'Build a Real-Time Chat App with Laravel Reverb and Vue',
                    'excerpt' => 'Laravel Reverb brings first-party WebSockets to the framework. Follow this step-by-step tutorial to build a fully functional real-time chat application with channels and presence.',
                    'image'  => 'https://picsum.photos/seed/realtimechat/800/450',
                    'tags'   => ['Laravel', 'Vue.js', 'Tutorial'],
                ],
                [
                    'title'  => 'Deploy Laravel to AWS with Zero Downtime Using GitHub Actions',
                    'excerpt' => 'Manual deployments are risky and slow. Automate your Laravel deployment to EC2 with GitHub Actions, RDS, ElastiCache, and a load balancer for true zero-downtime releases.',
                    'image'  => 'https://picsum.photos/seed/laravelaws/800/450',
                    'tags'   => ['Laravel', 'AWS', 'Tutorial'],
                ],
                [
                    'title'  => 'Building a REST API with Node.js, Express, and PostgreSQL',
                    'excerpt' => 'Node.js and PostgreSQL are a powerful combination for scalable APIs. Build a complete CRUD API with authentication, validation, error handling, and OpenAPI documentation.',
                    'image'  => 'https://picsum.photos/seed/nodeapi/800/450',
                    'tags'   => ['Node.js', 'PostgreSQL', 'Tutorial'],
                ],
            ],
            'news' => [
                [
                    'title'  => 'PHP 9.0: Everything You Need to Know About the New Features',
                    'excerpt' => 'PHP continues to evolve at a rapid pace. The upcoming 9.0 release brings property hooks, pipe operator enhancements, improved fibers, and several breaking changes you need to prepare for.',
                    'image'  => 'https://picsum.photos/seed/php9news/800/450',
                    'tags'   => ['PHP', 'News'],
                ],
                [
                    'title'  => 'The State of JavaScript 2025: Survey Results and Key Takeaways',
                    'excerpt' => 'The annual State of JavaScript survey is out. Explore what 30,000 developers said about frameworks, tooling, runtimes, and where the ecosystem is heading over the next few years.',
                    'image'  => 'https://picsum.photos/seed/jsstate/800/450',
                    'tags'   => ['JavaScript', 'News'],
                ],
                [
                    'title'  => 'Open Source AI Models Are Closing the Gap with Proprietary LLMs',
                    'excerpt' => 'Llama, Mistral, and Qwen are approaching GPT-4 quality on key benchmarks. Explore what this means for developers, self-hosting possibilities, and the future of the AI industry.',
                    'image'  => 'https://picsum.photos/seed/openaioss/800/450',
                    'tags'   => ['Open Source', 'News'],
                ],
            ],
        ];
    }

    private function buildContent(string $title, string $excerpt, string $category): string
    {
        return <<<HTML
<p class="lead">{$excerpt}</p>

<h2>Introduction</h2>
<p>In today's rapidly evolving landscape, staying ahead requires a deep understanding of the fundamentals and the courage to experiment with emerging approaches. This article breaks down everything you need to know about {$title} in a practical, actionable way.</p>

<h2>Why This Matters</h2>
<p>The gap between developers who understand these concepts and those who don't is widening every year. Whether you're building your first project or scaling a system to millions of users, the principles covered here apply at every level of the stack.</p>
<p>We've seen teams cut their development time in half by applying these techniques correctly. The key is not just knowing what to do, but understanding why — so you can adapt when the situation demands it.</p>

<h2>Core Concepts</h2>
<p>Before diving into the implementation details, it's worth establishing a shared vocabulary. The terminology here is used consistently with industry standards, so you can apply what you learn across different tools and contexts.</p>
<ul>
  <li><strong>Foundation:</strong> The underlying principles that don't change regardless of which framework or tool you're using.</li>
  <li><strong>Patterns:</strong> Reusable solutions to common problems that emerge in this domain.</li>
  <li><strong>Trade-offs:</strong> Every approach has costs and benefits. Understanding them lets you make informed decisions.</li>
</ul>

<h2>A Practical Walkthrough</h2>
<p>Theory only takes you so far. The best way to learn is by doing, so let's work through a real-world example that you can adapt to your own projects.</p>

<pre><code>// Practical example demonstrating the core concepts
const solution = {
  approach: 'pragmatic',
  focus: 'outcomes over process',
  iterate: true
};

console.log('Building something great:', solution);</code></pre>

<p>Notice how we prioritize clarity over cleverness. Code that your future self and teammates can understand is always better than the cleverest one-liner.</p>

<h2>Common Pitfalls to Avoid</h2>
<p>Experience is the best teacher, but other people's experience is even better. Here are the mistakes that trip up even experienced practitioners in this area:</p>
<ol>
  <li><strong>Premature optimization</strong> — solve the real problem first, then optimize what's actually slow.</li>
  <li><strong>Ignoring the user</strong> — technical elegance means nothing if the end result doesn't serve the person using it.</li>
  <li><strong>Skipping tests</strong> — the confidence that comes from a good test suite is worth every minute spent writing it.</li>
</ol>

<blockquote>
  "Make it work, make it right, make it fast — in that order." — Kent Beck
</blockquote>

<h2>Taking It Further</h2>
<p>This article has covered the essential ground, but there's always more to explore. The best practitioners in any field are perpetually curious — they read broadly, experiment constantly, and share what they learn with their communities.</p>
<p>Consider contributing to an open source project in this space, writing about your own experiments, or teaching a colleague. The act of explaining something is one of the most effective ways to deepen your own understanding.</p>

<h2>Conclusion</h2>
<p>The concepts and techniques in this article have been battle-tested across dozens of projects and teams. Start small, apply what resonates, and iterate as you learn what works in your specific context. The most important thing is to begin.</p>
HTML;
    }
}
