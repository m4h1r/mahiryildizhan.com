<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SettingController extends Controller
{
    private const DEFINITIONS = [
        'financial' => [
            ['key' => 'treasury_try', 'label' => 'Kasa (₺)'],
            ['key' => 'daily_passive_income_try', 'label' => 'Günlük Pasif Gelir (₺)'],
        ],
        'general' => [
            ['key' => 'admin_locale', 'label' => 'Admin Language'],
        ],
        'analytics' => [
            ['key' => 'gtm_id', 'label' => 'Google Tag Manager ID (GTM-XXXXXXX)'],
            ['key' => 'ga_tracking_id', 'label' => 'GA4 Measurement ID (G-XXXXXXXXXX) — Direct, GTM kullanmıyorsan'],
            ['key' => 'search_console_verification', 'label' => 'Search Console Verification'],
            ['key' => 'crux_api_key', 'label' => 'CrUX API Key', 'is_secret' => true],
        ],
        'advertising' => [
            ['key' => 'adsense_client_id', 'label' => 'AdSense Client ID'],
            ['key' => 'adsense_slot_id', 'label' => 'AdSense Slot ID'],
        ],
        'seo' => [
            ['key' => 'site_name', 'label' => 'Site Name'],
            ['key' => 'default_og_image', 'label' => 'Default OG Image'],
            ['key' => 'default_meta_description', 'label' => 'Default Meta Description'],
        ],
        'mailchimp' => [
            ['key' => 'mailchimp_api_key', 'label' => 'Mailchimp API Key', 'is_secret' => true],
            ['key' => 'mailchimp_list_id', 'label' => 'Mailchimp List ID'],
            ['key' => 'mailchimp_datacenter', 'label' => 'Mailchimp Datacenter'],
        ],
        'recaptcha' => [
            ['key' => 'recaptcha_site_key', 'label' => 'reCAPTCHA Site Key'],
            ['key' => 'recaptcha_secret_key', 'label' => 'reCAPTCHA Secret Key', 'is_secret' => true],
        ],
        'weather' => [
            ['key' => 'weather_latitude', 'label' => 'Weather Latitude'],
            ['key' => 'weather_longitude', 'label' => 'Weather Longitude'],
            ['key' => 'weather_city_name', 'label' => 'Weather City Name'],
        ],
        'social_links' => [
            ['key' => 'social_tagline',   'label' => 'Biolink Tagline'],
            ['key' => 'social_github',    'label' => 'GitHub URL'],
            ['key' => 'social_youtube',   'label' => 'YouTube URL'],
            ['key' => 'social_linkedin',  'label' => 'LinkedIn URL'],
            ['key' => 'social_instagram', 'label' => 'Instagram URL'],
            ['key' => 'social_twitter',   'label' => 'X (Twitter) URL'],
        ],
        'about' => [
            ['key' => 'about_content_en', 'label' => 'About Page (English)'],
            ['key' => 'about_content_tr', 'label' => 'About Page (Turkish)'],
        ],
    ];

    public function index(): View
    {
        $settings = Setting::query()->get()->keyBy('key');

        return view('admin.settings', [
            'settingsByGroup' => $this->groupedSettings($settings),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $payload = $request->input('settings', []);

        foreach (self::DEFINITIONS as $group => $items) {
            foreach ($items as $item) {
                $key = $item['key'];
                $value = isset($payload[$key]) ? trim((string) $payload[$key]) : null;
                $isSecret = (bool) ($item['is_secret'] ?? false);

                Setting::query()->updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => $value !== '' ? $value : null,
                        'group' => $group,
                        'is_secret' => $isSecret,
                        'description' => $item['label'],
                    ]
                );
            }
        }

        return to_route('admin.settings')->with('success', __('Settings updated.'));
    }

    private function groupedSettings(Collection $settings): array
    {
        $grouped = [];

        foreach (self::DEFINITIONS as $group => $items) {
            $grouped[$group] = array_map(function (array $item) use ($settings): array {
                $existing = $settings->get($item['key']);

                return [
                    'key' => $item['key'],
                    'label' => $item['label'],
                    'is_secret' => (bool) ($item['is_secret'] ?? false),
                    'value' => $existing?->value,
                ];
            }, $items);
        }

        return $grouped;
    }
}