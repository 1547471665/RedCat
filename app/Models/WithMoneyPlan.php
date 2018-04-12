<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class WithMoneyPlan extends Model
{
    protected $table = 'withmoneyplan';
    protected $fillable = ['number', 'last_id', 'start_time', 'invalid_time', 'status', 'dispense_invalid_time'];


}
