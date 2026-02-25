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
        'is_signed',
        'signed_at',
        'signed_by_user_id',
        'signature_file_path',
        'time_from',
        'time_to',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'approved_at' => 'datetime',
        'is_approved' => 'boolean',
        'signed_at' => 'datetime',
        'is_signed' => 'boolean',
        'time_from' => 'array',
        'time_to' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function certificates()
    {
        return $this->hasMany(MedicalCertificate::class, 'absence_id');
    }

    public function signedBy()
    {
        return $this->belongsTo(User::class, 'signed_by_user_id');
    }
}
