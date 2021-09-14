<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDomains extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'domain_id',
        'type'
    ];
}
