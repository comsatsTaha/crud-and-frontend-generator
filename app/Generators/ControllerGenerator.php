<?php

namespace App\Generators;

use App\Models\Entity;
use Illuminate\Support\Str;

class ControllerGenerator extends BaseGenerator
{
    public function generate(Entity $entity)
    {
        $stub = $this->getStub('controller');

        $modelClass       = $this->buildClassName($entity->name);
        $modelVariable    = $this->buildVariableName($entity->name);
        $collectionVariable = $this->buildCollectionName($entity->name);
        $controllerName   = $modelClass . 'Controller';

        $relationsToLoad  = [];
        $searchLogic      = "";
        $filterLogic      = "";
        $filterFields     = [];

        foreach ($entity->fields as $index => $field) {
            if ($field->type === 'relation' && in_array($field->relationship_type, ['BelongsTo', 'HasOne'])) {
                $relationName = Str::camel(str_replace('_id', '', $field->column_name));
                $relationsToLoad[] = "'{$relationName}'";
            }

            if (in_array($field->type, ['string', 'email', 'textarea'])) {
                $method       = ($index === 0) ? "where" : "orWhere";
                $searchLogic .= "                \$q->{$method}('{$field->column_name}', 'like', '%' . \$request->search . '%');\n";
            }

            if (in_array($field->type, ['select', 'boolean'])) {
                $filterFields[] = "'{$field->column_name}'";
                $filterLogic   .= "        if (\$request->{$field->column_name}) {\n";
                $filterLogic   .= "            \$query->where('{$field->column_name}', \$request->{$field->column_name});\n";
                $filterLogic   .= "        }\n";
            }

            // BelongsTo filter
            if ($field->type === 'relation' && $field->relationship_type === 'BelongsTo') {
                $filterFields[] = "'{$field->column_name}'";
                $filterLogic   .= "        if (\$request->{$field->column_name}) {\n";
                $filterLogic   .= "            \$query->where('{$field->column_name}', \$request->{$field->column_name});\n";
                $filterLogic   .= "        }\n";
            }
        }

        $withRelations = count($relationsToLoad) > 0
            ? "with([" . implode(", ", $relationsToLoad) . "])->"
            : "";

        // Related data to pass to create/edit views
        $relatedData = "";
        foreach ($entity->fields as $field) {
            if ($field->type !== 'relation' || ! $field->relatedEntity) {
                continue;
            }
            $relatedClass = $this->buildClassName($field->relatedEntity->name);
            $relatedVar   = Str::camel(Str::plural($relatedClass));

            // Use display_field for efficient selection
            $displayField = $field->display_field ?: 'id';
            $relatedData .= "'{$relatedVar}' => \\App\\Models\\{$relatedClass}::select('id', '{$displayField}')->get(),\n            ";
        }

        $fileHandling = "";
        foreach ($entity->fields as $field) {
            if ($field->type === 'file') {
                $fileHandling .= "\n        if (\$request->hasFile('{$field->column_name}')) {\n";
                $fileHandling .= "            \$data['{$field->column_name}'] = \$request->file('{$field->column_name}')->store('uploads', 'public');\n";
                $fileHandling .= "        }\n        ";
            }
        }

        // BelongsToMany sync logic for store/update
        $manyToManySync  = "";
        $relationsToLoadForEdit = [];

        foreach ($entity->fields as $field) {
            if ($field->type === 'relation' && $field->relationship_type === 'BelongsToMany' && $field->relatedEntity) {
                $methodName      = Str::camel(Str::plural($field->relatedEntity->name));
                $inputKey        = Str::snake($field->relatedEntity->name) . '_ids';

                $manyToManySync .= "\n        if (\$request->has('{$inputKey}')) {\n";
                $manyToManySync .= "            \${$modelVariable}->{$methodName}()->sync(\$request->{$inputKey});\n";
                $manyToManySync .= "        }\n";

                $relationsToLoadForEdit[] = "'{$methodName}'";
            }
        }

        $loadRelations = count($relationsToLoadForEdit) > 0
            ? "        \${$modelVariable}->load([" . implode(", ", $relationsToLoadForEdit) . "]);"
            : "";

        $content = str_replace(
            [
                '{{ modelClass }}',
                '{{ modelVariable }}',
                '{{ collectionVariable }}',
                '{{ controllerName }}',
                '{{ withRelations }}',
                '{{ fileHandling }}',
                '{{ searchLogic }}',
                '{{ filterLogic }}',
                '{{ filterFields }}',
                '{{ relatedData }}',
                '{{ manyToManySync }}',
                '{{ loadRelations }}',
            ],
            [
                $modelClass,
                $modelVariable,
                $collectionVariable,
                $controllerName,
                $withRelations,
                $fileHandling,
                trim($searchLogic),
                trim($filterLogic),
                implode(", ", $filterFields),
                trim($relatedData),
                $manyToManySync,
                $loadRelations,
            ],
            $stub
        );

        file_put_contents(app_path("Http/Controllers/{$controllerName}.php"), $content);
    }
}
