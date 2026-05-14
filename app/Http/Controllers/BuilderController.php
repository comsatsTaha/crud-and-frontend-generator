<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entity;
use Inertia\Inertia;

class BuilderController extends Controller
{
    public function index()
    {
        return Inertia::render('Builder/Index', [
            'entities' => Entity::latest()->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Builder/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'table_name' => 'required|string|max:255|unique:entities,table_name',
            'icon' => 'nullable|string|max:255',
            'soft_deletes' => 'boolean',
        ]);

        $entity = Entity::create([
            'name' => $validated['name'],
            'table_name' => $validated['table_name'],
            'icon' => $validated['icon'] ?? null,
            'soft_deletes' => $validated['soft_deletes'] ?? false,
        ]);

        return redirect()
            ->route('builder.edit', $entity->id)
            ->with('success', 'Entity created successfully.');
    }

 public function edit(Entity $entity)
{
    $entity->load('fields.relatedEntity');

    // 🔥 Normalize options before sending to Vue
    $entity->fields->transform(function ($field) {

        if (!empty($field->options)) {

            // if string → decode JSON
            if (is_string($field->options)) {
                $decoded = json_decode($field->options, true);
                $field->options = is_array($decoded) ? $decoded : [];
            }

            // if already array → keep it
            if (!is_array($field->options)) {
                $field->options = [];
            }

        } else {
            $field->options = [];
        }

        return $field;
    });

    return Inertia::render('Builder/Edit', [
        'entity' => $entity,
        'allEntities' => Entity::select('id', 'name')->get()
    ]);
}

    public function update(Request $request, Entity $entity)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'table_name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'soft_deletes' => 'boolean',
            'fields' => 'array',

            // 🔥 Field level validation
            'fields.*.name' => 'required|string|max:255',
            'fields.*.column_name' => 'required|string|max:255',
            'fields.*.type' => 'required|string',
            'fields.*.validation_rules' => 'nullable|string',
            'fields.*.is_nullable' => 'boolean',
            'fields.*.default_value' => 'nullable|string',

            // relation
            'fields.*.related_entity_id' => 'nullable|exists:entities,id',
            'fields.*.relationship_type' => 'nullable|string',
            'fields.*.foreign_key' => 'nullable|string',
            'fields.*.display_field' => 'nullable|string|max:255',
            'fields.*.pivot_table' => 'nullable|string|max:255',

            // 🔥 NEW: select options
            'fields.*.options' => 'nullable|array',
            'fields.*.options.*' => 'nullable|string',
        ]);

        // ✅ Update entity
        $entity->update([
            'name' => $validated['name'],
            'table_name' => $validated['table_name'],
            'icon' => $validated['icon'] ?? null,
            'soft_deletes' => $validated['soft_deletes'] ?? false,
        ]);

        // ✅ Sync fields
        $existingIds = collect($validated['fields'] ?? [])
            ->pluck('id')
            ->filter()
            ->toArray();

        // delete removed fields
        $entity->fields()->whereNotIn('id', $existingIds)->delete();

        foreach ($validated['fields'] ?? [] as $index => $fieldData) {

            // 🔥 Clean options (remove empty values)
            $options = collect($fieldData['options'] ?? [])
                ->filter()
                ->values()
                ->toArray();

            $entity->fields()->updateOrCreate(
                ['id' => $fieldData['id'] ?? null],
                [
                    'name' => $fieldData['name'],
                    'column_name' => $fieldData['column_name'],
                    'type' => $fieldData['type'],
                    'validation_rules' => $fieldData['validation_rules'] ?? null,
                    'is_nullable' => $fieldData['is_nullable'] ?? false,
                    'default_value' => $fieldData['default_value'] ?? null,
                    'order' => $index,

                    // relations
                    'related_entity_id' => $fieldData['related_entity_id'] ?? null,
                    'relationship_type' => $fieldData['relationship_type'] ?? null,
                    'foreign_key' => $fieldData['foreign_key'] ?? null,
                    'display_field' => $fieldData['display_field'] ?? null,
                    'pivot_table' => $fieldData['pivot_table'] ?? null,

                    // 🔥 NEW: save options as JSON
                    'options' => !empty($options) ? json_encode($options) : null,
                ]
            );
        }

        return back()->with('success', 'Entity updated successfully.');
    }

    public function destroy(Entity $entity)
    {
        $entity->delete();

        return redirect()
            ->route('builder.index')
            ->with('success', 'Entity deleted.');
    }

    /**
     * Returns the field names of a given entity so the frontend
     * can offer a "display field" picker for relationships.
     */
    public function entityFields(Entity $entity)
    {
        $fields = $entity->fields
            ->where('type', '!=', 'relation')
            ->values()
            ->map(fn ($f) => [
                'column_name' => $f->column_name,
                'name'        => $f->name,
            ]);

        return response()->json($fields);
    }
}