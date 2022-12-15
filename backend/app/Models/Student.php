<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id',
        'index_number',
        'first_name',
        'other_name',
        'last_name',
        'gender',
        'phone',
        'picture',
    ];

    // Optional zone open
    public function id(): string
    {
        return $this->id;
    }

    public function index_number(): string
    {
        return $this->index_number;
    }

    public function first_name(): string
    {
        return $this->first_name;
    }

    public function other_name()
    {
        return $this->other_name;
    }

    public function last_name(): string
    {
        return $this->last_name;
    }

    public function gender(): string
    {
        return $this->gender;
    }

    public function phone()
    {
        return $this->phone;
    }

    public function picture()
    {
        return $this->picture;
    }

    // optional zone close

    public function full_name()
    {
        return $this->other_name ? $this->first_name . ' ' . $this->other_name . ' ' . $this->last_name : $this->first_name . ' ' . $this->last_name;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}