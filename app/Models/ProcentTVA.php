<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcentTVA extends Model
{
    use HasFactory;

    protected $table = 'procente_tva';
    protected $guarded = [];

    public function path()
    {
        return "/procenteTVA/{$this->id}";
    }
}
