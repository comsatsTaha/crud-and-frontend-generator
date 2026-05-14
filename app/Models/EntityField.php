<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntityField extends Model
{
    protected $casts = [
    'options' => 'array',
];
    protected $fillable = [
        'entity_id',
        'name',
        'column_name',
        'type',
        'validation_rules',
        'is_nullable',
        'default_value',
        'order',
        'related_entity_id',
        'relationship_type',
        'foreign_key',
        'display_field',
        'pivot_table',
        'options',
    ];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }
    
    public function relatedEntity()
    {
        return $this->belongsTo(Entity::class, 'related_entity_id');
    }
}
