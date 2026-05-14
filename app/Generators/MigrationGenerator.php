<?php

namespace App\Generators;

use App\Models\Entity;
use Illuminate\Support\Str;

class MigrationGenerator extends BaseGenerator
{
    public function generate(Entity $entity)
    {
        // ── 1. Main table migration ──────────────────────────────────
        $stub = $this->getStub('migration');

        $className = 'Create' . Str::studly($entity->table_name) . 'Table';
        $tableName = $entity->table_name;

        $fields = "";
        foreach ($entity->fields as $field) {
            $line = $this->buildFieldSchema($field);
            if ($line) {
                $fields .= $line . "\n            ";
            }
        }

        if ($entity->soft_deletes) {
            $fields .= '$table->softDeletes();' . "\n            ";
        }

        $content = str_replace(
            ['{{ className }}', '{{ tableName }}', '{{ fields }}'],
            [$className, $tableName, trim($fields)],
            $stub
        );

        $timestamp = date('Y_m_d_His');
        $fileName  = "{$timestamp}_create_{$tableName}_table.php";
        file_put_contents(database_path("migrations/{$fileName}"), $content);

        // ── 2. Pivot table migrations for BelongsToMany ─────────────
        sleep(1); // ensure unique timestamps
        foreach ($entity->fields as $field) {
            if ($field->type === 'relation'
                && $field->relationship_type === 'BelongsToMany'
                && $field->relatedEntity
            ) {
                $this->generatePivotMigration($entity, $field);
            }
        }
    }

    private function generatePivotMigration($entity, $field)
    {
        $pivotTable = $field->pivot_table
            ?: collect([$entity->table_name, $field->relatedEntity->table_name])->sort()->implode('_');

        $modelA    = Str::singular($entity->table_name);
        $modelB    = Str::singular($field->relatedEntity->table_name);
        $timestamp = date('Y_m_d_His');

        $content = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$pivotTable}', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('{$modelA}_id')->constrained('{$entity->table_name}')->cascadeOnDelete();
            \$table->foreignId('{$modelB}_id')->constrained('{$field->relatedEntity->table_name}')->cascadeOnDelete();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$pivotTable}');
    }
};
PHP;

        $fileName = "{$timestamp}_create_{$pivotTable}_table.php";
        file_put_contents(database_path("migrations/{$fileName}"), $content);
    }

    private function buildFieldSchema($field): ?string
    {
        // BelongsToMany → pivot migration handles this, no column here
        if ($field->type === 'relation' && $field->relationship_type === 'BelongsToMany') {
            return null;
        }

        // BelongsTo / HasOne (owning side) → foreignId column
        if ($field->type === 'relation' && in_array($field->relationship_type, ['BelongsTo', 'HasOne'])) {
            $col    = $field->column_name ?: Str::snake($field->relatedEntity->name) . '_id';
            $schema = "\$table->foreignId('{$col}')";
            if ($field->is_nullable) {
                $schema .= "->nullable()->constrained('{$field->relatedEntity->table_name}')->nullOnDelete()";
            } else {
                $schema .= "->constrained('{$field->relatedEntity->table_name}')->cascadeOnDelete()";
            }
            return $schema . ";";
        }

        // HasMany (no column on this table)
        if ($field->type === 'relation') {
            return null;
        }

        $schema = '$table->';

        switch ($field->type) {
            case 'text':
            case 'textarea':
                $schema .= "text('{$field->column_name}')";
                break;
            case 'email':
            case 'file':
            case 'select':
            case 'string':
                $schema .= "string('{$field->column_name}')";
                break;
            case 'integer':
            case 'number':
                $schema .= "integer('{$field->column_name}')";
                break;
            case 'bigInteger':
                $schema .= "bigInteger('{$field->column_name}')";
                break;
            case 'boolean':
                $schema .= "boolean('{$field->column_name}')";
                break;
            case 'date':
                $schema .= "date('{$field->column_name}')";
                break;
            case 'dateTime':
                $schema .= "dateTime('{$field->column_name}')";
                break;
            case 'decimal':
                $schema .= "decimal('{$field->column_name}', 10, 2)";
                break;
            case 'json':
                $schema .= "json('{$field->column_name}')";
                break;
            case 'uuid':
                $schema .= "uuid('{$field->column_name}')";
                break;
            case 'enum':
                $opts   = collect($field->options ?? [])->map(fn ($o) => "'{$o}'")->implode(', ');
                $schema .= "enum('{$field->column_name}', [{$opts}])";
                break;
            case 'foreignId':
                $schema .= "foreignId('{$field->column_name}')->constrained()->cascadeOnDelete()";
                break;
            default:
                $schema .= "string('{$field->column_name}')";
        }

        if ($field->is_nullable) {
            $schema .= "->nullable()";
        }

        if ($field->default_value !== null && $field->default_value !== '') {
            if ($field->type === 'boolean') {
                $val = ($field->default_value === 'true' || $field->default_value === '1') ? 'true' : 'false';
            } else {
                $val = is_numeric($field->default_value)
                    ? $field->default_value
                    : "'{$field->default_value}'";
            }
            $schema .= "->default({$val})";
        }

        return $schema . ";";
    }
}
