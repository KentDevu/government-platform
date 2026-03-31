<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecentLaw extends Model
{
    use HasFactory;

    protected $fillable = ['number', 'title', 'description', 'status', 'sort_order'];
}
