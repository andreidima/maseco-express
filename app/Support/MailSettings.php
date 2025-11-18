<?php

namespace App\Support;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class MailSettings
{
    private const CACHE_KEY = 'system_settings.mail';

    private const KEY_MAP = [
        'mail_mailer' => 'MAIL_MAILER',
        'mail_host' => 'MAIL_HOST',
        'mail_port' => 'MAIL_PORT',
        'mail_username' => 'MAIL_USERNAME',
        'mail_password' => 'MAIL_PASSWORD',
        'mail_encryption' => 'MAIL_ENCRYPTION',
        'mail_from_address' => 'MAIL_FROM_ADDRESS',
        'mail_from_name' => 'MAIL_FROM_NAME',
        'mail_reply_to_address' => 'MAIL_REPLY_TO_ADDRESS',
        'mail_reply_to_name' => 'MAIL_REPLY_TO_NAME',
    ];

    /**
     * Retrieve the mail settings, falling back to config defaults.
     */
    public function get(): array
    {
        $settings = $this->defaults();

        if (! Schema::hasTable('system_settings')) {
            return $settings;
        }

        $stored = Cache::rememberForever(self::CACHE_KEY, function () {
            return SystemSetting::whereIn('key', array_values(self::KEY_MAP))
                ->pluck('value', 'key')
                ->toArray();
        });

        foreach (self::KEY_MAP as $field => $key) {
            if (array_key_exists($key, $stored) && $stored[$key] !== null) {
                $settings[$field] = $stored[$key];
            }
        }

        return $settings;
    }

    /**
     * Persist settings coming from the form and refresh cache/config.
     */
    public function update(array $data): void
    {
        foreach (self::KEY_MAP as $field => $key) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            $value = $this->normalizeValue($data[$field]);

            SystemSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        Cache::forget(self::CACHE_KEY);
        $this->applyToConfig();
    }

    /**
     * Push the stored values into Laravel's mail config at runtime.
     */
    public function applyToConfig(): void
    {
        if (! Schema::hasTable('system_settings')) {
            return;
        }

        $settings = $this->get();

        if (! empty($settings['mail_mailer'])) {
            Config::set('mail.default', $settings['mail_mailer']);
        }

        Config::set('mail.mailers.smtp.host', $settings['mail_host']);
        Config::set('mail.mailers.smtp.port', $settings['mail_port']);
        Config::set('mail.mailers.smtp.username', $settings['mail_username']);
        Config::set('mail.mailers.smtp.password', $settings['mail_password']);
        Config::set('mail.mailers.smtp.encryption', $settings['mail_encryption']);

        Config::set('mail.from', [
            'address' => $settings['mail_from_address'],
            'name' => $settings['mail_from_name'],
        ]);

        if (! empty($settings['mail_reply_to_address'])) {
            Config::set('mail.reply_to', [
                'address' => $settings['mail_reply_to_address'],
                'name' => $settings['mail_reply_to_name'],
            ]);
        } else {
            Config::set('mail.reply_to', null);
        }
    }

    private function defaults(): array
    {
        return [
            'mail_mailer' => config('mail.default'),
            'mail_host' => config('mail.mailers.smtp.host'),
            'mail_port' => config('mail.mailers.smtp.port'),
            'mail_username' => config('mail.mailers.smtp.username'),
            'mail_password' => config('mail.mailers.smtp.password'),
            'mail_encryption' => config('mail.mailers.smtp.encryption'),
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
            'mail_reply_to_address' => data_get(config('mail.reply_to'), 'address'),
            'mail_reply_to_name' => data_get(config('mail.reply_to'), 'name'),
        ];
    }

    private function normalizeValue(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        if ($value === '' || $value === null) {
            return null;
        }

        return (string) $value;
    }
}
