<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PressRelease extends Model
{
    protected $fillable = ['source', 'title', 'url', 'sort_order'];
}
