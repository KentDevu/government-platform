<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    protected $fillable = ['icon', 'title', 'department_id', 'description', 'cta', 'color', 'url', 'page', 'sort_order'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
