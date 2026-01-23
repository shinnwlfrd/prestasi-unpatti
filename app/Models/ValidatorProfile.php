<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidatorProfile extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'department',
    ];

    // Relasi
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
