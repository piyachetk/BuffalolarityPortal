<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Alias extends Model
{
    protected $table = 'aliases';
    protected $fillable = ['alias', 'id'];
    public $incrementing = false;
    public $timestamps = false;
}
