<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'user';

    protected $fillable = [
        'guardian_id',
        'classroom_id',
        'name',
        'email',
        'date_of_birth',
        'description',
        'password_hash',
        'role',
        'is_minor',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_minor' => 'boolean',
    ];

    public function getAuthPasswordName()
    {
        return 'password_hash';
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function setPasswordAttribute($value): void
    {
        $this->attributes['password_hash'] = $value;
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function taughtClassrooms()
    {
        return $this->belongsToMany(Classroom::class, 'classroom_teacher', 'teacher_id', 'classroom_id');
    }

    public function guardian()
    {
        return $this->belongsTo(User::class, 'guardian_id');
    }

    public function wards()
    {
        return $this->hasMany(User::class, 'guardian_id');
    }

    public function absences()
    {
        return $this->hasMany(Absence::class, 'student_id');
    }

    public function delays()
    {
        return $this->hasMany(Delay::class, 'student_id');
    }

    public function isAdult(): ?bool
    {
        if ($this->date_of_birth) {
            return Carbon::parse($this->date_of_birth)->age >= 18;
        }

        if ($this->is_minor !== null) {
            return !$this->is_minor;
        }

        return null;
    }

    public function isMinor(): ?bool
    {
        $adult = $this->isAdult();
        return $adult === null ? null : !$adult;
    }
}
