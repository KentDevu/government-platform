<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    protected $fillable = ['agency_group_id', 'name', 'acronym', 'icon', 'url', 'sort_order'];

    public function group()
    {
        return $this->belongsTo(AgencyGroup::class, 'agency_group_id');
    }
}
