<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = ['category', 'category_color', 'title', 'excerpt', 'date', 'image', 'image_alt', 'sort_order'];
}
