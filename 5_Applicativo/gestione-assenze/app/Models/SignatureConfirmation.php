<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignatureConfirmation extends Model
{
    protected $table = 'signature_confirmation';

    protected $fillable = [
        'absence_id',
        'signer_name',
        'signer_email',
        'token_hash',
        'expires_at',
        'signed_at',
        'signature_path',
        'ip_address',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'signed_at' => 'datetime',
    ];

    public function absence()
    {
        return $this->belongsTo(Absence::class, 'absence_id');
    }
}
