<?php

namespace App\Generators;

use App\Models\Entity;
use Illuminate\Support\Str;

class RequestGenerator extends BaseGenerator
{
    public function generate(Entity $entity)
    {
        $stub       = $this->getStub('request');
        $modelClass = $this->buildClassName($entity->name);

        $rules = [];

        foreach ($entity->fields as $field) {

            // ── BelongsToMany → array of IDs ───────────────────
            if ($field->type === 'relation' && $field->relationship_type === 'BelongsToMany') {
                if ($field->relatedEntity) {
                    $inputKey = Str::snake($field->relatedEntity->name) . '_ids';
                    $nullable = $field->is_nullable ? 'nullable' : 'required';
                    $rules[]  = "            '{$inputKey}' => '{$nullable}|array'";
                    $rules[]  = "            '{$inputKey}.*' => 'integer|exists:{$field->relatedEntity->table_name},id'";
                }
                continue;
            }

            // ── BelongsTo / HasOne → exists rule ───────────────
            if ($field->type === 'relation' && in_array($field->relationship_type, ['BelongsTo', 'HasOne'])) {
                if ($field->relatedEntity) {
                    $nullable = $field->is_nullable ? 'nullable' : 'required';
                    $rules[]  = "            '{$field->column_name}' => '{$nullable}|integer|exists:{$field->relatedEntity->table_name},id'";
                }
                continue;
            }

            // ── HasMany / HasOne inverse → no input rule ───────
            if ($field->type === 'relation') {
                continue;
            }

            // ── Regular fields ──────────────────────────────────
            $rule = $field->validation_rules ?: '';

            if (! $rule) {
                $rule = $field->is_nullable ? 'nullable' : 'required';

                if ($field->type === 'integer' || $field->type === 'number') $rule .= '|integer';
                if ($field->type === 'bigInteger')                            $rule .= '|integer';
                if ($field->type === 'decimal')                               $rule .= '|numeric';
                if ($field->type === 'email')                                 $rule .= '|email';
                if ($field->type === 'date')                                  $rule .= '|date';
                if ($field->type === 'dateTime')                              $rule .= '|date';
                if ($field->type === 'boolean')                               $rule .= '|boolean';
                if ($field->type === 'uuid')                                  $rule .= '|uuid';
                if ($field->type === 'file')                                  $rule .= '|file';
                if ($field->type === 'json')                                  $rule .= '|array';
            }

            $rules[] = "            '{$field->column_name}' => '{$rule}'";
        }

        $rulesStr = implode(",\n", $rules);

        // Store Request
        $storeContent = str_replace(
            ['{{ requestClass }}', '{{ rules }}'],
            ["Store{$modelClass}Request", $rulesStr],
            $stub
        );
        file_put_contents(app_path("Http/Requests/Store{$modelClass}Request.php"), $storeContent);

        // Update Request (all rules nullable for partial updates)
        $updateContent = str_replace(
            ['{{ requestClass }}', '{{ rules }}'],
            ["Update{$modelClass}Request", $rulesStr],
            $stub
        );
        file_put_contents(app_path("Http/Requests/Update{$modelClass}Request.php"), $updateContent);
    }
}
