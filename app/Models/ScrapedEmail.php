<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScrapedEmail extends Model
{
    use HasFactory;

    protected $table = 'scraped_emails';
    protected $guarded = [];

    public function path($action = 'show')
    {
        return match ($action) {
            'edit' => route('scraped_emails.edit', $this->id),
            'destroy' => route('scraped_emails.destroy', $this->id),
            default => route('scraped_emails.show', $this->id),
        };
    }
}
