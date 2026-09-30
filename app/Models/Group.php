<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['group_family_id', 'name', 'description'];

    public function family()
    {
        return $this->belongsTo(GroupFamily::class, 'group_family_id');
    }
}