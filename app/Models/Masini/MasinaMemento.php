<?php

namespace App\Models\Masini;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasinaMemento extends Model
{
    use HasFactory;

    protected $table = 'masini_mementouri';

    protected $fillable = [
        'email_notificari',
        'telefon_notificari',
        'observatii',
    ];

    public function masina()
    {
        return $this->belongsTo(Masina::class);
    }
}
