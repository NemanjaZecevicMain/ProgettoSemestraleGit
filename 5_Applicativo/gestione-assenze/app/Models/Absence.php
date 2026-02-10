<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    protected $table = 'absence';

    protected $fillable = [
        'student_id',
        'date_from',
        'date_to',
        'reason',
        'status',
        'hours_assigned',
        'note',
        'is_approved',
        'approved_by_user_id',
        'approved_at',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'approved_at' => 'datetime',
        'is_approved' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
