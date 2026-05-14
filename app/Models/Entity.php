<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    protected $fillable = [
        'name',
        'table_name',
        'is_generated',
        'soft_deletes',
        'icon',
    ];

    public function fields()
    {
        return $this->hasMany(EntityField::class)->orderBy('order');
    }
}
