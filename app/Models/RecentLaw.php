<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecentLaw extends Model
{
    protected $fillable = ['number', 'title', 'description', 'status', 'sort_order'];
}
