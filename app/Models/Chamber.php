<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chamber extends Model
{
    protected $fillable = ['name', 'leader', 'icon', 'description', 'members', 'location', 'sort_order'];
}
