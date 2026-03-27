<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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
        'is_active',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_minor' => 'boolean',
        'is_active' => 'boolean',
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

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
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

    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'ADMIN') {
            return true;
        }

        // Keep legacy role column authoritative to avoid hidden access issues
        // when role_user / permission_role data is not fully aligned.
        if ($this->hasLegacyPermission($permission)) {
            return true;
        }

        $hasAssignedRoles = $this->roles()->exists();

        $hasPermission = $this->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('name', $permission);
            })
            ->exists();

        if ($hasPermission) {
            return true;
        }

        // If this user has role assignments but not the required permission, deny.
        if ($hasAssignedRoles) {
            return false;
        }

        // Transitional fallback for legacy data where role_user is not aligned.
        return $this->hasLegacyPermission($permission);
    }

    private function hasLegacyPermission(string $permission): bool
    {
        $legacyMap = [
            'TEACHER' => [
                'teacher.classes.access',
                'teacher.students.access',
            ],
            'STUDENT' => [
                'student.absences.access',
                'student.delays.access',
                'student.signatures.access',
                'student.certificates.access',
                'student.reports.access',
            ],
            'GUARDIAN' => [
                'guardian.absences.access',
            ],
            'CAPOLAB' => [
                'teacher.classes.access',
                'teacher.students.access',
                'capolab.absence_approvals.access',
            ],
            'DIREZIONE' => [
                'teacher.classes.access',
                'teacher.students.access',
                'direzione.absence_approvals.access',
            ],
        ];

        if (!$this->role || !isset($legacyMap[$this->role])) {
            return false;
        }

        return in_array($permission, $legacyMap[$this->role], true);
    }

    public function hasGlobalInstituteVisibility(): bool
    {
        return in_array($this->role, ['DIREZIONE', 'ADMIN'], true);
    }
}
