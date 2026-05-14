<?php

namespace App\Generators;

use App\Models\Entity;
use Illuminate\Support\Str;

class ModelGenerator extends BaseGenerator
{
    public function generate(Entity $entity)
    {
        $stub      = $this->getStub('model');
        $className = $this->buildClassName($entity->name);

        $fillable      = [];
        $relationships = "";

        foreach ($entity->fields as $field) {

            if ($field->type !== 'relation') {
                $fillable[] = "'{$field->column_name}'";
                continue;
            }

            if (! $field->relatedEntity) {
                continue;
            }

            $relatedClass = $this->buildClassName($field->relatedEntity->name);
            $methodName   = Str::camel(Str::singular($field->relatedEntity->name));

            switch ($field->relationship_type) {

                case 'BelongsTo':
                    $fillable[]    = "'{$field->column_name}'";
                    $fk             = $field->foreign_key ?: $field->column_name;
                    $relationships .= "    public function {$methodName}()\n    {\n";
                    $relationships .= "        return \$this->belongsTo({$relatedClass}::class, '{$fk}');\n";
                    $relationships .= "    }\n\n";
                    break;

                case 'BelongsToMany':
                    $pivotTable     = $field->pivot_table
                        ?: collect([$entity->table_name, $field->relatedEntity->table_name])
                            ->sort()->implode('_');
                    $methodNameMany = Str::camel(Str::plural($field->relatedEntity->name));
                    $relationships .= "    public function {$methodNameMany}()\n    {\n";
                    $relationships .= "        return \$this->belongsToMany({$relatedClass}::class, '{$pivotTable}');\n";
                    $relationships .= "    }\n\n";
                    // BelongsToMany IDs are stored in pivot — no fillable column
                    break;

                case 'HasMany':
                    $methodNameMany = Str::camel(Str::plural($field->relatedEntity->name));
                    $fk             = $field->foreign_key ?: Str::snake($entity->name) . '_id';
                    $relationships .= "    public function {$methodNameMany}()\n    {\n";
                    $relationships .= "        return \$this->hasMany({$relatedClass}::class, '{$fk}');\n";
                    $relationships .= "    }\n\n";
                    break;

                case 'HasOne':
                    $fk             = $field->foreign_key ?: Str::snake($entity->name) . '_id';
                    $relationships .= "    public function {$methodName}()\n    {\n";
                    $relationships .= "        return \$this->hasOne({$relatedClass}::class, '{$fk}');\n";
                    $relationships .= "    }\n\n";
                    break;
            }
        }

        $fillableStr       = implode(",\n        ", $fillable);
        $softDeletesImport = $entity->soft_deletes ? "use Illuminate\\Database\\Eloquent\\SoftDeletes;" : "";
        $softDeletesUse    = $entity->soft_deletes ? "use SoftDeletes;" : "";

        $content = str_replace(
            ['{{ className }}', '{{ fillable }}', '{{ relationships }}', '{{ softDeletesImport }}', '{{ softDeletesUse }}'],
            [$className, $fillableStr, trim($relationships), $softDeletesImport, $softDeletesUse],
            $stub
        );

        file_put_contents(app_path("Models/{$className}.php"), $content);
    }
}
