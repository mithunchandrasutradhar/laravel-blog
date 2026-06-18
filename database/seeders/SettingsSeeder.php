<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Default site settings organised by group.
     *
     * @var array<string, array<string, mixed>>
     */
    private array $settings = [
        // -----------------------------------------------------------------------
        // General
        // -----------------------------------------------------------------------
        'general' => [
            'site_name'        => 'My Blog',
            'site_tagline'     => 'Thoughts, stories and ideas.',
            'site_description' => 'A modern blog platform built with Laravel.',
            'site_logo'        => '',
            'site_favicon'     => '',
            'site_email'       => 'hello@myblog.com',
            'site_phone'       => '',
            'site_address'     => '',
            'posts_per_page'   => '10',
            'date_format'      => 'F j, Y',
            'time_format'      => 'g:i a',
            'timezone'         => 'UTC',
            'language'         => 'en',
            'maintenance_mode'    => '0',
            'terms_page'          => '',
            'privacy_policy_page' => '',
        ],

        // -----------------------------------------------------------------------
        // SEO
        // -----------------------------------------------------------------------
        'seo' => [
            'meta_title'            => 'My Blog – Thoughts, stories and ideas',
            'meta_description'      => 'A modern blog platform built with Laravel.',
            'meta_keywords'         => 'blog, laravel, technology',
            'google_analytics_id'   => '',
            'google_tag_manager_id' => '',
            'robots_txt'            => "User-agent: *\nAllow: /",
            'canonical_url'         => '',
            'og_image'              => '',
            'twitter_card'          => 'summary_large_image',
            'twitter_site'          => '',
            'structured_data'       => '1',
        ],

        // -----------------------------------------------------------------------
        // Social
        // -----------------------------------------------------------------------
        'social' => [
            'facebook_url'  => '',
            'twitter_url'   => '',
            'instagram_url' => '',
            'linkedin_url'  => '',
            'youtube_url'   => '',
            'github_url'    => '',
            'pinterest_url' => '',
        ],

        // -----------------------------------------------------------------------
        // Mail
        // -----------------------------------------------------------------------
        'mail' => [
            'mail_driver'          => 'smtp',
            'mail_host'            => 'smtp.mailtrap.io',
            'mail_port'            => '587',
            'mail_username'        => '',
            'mail_password'        => '',
            'mail_encryption'      => 'tls',
            'mail_from_address'    => 'noreply@myblog.com',
            'mail_from_name'       => 'My Blog',
            'newsletter_from_name' => 'My Blog Newsletter',
        ],

        // -----------------------------------------------------------------------
        // Comments
        // -----------------------------------------------------------------------
        'comments' => [
            'comments_enabled'        => '1',
            'comments_moderation'     => '1',
            'comments_guest_allowed'  => '1',
            'comments_per_page'       => '20',
            'recaptcha_enabled'       => '0',
            'recaptcha_site_key'      => '',
            'recaptcha_secret_key'    => '',
        ],

        // -----------------------------------------------------------------------
        // Appearance
        // -----------------------------------------------------------------------
        'appearance' => [
            'theme'              => 'default',
            'primary_color'      => '#3b82f6',
            'secondary_color'    => '#6b7280',
            'font_family'        => 'Inter, sans-serif',
            'custom_css'         => '',
            'custom_js'          => '',
            'header_scripts'     => '',
            'footer_scripts'     => '',
        ],

        // -----------------------------------------------------------------------
        // Storage
        // -----------------------------------------------------------------------
        'storage' => [
            'default_disk'          => 'public',
            'max_upload_size'       => '10240', // KB
            'allowed_image_types'   => 'jpg,jpeg,png,gif,webp,svg',
            'allowed_file_types'    => 'pdf,doc,docx,xls,xlsx,zip',
            'image_quality'         => '85',
            'generate_thumbnails'   => '1',
            'thumbnail_width'       => '400',
            'thumbnail_height'      => '300',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $count = 0;

        foreach ($this->settings as $group => $items) {
            foreach ($items as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'group' => $group]
                );
                $count++;
            }
        }

        $this->command->info("Seeded {$count} settings across " . count($this->settings) . ' groups.');
    }
}
