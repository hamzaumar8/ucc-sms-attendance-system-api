<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleBank extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'code',
        'credit_hour',
    ];
}