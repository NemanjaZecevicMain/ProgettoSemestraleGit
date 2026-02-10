<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'user';

    protected $fillable = [
        'classroom_id',
        'name',
        'email',
        'description',
        'password_hash',
        'role',
        'is_minor',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function absences()
    {
        return $this->hasMany(Absence::class, 'student_id');
    }

    public function delays()
    {
        return $this->hasMany(Delay::class, 'student_id');
    }
}
