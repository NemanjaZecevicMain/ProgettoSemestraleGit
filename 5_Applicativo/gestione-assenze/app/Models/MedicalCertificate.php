<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalCertificate extends Model
{
    protected $table = 'medical_certificate';

    protected $fillable = [
        'absence_id',
        'slot',
        'file_path',
        'uploaded_at',
        'deadline_at',
        'validated_by_teacher_id',
        'status',
        'note',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'deadline_at' => 'datetime',
    ];

    public function absence()
    {
        return $this->belongsTo(Absence::class, 'absence_id');
    }
}
