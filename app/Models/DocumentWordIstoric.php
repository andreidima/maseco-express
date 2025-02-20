<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentWordIstoric extends Model
{
    use HasFactory;

    // Use the same column for both creation and update timestamps.
    const CREATED_AT = 'operare_data';
    const UPDATED_AT = 'operare_data';

    protected $table = 'documente_word_istoric';
    protected $primaryKey = 'id_pk';
    protected $guarded = [];
}
