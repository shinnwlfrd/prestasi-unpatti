<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SikadCredential extends Model
{
    use HasFactory;

    protected $primaryKey = 'student_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'student_id',
        'password_hash',
        'last_login',
        'is_active',
    ];

    protected $casts = [
        'last_login' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relasi
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
}