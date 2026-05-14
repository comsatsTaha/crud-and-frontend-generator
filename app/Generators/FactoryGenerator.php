<?php

namespace App\Generators;

use App\Models\Entity;
use Illuminate\Support\Str;

class FactoryGenerator extends BaseGenerator
{
    public function generate(Entity $entity)
    {
        $stub = $this->getStub('factory');

        $modelName = Str::studly($entity->name);
        $factoryName = $modelName . 'Factory';

        $fields = "";

        foreach ($entity->fields as $field) {
            $fields .= $this->buildFactoryField($field) . "\n            ";
        }

        $content = str_replace(
            ['{{ modelName }}', '{{ factoryName }}', '{{ fields }}'],
            [$modelName, $factoryName, trim($fields)],
            $stub
        );

        $path = database_path("factories/{$factoryName}.php");

        file_put_contents($path, $content);
    }

    private function buildFactoryField($field)
    {
        $name = $field->column_name;

        if (in_array($name, ['id', 'created_at', 'updated_at'])) {
            return "";
        }

        // Relation
        if ($field->type === 'relation' && $field->relationship_type === 'BelongsTo') {
            $relatedModel = \Illuminate\Support\Str::studly($field->relatedEntity->name);
            return "'{$name}' => \\App\\Models\\{$relatedModel}::factory(),";
        }

        switch ($field->type) {

            case 'text': // short string
                return "'{$name}' => fake()->paragraph(),";

            case 'textarea': // long text
                return "'{$name}' => fake()->paragraph(),";

            case 'email':
                return "'{$name}' => fake()->safeEmail(),";

            case 'integer':
                return "'{$name}' => fake()->numberBetween(1, 1000),";

            case 'boolean':
                return "'{$name}' => fake()->boolean(),";

            case 'date':
                return "'{$name}' => fake()->date(),";

            case 'file':
                return "'{$name}' => fake()->imageUrl(),";

            case 'select':
                if (!empty($field->options)) {
                    $options = json_decode($field->options, true);
                    $optionsArray = implode("','", $options);
                    return "'{$name}' => fake()->randomElement(['{$optionsArray}']),";
                }
                return "'{$name}' => fake()->word(),";

            default:
                return "'{$name}' => fake()->word(),";
        }
    }
}
