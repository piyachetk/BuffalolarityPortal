<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'projects';
    protected $fillable = ['id', 'name', 'short_description', 'full_description', 'main_url', 'banner', 'icon'];
    public $incrementing = false;
    public $timestamps = false;
}
