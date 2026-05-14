<?php

namespace App\Generators;

use App\Models\Entity;
use Illuminate\Support\Str;

class VueGenerator extends BaseGenerator
{
    public function generate(Entity $entity)
    {
        $folderName = $this->buildClassName($entity->name);
        $path       = resource_path("js/pages/{$folderName}");

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $this->generateIndex($entity, $path);
        $this->generateCreate($entity, $path);
        $this->generateEdit($entity, $path);
        $this->generateForm($entity, $path);
    }

    // ─────────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────────
    private function generateIndex(Entity $entity, string $path)
    {
        $stub             = $this->getStub('vue/index');
        $modelName        = $this->buildClassName($entity->name);
        $routeName        = Str::kebab($this->buildCollectionName($entity->name));
        $collectionVariable = $this->buildCollectionName($entity->name);

        $tableHeaders   = "";
        $tableColumns   = "";
        $relationProps  = "";
        $filterRefs     = "";
        $filterParams   = "";
        $filterWatch    = [];
        $filtersHtml    = "";

        $displayFields  = $entity->fields->take(5);
        $addedRelations = [];

        foreach ($displayFields as $field) {
            $isSortable = in_array($field->type, ['string', 'number', 'email', 'boolean', 'datetime', 'date']);
            $classes    = "px-6 py-4";
            $sortAttr   = "";

            if ($isSortable) {
                $classes  .= " cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors";
                $sortAttr  = " @click=\"toggleSort('{$field->column_name}')\"";
            }

            $tableHeaders .= "                            <th class=\"{$classes}\"{$sortAttr}>\n";
            $tableHeaders .= "                                <div class=\"flex items-center gap-1\">\n";
            $tableHeaders .= "                                    {$field->name}\n";
            if ($isSortable) {
                $tableHeaders .= "                                    <span v-if=\"sortBy === '{$field->column_name}'\">\n";
                $tableHeaders .= "                                        <template v-if=\"sortDirection === 'asc'\">↑</template>\n";
                $tableHeaders .= "                                        <template v-else>↓</template>\n";
                $tableHeaders .= "                                    </span>\n";
            }
            $tableHeaders .= "                                </div>\n";
            $tableHeaders .= "                            </th>\n";

            if ($field->type === 'relation' && $field->relationship_type === 'BelongsTo') {
                $relName         = Str::camel(Str::singular($field->relatedEntity->name));
                $displayField    = $field->display_field ?: 'name';
                $tableColumns   .= "                            <td class=\"px-6 py-4\">{{ item.{$relName} ? item.{$relName}.{$displayField} ?? item.{$relName}.id : '-' }}</td>\n";

                $relatedCollection = Str::camel(Str::plural(lcfirst($this->buildClassName($field->relatedEntity->name))));

                if (! in_array($relatedCollection, $addedRelations)) {
                    $relationProps   .= "    {$relatedCollection}: { type: Array, default: () => [] },\n";
                    $addedRelations[]  = $relatedCollection;
                }

                $filterRefs    .= "const {$field->column_name} = ref(props.filters.{$field->column_name} || '');\n";
                $filterParams  .= "        {$field->column_name}: {$field->column_name}.value,\n";
                $filterWatch[]  = "{$field->column_name}";

                $filtersHtml   .= "                <div class=\"min-w-[150px]\">\n";
                $filtersHtml   .= "                    <select v-model=\"{$field->column_name}\" class=\"w-full rounded-md border px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-700\">\n";
                $filtersHtml   .= "                        <option value=\"\">All {$field->name}</option>\n";
                $filtersHtml   .= "                        <option v-for=\"opt in {$relatedCollection}\" :key=\"opt.id\" :value=\"opt.id\">{{ opt.{$displayField} ?? opt.id }}</option>\n";
                $filtersHtml   .= "                    </select>\n";
                $filtersHtml   .= "                </div>\n";

            } elseif ($field->type === 'relation' && $field->relationship_type === 'BelongsToMany') {
                $relName       = Str::camel(Str::plural($field->relatedEntity->name));
                $displayField  = $field->display_field ?: 'name';
                $tableColumns .= "                            <td class=\"px-6 py-4\">\n";
                $tableColumns .= "                                <span v-if=\"item.{$relName} && item.{$relName}.length\">\n";
                $tableColumns .= "                                    {{ item.{$relName}.map(r => r.{$displayField} ?? r.id).join(', ') }}\n";
                $tableColumns .= "                                </span>\n";
                $tableColumns .= "                                <span v-else>—</span>\n";
                $tableColumns .= "                            </td>\n";

            } else {
                $tableColumns .= "                            <td class=\"px-6 py-4\">{{ item.{$field->column_name} }}</td>\n";

                if ($field->type === 'boolean') {
                    $filterRefs   .= "const {$field->column_name} = ref(props.filters.{$field->column_name} || '');\n";
                    $filterParams .= "        {$field->column_name}: {$field->column_name}.value,\n";
                    $filterWatch[] = "{$field->column_name}";

                    $filtersHtml  .= "                <div class=\"min-w-[150px]\">\n";
                    $filtersHtml  .= "                    <select v-model=\"{$field->column_name}\" class=\"w-full rounded-md border px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-700\">\n";
                    $filtersHtml  .= "                        <option value=\"\">All {$field->name}</option>\n";
                    $filtersHtml  .= "                        <option value=\"1\">Yes</option>\n";
                    $filtersHtml  .= "                        <option value=\"0\">No</option>\n";
                    $filtersHtml  .= "                    </select>\n";
                    $filtersHtml  .= "                </div>\n";
                }
            }
        }

        $content = str_replace(
            [
                '{{ modelName }}',
                '{{ routeName }}',
                '{{ collectionVariable }}',
                '{{ tableHeaders }}',
                '{{ tableColumns }}',
                '{{ relationProps }}',
                '{{ filterRefs }}',
                '{{ filterParams }}',
                '{{ filterWatch }}',
                '{{ filtersHtml }}',
            ],
            [
                $modelName,
                $routeName,
                $collectionVariable,
                trim($tableHeaders),
                trim($tableColumns),
                trim($relationProps),
                trim($filterRefs),
                trim($filterParams),
                count($filterWatch) > 0 ? ", " . implode(", ", $filterWatch) : "",
                trim($filtersHtml),
            ],
            $stub
        );

        file_put_contents("{$path}/Index.vue", $content);
    }

    // ─────────────────────────────────────────────────────────────────
    // CREATE
    // ─────────────────────────────────────────────────────────────────
    private function generateCreate(Entity $entity, string $path)
    {
        $stub      = $this->getStub('vue/create');
        $modelName = $this->buildClassName($entity->name);
        $routeName = Str::kebab($this->buildCollectionName($entity->name));

        // Build relation props for Create page (needs the collection dropdowns)
        $relationProps = $this->buildRelationPropsStr($entity);

        $content = str_replace(
            ['{{ modelName }}', '{{ routeName }}', '{{ relationProps }}'],
            [$modelName, $routeName, $relationProps],
            $stub
        );

        file_put_contents("{$path}/Create.vue", $content);
    }

    // ─────────────────────────────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────────────────────────────
    private function generateEdit(Entity $entity, string $path)
    {
        $stub          = $this->getStub('vue/edit');
        $modelName     = $this->buildClassName($entity->name);
        $routeName     = Str::kebab($this->buildCollectionName($entity->name));
        $modelVariable = $this->buildVariableName($entity->name);
        $relationProps = $this->buildRelationPropsStr($entity);

        $content = str_replace(
            ['{{ modelName }}', '{{ routeName }}', '{{ modelVariable }}', '{{ relationProps }}'],
            [$modelName, $routeName, $modelVariable, $relationProps],
            $stub
        );

        file_put_contents("{$path}/Edit.vue", $content);
    }

    // ─────────────────────────────────────────────────────────────────
    // FORM
    // ─────────────────────────────────────────────────────────────────
    private function generateForm(Entity $entity, string $path)
    {
        $stub          = $this->getStub('vue/form');
        $modelVariable = $this->buildVariableName($entity->name);
        $routeName     = Str::kebab($this->buildCollectionName($entity->name));
        $relationProps = $this->buildRelationPropsStr($entity);

        $formInit   = "{\n";
        $fieldsHtml = "";

        foreach ($entity->fields as $field) {

            // ── Default value ────────────────────────────────────
            $default = "''";
            if ($field->type === 'boolean') {
                $default = ($field->default_value === 'true' || $field->default_value === '1') ? 'true' : 'false';
            } elseif ($field->type === 'integer' || $field->type === 'number' || $field->type === 'decimal') {
                $default = $field->default_value !== null ? $field->default_value : 'null';
            } elseif ($field->type === 'relation') {
                $default = $field->relationship_type === 'BelongsToMany' ? '[]' : 'null';
            } elseif ($field->default_value !== null) {
                $default = "'{$field->default_value}'";
            }

            // BelongsToMany initialise from existing pivot IDs
            if ($field->type === 'relation' && $field->relationship_type === 'BelongsToMany' && $field->relatedEntity) {
                $inputKey      = Str::snake($field->relatedEntity->name) . '_ids';
                $methodNameMany = Str::camel(Str::plural($field->relatedEntity->name));
                $formInit     .= "    {$inputKey}: props.{$modelVariable} ? props.{$modelVariable}.{$methodNameMany}?.map(r => r.id) ?? [] : [],\n";
            } elseif ($field->type !== 'relation') {
                $formInit .= "    {$field->column_name}: props.{$modelVariable} ? props.{$modelVariable}.{$field->column_name} : {$default},\n";
            } else {
                // BelongsTo
                $formInit .= "    {$field->column_name}: props.{$modelVariable} ? props.{$modelVariable}.{$field->column_name} : {$default},\n";
            }

            // ── Field HTML ───────────────────────────────────────
            $fieldsHtml .= "        <div class=\"mb-4\">\n";
            $fieldsHtml .= "            <label class=\"block text-sm font-medium text-gray-700 dark:text-gray-300\">{$field->name}</label>\n";

            if ($field->type === 'textarea') {
                $fieldsHtml .= "            <textarea v-model=\"form.{$field->column_name}\" class=\"mt-1 w-full rounded-md border px-3 py-2 dark:bg-gray-800\"></textarea>\n";

            } elseif ($field->type === 'boolean') {
                $fieldsHtml .= "            <input type=\"checkbox\" v-model=\"form.{$field->column_name}\" class=\"mt-2\" />\n";

            } elseif ($field->type === 'file') {
                $fieldsHtml .= "            <input type=\"file\" @change=\"e => form.{$field->column_name} = e.target.files[0]\" class=\"mt-2\" />\n";

            } elseif ($field->type === 'relation' && $field->relationship_type === 'BelongsTo' && $field->relatedEntity) {
                $relatedCollection = Str::camel(Str::plural(lcfirst($this->buildClassName($field->relatedEntity->name))));
                $displayField      = $field->display_field ?: 'name';

                $fieldsHtml .= "            <select v-model=\"form.{$field->column_name}\" class=\"mt-1 w-full rounded-md border px-3 py-2 dark:bg-gray-800\">\n";
                $fieldsHtml .= "                <option :value=\"null\">Select {$field->name}</option>\n";
                $fieldsHtml .= "                <option v-for=\"item in {$relatedCollection}\" :key=\"item.id\" :value=\"item.id\">{{ item.{$displayField} ?? item.id }}</option>\n";
                $fieldsHtml .= "            </select>\n";
                $fieldsHtml .= "            <p class=\"text-xs text-gray-400 mt-1\">Stored value: <strong>id</strong> · Displayed: <strong>{$displayField}</strong></p>\n";

            } elseif ($field->type === 'relation' && $field->relationship_type === 'BelongsToMany' && $field->relatedEntity) {
                $inputKey          = Str::snake($field->relatedEntity->name) . '_ids';
                $relatedCollection = Str::camel(Str::plural(lcfirst($this->buildClassName($field->relatedEntity->name))));
                $displayField      = $field->display_field ?: 'name';

                $fieldsHtml .= "            <!-- Multi-select for BelongsToMany -->\n";
                $fieldsHtml .= "            <select v-model=\"form.{$inputKey}\" multiple class=\"mt-1 w-full rounded-md border px-3 py-2 dark:bg-gray-800 min-h-[120px]\">\n";
                $fieldsHtml .= "                <option v-for=\"item in {$relatedCollection}\" :key=\"item.id\" :value=\"item.id\">{{ item.{$displayField} ?? item.id }}</option>\n";
                $fieldsHtml .= "            </select>\n";
                $fieldsHtml .= "            <p class=\"text-xs text-gray-400 mt-1\">Hold Ctrl/Cmd to select multiple. Values: <strong>ids</strong> · Label: <strong>{$displayField}</strong></p>\n";

            } elseif ($field->type === 'select' || $field->type === 'enum') {
                $fieldsHtml .= "            <select v-model=\"form.{$field->column_name}\" class=\"mt-1 w-full rounded-md border px-3 py-2 dark:bg-gray-800\">\n";
                $fieldsHtml .= "                <option value=\"\">Select {$field->name}</option>\n";
                $options     = is_string($field->options)
                    ? json_decode($field->options, true)
                    : ($field->options ?? []);
                foreach ($options as $opt) {
                    $fieldsHtml .= "                <option value=\"{$opt}\">{$opt}</option>\n";
                }
                $fieldsHtml .= "            </select>\n";

            } else {
                $inputType   = match ($field->type) {
                    'number', 'integer', 'bigInteger', 'decimal' => 'number',
                    'email'    => 'email',
                    'date'     => 'date',
                    'dateTime' => 'datetime-local',
                    default    => 'text',
                };
                $fieldsHtml .= "            <input type=\"{$inputType}\" v-model=\"form.{$field->column_name}\" class=\"mt-1 w-full rounded-md border px-3 py-2 dark:bg-gray-800\" />\n";
            }

            $fieldsHtml .= "            <p v-if=\"form.errors.{$field->column_name}\" class=\"text-red-500 text-sm mt-1\">{{ form.errors.{$field->column_name} }}</p>\n";
            $fieldsHtml .= "        </div>\n\n";
        }

        $formInit .= "}";

        $content = str_replace(
            ['{{ modelVariable }}', '{{ routeName }}', '{{ formInit }}', '{{ fieldsHtml }}', '{{ relationProps }}'],
            [$modelVariable, $routeName, $formInit, trim($fieldsHtml), trim($relationProps)],
            $stub
        );

        file_put_contents("{$path}/Form.vue", $content);
    }

    // ─────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────

    /**
     * Build the defineProps string for relation collections
     * (both BelongsTo and BelongsToMany need the collection).
     */
    private function buildRelationPropsStr(Entity $entity): string
    {
        $props       = "";
        $addedProps  = [];

        foreach ($entity->fields as $field) {
            if ($field->type !== 'relation' || ! $field->relatedEntity) {
                continue;
            }
            $relatedCollection = Str::camel(Str::plural(lcfirst($this->buildClassName($field->relatedEntity->name))));
            if (! in_array($relatedCollection, $addedProps)) {
                $props         .= "    {$relatedCollection}: { type: Array, default: () => [] },\n";
                $addedProps[]   = $relatedCollection;
            }
        }

        return trim($props);
    }
}
