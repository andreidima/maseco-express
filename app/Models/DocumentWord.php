<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentWord extends Model
{
    use HasFactory;

    protected $table = 'documente_word';
    protected $guarded = [];

    public function path()
    {
        return "/documente-word/{$this->id}";
    }
}
