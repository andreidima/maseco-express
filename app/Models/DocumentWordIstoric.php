<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentWordIstoric extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'documente_word_istoric';
    protected $guarded = [];
}
