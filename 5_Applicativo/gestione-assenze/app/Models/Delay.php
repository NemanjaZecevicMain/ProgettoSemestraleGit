<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delay extends Model
{
    protected $table = 'delay';

    protected $fillable = [
        'student_id',
        'created_by_teacher_id',
        'date',
        'minutes',
        'note',
        'is_signed',
        'signed_at',
        'signed_by_user_id',
    ];

    protected $casts = [
        'date' => 'date',
        'signed_at' => 'datetime',
        'is_signed' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function signedBy()
    {
        return $this->belongsTo(User::class, 'signed_by_user_id');
    }
}
