<?php

namespace App\Services;

use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\Schema;

class WebsiteSettings
{
    private ?array $settings = null;

    public function all(): array
    {
        if ($this->settings === null) {
            $saved = Schema::hasTable('website_settings')
                ? (WebsiteSetting::find(1)?->data ?? [])
                : [];
            $this->settings = self::withDefaults($saved);
            $this->settings['hotline_link'] = preg_replace('/[^0-9+]/', '', $this->settings['hotline']);
        }

        return $this->settings;
    }

    public function forget(): void
    {
        $this->settings = null;
    }

    public static function withDefaults(array $saved): array
    {
        $settings = array_replace(config('website.defaults'), $saved);
        if (!array_key_exists('banners', $saved)) {
            // Preserve configurations saved by the original three-slot form.
            foreach ($settings['banners'] as $index => &$banner) {
                foreach (['desktop', 'mobile', 'enabled', 'alt'] as $field) {
                    $key = 'banner_'.($index + 1).'_'.$field;
                    if (array_key_exists($key, $saved)) $banner[$field] = $saved[$key];
                }
            }
            unset($banner);
        }
        foreach (array_keys($settings) as $key) {
            if (preg_match('/^banner_\d+_/', $key)) unset($settings[$key]);
        }

        return $settings;
    }

    public static function defaultBanner(?string $id): ?array
    {
        foreach (config('website.defaults.banners') as $banner) {
            if ($banner['id'] === $id) return $banner;
        }

        return null;
    }
}
