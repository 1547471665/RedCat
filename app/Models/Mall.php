<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Mall extends Model
{
    protected $fillable = ['name', 'title', 'img', 'created_at', 'updated_at', 'money', 'status'];
}
