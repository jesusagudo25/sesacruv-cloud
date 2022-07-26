<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Standby extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'identity_card',
        'receipt_number',
    ];
}
