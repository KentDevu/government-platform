<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgencyGroup extends Model
{
    protected $fillable = ['category', 'sort_order'];

    public function agencies()
    {
        return $this->hasMany(Agency::class)->orderBy('sort_order');
    }
}
