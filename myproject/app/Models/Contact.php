<?php

namespace App\Models;

use App\Enums\ContactStatus;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'subject',
        'body',
        'status',
        'ip_address',
        'user_agent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ContactStatus::class,
        ];
    }
}
