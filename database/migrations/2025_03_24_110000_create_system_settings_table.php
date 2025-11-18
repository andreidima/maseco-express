<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $now = now();
        $mailSettings = [
            'MAIL_MAILER' => env('MAIL_MAILER', config('mail.default')),
            'MAIL_HOST' => env('MAIL_HOST', config('mail.mailers.smtp.host')),
            'MAIL_PORT' => env('MAIL_PORT', config('mail.mailers.smtp.port')),
            'MAIL_USERNAME' => env('MAIL_USERNAME', config('mail.mailers.smtp.username')),
            'MAIL_PASSWORD' => env('MAIL_PASSWORD', config('mail.mailers.smtp.password')),
            'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION', config('mail.mailers.smtp.encryption')),
            'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS', config('mail.from.address')),
            'MAIL_FROM_NAME' => env('MAIL_FROM_NAME', config('mail.from.name')),
            'MAIL_REPLY_TO_ADDRESS' => env('MAIL_REPLY_TO_ADDRESS', data_get(config('mail.reply_to'), 'address')),
            'MAIL_REPLY_TO_NAME' => env('MAIL_REPLY_TO_NAME', data_get(config('mail.reply_to'), 'name')),
        ];

        foreach ($mailSettings as $key => $value) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
