<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    protected $table = 'classroom';

    protected $fillable = [
        'name',
        'year',
        'section',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'classroom_id');
    }
}
