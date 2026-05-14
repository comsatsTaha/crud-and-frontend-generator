<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, onMounted, computed, watch } from 'vue';

const props = defineProps<{
    entity: any;
    allEntities: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Builder Dashboard', href: '/builder' },
    { title: `Edit ${props.entity.name}`, href: `/builder/${props.entity.id}/edit` },
];

const form = useForm({
    name: props.entity.name,
    table_name: props.entity.table_name,
    icon: props.entity.icon || '',
    soft_deletes: !!props.entity.soft_deletes,
    timestamps: props.entity.timestamps !== false,
    fields: [] as any[],
});

onMounted(() => {
    if (props.entity.fields && props.entity.fields.length > 0) {
        form.fields = props.entity.fields.map((f: any) => ({
            ...f,
            display_field: f.display_field || '',
            pivot_table: f.pivot_table || '',
        }));
        // Pre-fetch related entity fields for any existing relation fields
        form.fields.forEach((f: any) => {
            if (f.type === 'relation' && f.related_entity_id) {
                fetchRelatedFields(f.related_entity_id);
            }
        });
    }
});

// ─── Related Entity Field Fetching ────────────────────────────────
// Map of entityId → array of { column_name, name }
const relatedEntityFields = ref<Record<number, { column_name: string; name: string }[]>>({});
const fetchingFields = ref<Record<number, boolean>>({});

async function fetchRelatedFields(entityId: number) {
    if (!entityId || relatedEntityFields.value[entityId]) return;
    fetchingFields.value[entityId] = true;
    try {
        const res = await fetch(`/builder/${entityId}/fields`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        relatedEntityFields.value[entityId] = data;
    } catch (e) {
        relatedEntityFields.value[entityId] = [];
    } finally {
        fetchingFields.value[entityId] = false;
    }
}

function onRelatedEntityChange(field: any) {
    field.display_field = '';
    if (field.related_entity_id) {
        // Auto-generate pivot table name for BelongsToMany
        if (field.relationship_type === 'BelongsToMany') {
            const relEnt = props.allEntities.find((e: any) => e.id === field.related_entity_id);
            if (relEnt) {
                const names = [props.entity.name.toLowerCase(), relEnt.name.toLowerCase()].sort();
                field.pivot_table = names.join('_');
            }
        }
        fetchRelatedFields(field.related_entity_id);
    }
}

function onRelationshipTypeChange(field: any) {
    field.display_field = '';
    field.pivot_table = '';
    if (field.related_entity_id && field.relationship_type === 'BelongsToMany') {
        const relEnt = props.allEntities.find((e: any) => e.id === field.related_entity_id);
        if (relEnt) {
            const names = [props.entity.name.toLowerCase(), relEnt.name.toLowerCase()].sort();
            field.pivot_table = names.join('_');
        }
    }
}

// ─── Field Types Palette ───────────────────────────────────────────
const TYPES = [
    { type: 'string',    dot: '#4ecdc4', label: 'string' },
    { type: 'integer',   dot: '#6c63ff', label: 'integer' },
    { type: 'bigInteger',dot: '#818cf8', label: 'bigInteger' },
    { type: 'boolean',   dot: '#ffd166', label: 'boolean' },
    { type: 'text',      dot: '#4ecdc4', label: 'text' },
    { type: 'decimal',   dot: '#c084fc', label: 'decimal' },
    { type: 'date',      dot: '#f97316', label: 'date' },
    { type: 'dateTime',  dot: '#fb923c', label: 'dateTime' },
    { type: 'json',      dot: '#ff6b6b', label: 'json' },
    { type: 'enum',      dot: '#e879f9', label: 'enum' },
    { type: 'uuid',      dot: '#94a3b8', label: 'uuid' },
    { type: 'foreignId', dot: '#34d399', label: 'foreignId' },
    { type: 'email',     dot: '#38bdf8', label: 'email' },
    { type: 'file',      dot: '#fb7185', label: 'file' },
    { type: 'select',    dot: '#a78bfa', label: 'select' },
    { type: 'relation',  dot: '#34d399', label: 'relation' },
];

const TYPE_DOTS: Record<string, string> = Object.fromEntries(TYPES.map(t => [t.type, t.dot]));

// ─── Active Preview Tab ────────────────────────────────────────────
const activeTab = ref<'migration' | 'model' | 'factory'>('migration');

// ─── Drag & Drop State ─────────────────────────────────────────────
const dragSrcIndex = ref<number | null>(null);
const dragOverIndex = ref<number | null>(null);
const dragPaletteType = ref<string | null>(null);
const dropZoneActive = ref(false);

// ─── Field Operations ──────────────────────────────────────────────
let nextId = 100;

function addField(type: string) {
    const defaultName = type === 'foreignId' ? 'user_id' : type + '_field';
    form.fields.push({
        id: nextId++,
        name: defaultName,
        column_name: defaultName,
        type,
        validation_rules: '',
        is_nullable: false,
        default_value: '',
        related_entity_id: null,
        relationship_type: 'BelongsTo',
        foreign_key: '',
        display_field: '',
        pivot_table: '',
        options: [],
    });
}

function removeField(index: number) {
    form.fields.splice(index, 1);
}

function autoFillColumnName(field: any) {
    if (!field.column_name && field.name) {
        field.column_name = field.name.toLowerCase().replace(/\s+/g, '_');
    }
}

// ─── Drag from Palette ─────────────────────────────────────────────
function paletteDragStart(e: DragEvent, type: string) {
    dragPaletteType.value = type;
    dragSrcIndex.value = null;
    if (e.dataTransfer) e.dataTransfer.effectAllowed = 'copy';
}

// ─── Drag Rows to Reorder ──────────────────────────────────────────
function rowDragStart(e: DragEvent, index: number) {
    dragSrcIndex.value = index;
    dragPaletteType.value = null;
    if (e.dataTransfer) e.dataTransfer.effectAllowed = 'move';
}

function rowDragOver(e: DragEvent, index: number) {
    e.preventDefault();
    dragOverIndex.value = index;
}

function rowDragLeave() {
    dragOverIndex.value = null;
}

function rowDrop(e: DragEvent, targetIndex: number) {
    e.preventDefault();
    dragOverIndex.value = null;

    if (dragPaletteType.value) {
        addField(dragPaletteType.value);
        dragPaletteType.value = null;
        return;
    }

    if (dragSrcIndex.value !== null && dragSrcIndex.value !== targetIndex) {
        const items = [...form.fields];
        const [moved] = items.splice(dragSrcIndex.value, 1);
        items.splice(targetIndex, 0, moved);
        form.fields = items;
    }
    dragSrcIndex.value = null;
}

function dropZoneDragOver(e: DragEvent) {
    e.preventDefault();
    dropZoneActive.value = true;
}

function dropZoneDrop(e: DragEvent) {
    e.preventDefault();
    dropZoneActive.value = false;
    if (dragPaletteType.value) {
        addField(dragPaletteType.value);
        dragPaletteType.value = null;
    }
}

// ─── Code Generators ──────────────────────────────────────────────
function snakeCase(s: string) {
    return s.replace(/([A-Z])/g, '_$1').toLowerCase().replace(/^_/, '');
}

const tableName = computed(() =>
    form.table_name || snakeCase(form.name || 'model') + 's'
);

const migrationCode = computed(() => {
    const table = tableName.value;
    const lines: string[] = [];
    lines.push(`// database/migrations/create_${table}_table.php`);
    lines.push(`Schema::create('${table}', function (Blueprint $table) {`);
    lines.push(`    $table->id();`);
    form.fields.forEach((f: any) => {
        let line = `    $table->${f.type}('${f.column_name || f.name}')`;
        if (f.type === 'decimal') line = `    $table->decimal('${f.column_name || f.name}', 10, 2)`;
        if (f.type === 'enum')    line = `    $table->enum('${f.column_name || f.name}', [${(f.options||[]).map((o: string) => `'${o}'`).join(', ')}])`;
        if (f.type === 'foreignId') line = `    $table->foreignId('${f.column_name || f.name}')->constrained()->cascadeOnDelete()`;
        if (f.type === 'select')  line = `    $table->string('${f.column_name || f.name}')`;
        if (f.type === 'relation') return;
        if (f.is_nullable) line += `->nullable()`;
        if (f.default_value) line += `->default('${f.default_value}')`;
        lines.push(line + ';');
    });
    if (form.timestamps) lines.push(`    $table->timestamps();`);
    if (form.soft_deletes) lines.push(`    $table->softDeletes();`);
    lines.push(`});`);
    return lines.join('\n');
});

const modelCode = computed(() => {
    const modelName = form.name || 'Model';
    const fillable = form.fields
        .filter((f: any) => f.type !== 'relation')
        .map((f: any) => `'${f.column_name || f.name}'`)
        .join(', ');
    const castFields = form.fields.filter((f: any) =>
        ['boolean', 'json', 'decimal', 'date', 'dateTime'].includes(f.type)
    );
    const castMap: Record<string, string> = {
        boolean: 'boolean', json: 'array', decimal: 'decimal:2', date: 'date', dateTime: 'datetime',
    };
    const lines: string[] = [];
    lines.push(`// app/Models/${modelName}.php`);
    lines.push(`namespace App\\Models;\n`);
    lines.push(`use Illuminate\\Database\\Eloquent\\Model;`);
    if (form.soft_deletes) lines.push(`use Illuminate\\Database\\Eloquent\\SoftDeletes;`);
    lines.push(`use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;\n`);
    lines.push(`class ${modelName} extends Model {`);
    lines.push(`  use HasFactory${form.soft_deletes ? ', SoftDeletes' : ''};\n`);
    lines.push(`  protected $table = '${tableName.value}';\n`);
    lines.push(`  protected $fillable = [${fillable}];\n`);
    if (castFields.length > 0) {
        lines.push(`  protected $casts = [`);
        castFields.forEach((f: any) => {
            lines.push(`    '${f.column_name || f.name}' => '${castMap[f.type]}',`);
        });
        lines.push(`  ];`);
    }
    lines.push(`}`);
    return lines.join('\n');
});

const factoryCode = computed(() => {
    const modelName = form.name || 'Model';
    const fakeMap: Record<string, string> = {
        string: "fake()->words(3, true)",
        integer: "fake()->numberBetween(1, 1000)",
        bigInteger: "fake()->numberBetween(1, 99999)",
        boolean: "fake()->boolean()",
        text: "fake()->paragraph()",
        decimal: "fake()->randomFloat(2, 1, 999)",
        date: "fake()->date()",
        dateTime: "fake()->dateTime()",
        json: "[]",
        enum: "fake()->randomElement(['active','inactive'])",
        uuid: "fake()->uuid()",
        foreignId: "1",
        email: "fake()->safeEmail()",
        file: "null",
        select: "fake()->word()",
    };
    const lines: string[] = [];
    lines.push(`// database/factories/${modelName}Factory.php`);
    lines.push(`class ${modelName}Factory extends Factory {`);
    lines.push(`  public function definition(): array {`);
    lines.push(`    return [`);
    form.fields.filter((f: any) => f.type !== 'relation').forEach((f: any) => {
        lines.push(`      '${f.column_name || f.name}' => ${fakeMap[f.type] || "fake()->word()"},`);
    });
    lines.push(`    ];`);
    lines.push(`  }`);
    lines.push(`}`);
    return lines.join('\n');
});

const activeCode = computed(() => {
    if (activeTab.value === 'migration') return migrationCode.value;
    if (activeTab.value === 'model') return modelCode.value;
    return factoryCode.value;
});

// ─── Copy Code ────────────────────────────────────────────────────
const copied = ref(false);
function copyCode() {
    navigator.clipboard.writeText(activeCode.value).then(() => {
        copied.value = true;
        setTimeout(() => (copied.value = false), 1500);
    });
}

// ─── Save & Generate ──────────────────────────────────────────────
function submit() {
    form.put(`/builder/${props.entity.id}`, { preserveScroll: true });
}

function generateCrud() {
    if (confirm('This will generate backend and frontend files and modify your routes. Proceed?')) {
        router.post(`/builder/${props.entity.id}/generate`);
    }
}
</script>

<template>
    <Head :title="`Edit Entity: ${entity.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">

        <!-- ── Global Styles ─────────────────────────────────────── -->
        

        <div class="sb-root">

            <!-- ── Topbar ─────────────────────────────────────────── -->
            <div class="sb-topbar">
                <div style="display:flex;align-items:center;gap:8px;">
                    <span class="sb-logo">⬡ SCHEMA BUILDER</span>
                    <div class="sb-model-wrap">
                        <label>Model:</label>
                        <input v-model="form.name" class="sb-input-sm" style="width:130px" placeholder="Model naam...">
                    </div>
                    <div class="sb-model-wrap">
                        <label>Table:</label>
                        <input v-model="form.table_name" class="sb-input-sm" style="width:140px" placeholder="table_name">
                    </div>
                    <div class="sb-model-wrap">
                        <label>Icon:</label>
                        <input v-model="form.icon" class="sb-input-sm" style="width:80px" placeholder="🗂️">
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <button class="sb-btn sb-btn-ghost" @click="form.fields=[]">✕ Clear</button>
                    <button class="sb-btn sb-btn-ghost" @click="submit">💾 Save Schema</button>
                    <button class="sb-btn sb-btn-green" @click="generateCrud">🚀 Generate CRUD</button>
                </div>
            </div>

            <!-- ── 3-Column Layout ─────────────────────────────────── -->
            <div class="sb-layout">

                <!-- LEFT: Types Palette + Settings -->
                <div class="sb-sidebar">
                    <div class="sb-sidebar-title">Field Types</div>
                    <div
                        v-for="t in TYPES" :key="t.type"
                        class="sb-type-item"
                        draggable="true"
                        @dragstart="paletteDragStart($event, t.type)"
                        @click="addField(t.type)"
                    >
                        <div class="sb-dot" :style="{ background: t.dot }"></div>
                        {{ t.label }}
                    </div>

                    <!-- Entity Settings inside sidebar -->
                    <div class="sb-settings-section">
                        <div class="sb-sidebar-title">Options</div>
                        <div class="sb-setting-row">
                            <span class="sb-setting-label">timestamps()</span>
                            <button
                                class="sb-toggle"
                                :class="{ on: form.timestamps }"
                                @click="form.timestamps = !form.timestamps"
                            ></button>
                        </div>
                        <div class="sb-setting-row" style="margin-top:5px">
                            <span class="sb-setting-label">softDeletes()</span>
                            <button
                                class="sb-toggle"
                                :class="{ on: form.soft_deletes }"
                                @click="form.soft_deletes = !form.soft_deletes"
                            ></button>
                        </div>
                    </div>
                </div>

                <!-- CENTER: Fields Canvas -->
                <div class="sb-canvas">
                    <div class="sb-canvas-header">
                        <span class="sb-model-badge">{{ form.name || 'Model' }}</span>
                        <span class="sb-field-count">{{ form.fields.length }} field{{ form.fields.length !== 1 ? 's' : '' }}</span>
                    </div>

                    <!-- Empty state -->
                    <div v-if="form.fields.length === 0" class="sb-empty-state">
                        <div style="font-size:28px;margin-bottom:10px;">⬡</div>
                        Sidebar se type drag karein<br>ya click karein field add karne ke liye
                    </div>

                    <!-- Field Rows -->
                    <div
                        v-for="(field, index) in form.fields"
                        :key="field.id ?? index"
                        class="sb-field-row-wrap"
                        :class="{
                            'drag-over': dragOverIndex === index,
                            'dragging': dragSrcIndex === index
                        }"
                        draggable="true"
                        @dragstart="rowDragStart($event, index)"
                        @dragover="rowDragOver($event, index)"
                        @dragleave="rowDragLeave"
                        @drop="rowDrop($event, index)"
                    >
                        <!-- Main row -->
                        <div class="sb-field-row-inner">
                            <div class="sb-drag-handle">⠿</div>

                            <!-- Name -->
                            <input
                                v-model="field.name"
                                class="sb-field-inp"
                                placeholder="field_label"
                                @blur="autoFillColumnName(field)"
                            >

                            <!-- Type -->
                            <select v-model="field.type" class="sb-field-sel">
                                <option v-for="t in TYPES" :key="t.type" :value="t.type">{{ t.type }}</option>
                            </select>

                            <!-- Nullable -->
                            <select v-model="field.is_nullable" class="sb-field-sel">
                                <option :value="false">required</option>
                                <option :value="true">nullable</option>
                            </select>

                            <!-- Type dot indicator -->
                            <div
                                class="sb-dot"
                                :style="{ background: TYPE_DOTS[field.type] || '#555a75' }"
                                style="width:9px;height:9px;flex-shrink:0"
                            ></div>

                            <!-- Remove -->
                            <button class="sb-remove-btn" @click="removeField(index)">×</button>
                        </div>

                        <!-- DB Column + Validation + Default (always visible) -->
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-top:8px;">
                            <div>
                                <div class="sb-mini-label">DB Column</div>
                                <input v-model="field.column_name" class="sb-field-inp" placeholder="column_name">
                            </div>
                            <div>
                                <div class="sb-mini-label">Validation</div>
                                <input v-model="field.validation_rules" class="sb-field-inp" placeholder="required|string|max:255">
                            </div>
                            <div>
                                <div class="sb-mini-label">Default</div>
                                <input v-model="field.default_value" class="sb-field-inp" placeholder="null">
                            </div>
                        </div>

                        <!-- Relation expanded -->
                        <template v-if="field.type === 'relation'">
                            <!-- Row 1: Entity + Relationship Type -->
                            <div class="sb-expanded-row">
                                <div>
                                    <div class="sb-mini-label">Related Entity</div>
                                    <select
                                        v-model="field.related_entity_id"
                                        class="sb-field-sel"
                                        @change="onRelatedEntityChange(field)"
                                    >
                                        <option :value="null">Select Entity</option>
                                        <option v-for="ent in allEntities" :key="ent.id" :value="ent.id">{{ ent.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <div class="sb-mini-label">Relationship Type</div>
                                    <select
                                        v-model="field.relationship_type"
                                        class="sb-field-sel"
                                        @change="onRelationshipTypeChange(field)"
                                    >
                                        <option value="BelongsTo">BelongsTo (single select)</option>
                                        <option value="HasMany">HasMany</option>
                                        <option value="BelongsToMany">BelongsToMany (multi-select)</option>
                                        <option value="HasOne">HasOne</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Row 2: Display Field + FK / Pivot Table (shown once entity is chosen) -->
                            <div v-if="field.related_entity_id" class="sb-expanded-row sb-relation-extra">
                                <!-- Display Field picker -->
                                <div>
                                    <div class="sb-mini-label">
                                        Display Field
                                        <span class="sb-badge-tip">shown in dropdown</span>
                                    </div>
                                    <div v-if="fetchingFields[field.related_entity_id]" class="sb-fetching-hint">
                                        ⏳ Loading fields…
                                    </div>
                                    <select
                                        v-else
                                        v-model="field.display_field"
                                        class="sb-field-sel sb-display-field-sel"
                                    >
                                        <option value="">— pick a label field —</option>
                                        <option
                                            v-for="rf in (relatedEntityFields[field.related_entity_id] || [])"
                                            :key="rf.column_name"
                                            :value="rf.column_name"
                                        >
                                            {{ rf.name }} ({{ rf.column_name }})
                                        </option>
                                    </select>
                                    <div v-if="field.display_field" class="sb-display-preview">
                                        <span class="sb-dp-label">Preview:</span>
                                        <span class="sb-dp-value">value = <strong>id</strong>, label = <strong>{{ field.display_field }}</strong></span>
                                    </div>
                                </div>

                                <!-- BelongsTo: foreign key hint -->
                                <div v-if="field.relationship_type === 'BelongsTo' || field.relationship_type === 'HasOne'">
                                    <div class="sb-mini-label">Foreign Key <span class="sb-badge-tip">optional</span></div>
                                    <input
                                        v-model="field.foreign_key"
                                        class="sb-field-inp"
                                        placeholder="e.g. category_id"
                                    >
                                    <div class="sb-relation-hint">
                                        💡 Stores <strong>id</strong> in DB · displays <strong>{{ field.display_field || 'label' }}</strong> in UI
                                    </div>
                                </div>

                                <!-- BelongsToMany: pivot table -->
                                <div v-if="field.relationship_type === 'BelongsToMany'">
                                    <div class="sb-mini-label">Pivot Table <span class="sb-badge-tip">auto-named</span></div>
                                    <input
                                        v-model="field.pivot_table"
                                        class="sb-field-inp"
                                        placeholder="e.g. category_product"
                                    >
                                    <div class="sb-relation-hint">
                                        💡 Multi-select · values stored in <strong>{{ field.pivot_table || 'pivot_table' }}</strong>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Select options expanded -->
                        <template v-if="field.type === 'select' || field.type === 'enum'">
                            <div class="sb-expanded-row-single">
                                <div class="sb-mini-label">Options</div>
                                <div v-for="(opt, i) in field.options" :key="i" class="sb-option-row">
                                    <input v-model="field.options[i]" class="sb-option-inp" placeholder="option value">
                                    <button class="sb-option-del" @click="field.options.splice(i, 1)">×</button>
                                </div>
                                <button class="sb-add-opt-btn" @click="field.options.push('')">+ Add Option</button>
                            </div>
                        </template>
                    </div>

                    <!-- Add Field -->
                    <button class="sb-add-field-btn" @click="addField('string')">+ Add Field</button>

                    <!-- Drop Zone -->
                    <div
                        class="sb-drop-zone"
                        :class="{ active: dropZoneActive }"
                        @dragover="dropZoneDragOver"
                        @dragleave="dropZoneActive = false"
                        @drop="dropZoneDrop"
                    >
                        Drop field type here to add
                    </div>
                </div>

                <!-- RIGHT: Live Code Preview -->
                <div class="sb-preview">
                    <div class="sb-preview-tabs">
                        <div
                            v-for="tab in (['migration','model','factory'] as const)"
                            :key="tab"
                            class="sb-preview-tab"
                            :class="{ active: activeTab === tab }"
                            @click="activeTab = tab"
                        >{{ tab }}</div>
                    </div>
                    <div class="sb-preview-body">
                        <pre class="sb-code">{{ activeCode }}</pre>
                    </div>
                    <button class="sb-copy-btn" @click="copyCode">
                        {{ copied ? '✓ Copied!' : '⎘ Copy Code' }}
                    </button>
                </div>

            </div>
        </div>

    </AppLayout>
</template>

<style>
            @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Syne:wght@400;500;600;700&display=swap');

            .sb-root {
                --sb-bg:      #f4f5f7;
                --sb-surface: #ffffff;
                --sb-surf2:   #f8f9fb;
                --sb-surf3:   #eef0f4;
                --sb-border:  #e2e5ec;
                --sb-border2: #c8cdd9;
                --sb-accent:  #5b52f0;
                --sb-accent2: #0ea88a;
                --sb-red:     #e53e3e;
                --sb-amber:   #d97706;
                --sb-purple:  #7c3aed;
                --sb-text:    #1a1d2e;
                --sb-text2:   #4a5068;
                --sb-text3:   #9399b2;
                --sb-mono:    'JetBrains Mono', monospace;
                --sb-sans:    'Syne', sans-serif;

                background: var(--sb-bg);
                color: var(--sb-text);
                font-family: var(--sb-sans);
                min-height: 100vh;
            }

            /* ── Topbar ── */
            .sb-topbar {
                display: flex; align-items: center; justify-content: space-between;
                padding: 0 20px; height: 54px;
                background: var(--sb-surface);
                border-bottom: 1px solid var(--sb-border);
                box-shadow: 0 1px 4px rgba(0,0,0,0.06);
                position: sticky; top: 0; z-index: 50;
            }
            .sb-logo {
                font-family: var(--sb-mono); font-size: 13px; font-weight: 700;
                color: var(--sb-accent); letter-spacing: 0.06em;
            }
            .sb-model-wrap { display: flex; align-items: center; gap: 8px; margin-left: 20px; }
            .sb-model-wrap label { font-family: var(--sb-mono); font-size: 11px; color: var(--sb-text2); }
            .sb-input-sm {
                background: var(--sb-surf2); border: 1px solid var(--sb-border2);
                color: var(--sb-text); font-family: var(--sb-mono); font-size: 12px;
                padding: 5px 10px; border-radius: 6px; outline: none;
                transition: border-color 0.15s;
            }
            .sb-input-sm:focus { border-color: var(--sb-accent); }
            .sb-btn {
                display: inline-flex; align-items: center; gap: 5px;
                padding: 6px 14px; border-radius: 6px; border: none;
                cursor: pointer; font-family: var(--sb-sans); font-size: 12px; font-weight: 600;
                transition: all 0.15s;
            }
            .sb-btn-ghost {
                background: transparent; border: 1px solid var(--sb-border2); color: var(--sb-text2);
            }
            .sb-btn-ghost:hover { border-color: var(--sb-accent); color: var(--sb-accent); }
            .sb-btn-primary { background: var(--sb-accent); color: #fff; }
            .sb-btn-primary:hover { background: #7c74ff; transform: translateY(-1px); }
            .sb-btn-green { background: #1d9e75; color: #fff; }
            .sb-btn-green:hover { background: #17a37a; transform: translateY(-1px); }

            /* ── Layout ── */
            .sb-layout {
                display: grid;
                grid-template-columns: 200px 1fr 290px;
                height: calc(100vh - 54px);
            }

            /* ── Sidebar ── */
            .sb-sidebar {
                background: var(--sb-surface);
                border-right: 1px solid var(--sb-border);
                padding: 14px 12px;
                overflow-y: auto;
            }
            .sb-sidebar-title {
                font-family: var(--sb-mono); font-size: 10px; color: var(--sb-text3);
                letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 10px;
            }
            .sb-type-item {
                display: flex; align-items: center; gap: 8px;
                padding: 7px 10px; border-radius: 6px;
                border: 1px solid var(--sb-border); background: var(--sb-surf2);
                cursor: grab; user-select: none; margin-bottom: 5px;
                font-size: 12px; font-family: var(--sb-mono); color: var(--sb-text2);
                transition: all 0.15s;
            }
            .sb-type-item:hover { border-color: var(--sb-border2); background: var(--sb-surf3); color: var(--sb-text); }
            .sb-type-item:active { cursor: grabbing; }
            .sb-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }

            .sb-settings-section {
                margin-top: 20px;
                padding-top: 14px;
                border-top: 1px solid var(--sb-border);
            }
            .sb-setting-row {
                display: flex; align-items: center; justify-content: space-between;
                padding: 7px 10px; border-radius: 6px;
                background: var(--sb-surf2); border: 1px solid var(--sb-border);
                margin-bottom: 5px;
            }
            .sb-setting-label { font-family: var(--sb-mono); font-size: 11px; color: var(--sb-text2); }
            .sb-toggle {
                width: 32px; height: 18px; border-radius: 9px;
                background: var(--sb-border); cursor: pointer;
                position: relative; flex-shrink: 0; transition: background 0.2s;
                border: none;
            }
            .sb-toggle.on { background: var(--sb-accent); }
            .sb-toggle::after {
                content: ''; position: absolute;
                width: 12px; height: 12px; background: #fff;
                border-radius: 50%; top: 3px; left: 3px; transition: left 0.2s;
            }
            .sb-toggle.on::after { left: 17px; }

            /* ── Canvas ── */
            .sb-canvas {
                background: var(--sb-bg);
                padding: 14px 16px;
                overflow-y: auto;
            }
            .sb-canvas-header {
                display: flex; align-items: center; justify-content: space-between;
                margin-bottom: 12px;
            }
            .sb-model-badge {
                font-family: var(--sb-mono); font-size: 14px; font-weight: 600;
                color: var(--sb-accent);
            }
            .sb-field-count { font-family: var(--sb-mono); font-size: 11px; color: var(--sb-text3); }

            /* ── Field Row ── */
            .sb-field-row {
                display: grid;
                grid-template-columns: 22px 1fr 110px 90px auto 26px;
                align-items: center; gap: 6px;
                padding: 8px 10px;
                background: var(--sb-surface);
                border: 1px solid var(--sb-border);
                border-radius: 8px;
                margin-bottom: 6px;
                cursor: grab; transition: all 0.15s;
                position: relative;
            }
            .sb-field-row:hover { border-color: var(--sb-border2); }
            .sb-field-row.drag-over { border-color: var(--sb-accent); background: rgba(108,99,255,0.07); }
            .sb-field-row.dragging { opacity: 0.35; border-style: dashed; }
            .sb-drag-handle {
                color: var(--sb-text3); font-size: 14px; cursor: grab;
                display: flex; align-items: center; justify-content: center;
                user-select: none;
            }
            .sb-field-inp, .sb-field-sel {
                background: var(--sb-surf2); border: 1px solid var(--sb-border);
                color: var(--sb-text); font-family: var(--sb-mono); font-size: 11px;
                padding: 5px 8px; border-radius: 5px; outline: none; width: 100%;
                transition: border-color 0.15s;
            }
            .sb-field-inp:focus, .sb-field-sel:focus { border-color: var(--sb-accent); }
            .sb-field-sel option { background: var(--sb-surf2); }
            .sb-remove-btn {
                background: transparent; border: none; cursor: pointer;
                color: var(--sb-text3); font-size: 16px;
                display: flex; align-items: center; justify-content: center;
                border-radius: 4px; padding: 2px; transition: color 0.15s;
            }
            .sb-remove-btn:hover { color: var(--sb-red); }

            /* Relation / Select expanded row */
            .sb-expanded-row {
                grid-column: 1 / -1;
                display: grid; grid-template-columns: 1fr 1fr; gap: 8px;
                margin-top: 8px; padding-top: 8px;
                border-top: 1px solid var(--sb-border);
            }
            .sb-expanded-row-single { grid-column: 1 / -1; margin-top: 8px; padding-top: 8px; border-top: 1px solid var(--sb-border); }
            .sb-field-row-wrap {
                background: var(--sb-surface);
                border: 1px solid var(--sb-border);
                border-radius: 8px;
                padding: 8px 10px;
                margin-bottom: 6px;
                transition: border-color 0.15s, box-shadow 0.15s;
                box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            }
            .sb-field-row-wrap:hover { border-color: var(--sb-border2); box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
            .sb-field-row-wrap.drag-over { border-color: var(--sb-accent); background: rgba(108,99,255,0.07); }
            .sb-field-row-inner {
                display: grid;
                grid-template-columns: 22px 1fr 110px 90px auto 26px;
                align-items: center; gap: 6px; cursor: grab;
            }

            .sb-mini-label {
                font-family: var(--sb-mono); font-size: 10px;
                color: var(--sb-text3); text-transform: uppercase;
                letter-spacing: 0.07em; margin-bottom: 4px;
            }
            .sb-option-row { display: flex; gap: 6px; align-items: center; margin-bottom: 4px; }
            .sb-option-inp {
                flex: 1; background: var(--sb-surf2); border: 1px solid var(--sb-border);
                color: var(--sb-text); font-family: var(--sb-mono); font-size: 11px;
                padding: 4px 8px; border-radius: 4px; outline: none;
            }
            .sb-option-inp:focus { border-color: var(--sb-accent); }
            .sb-option-del { background: transparent; border: none; color: var(--sb-text3); cursor: pointer; font-size: 14px; }
            .sb-option-del:hover { color: var(--sb-red); }
            .sb-add-opt-btn {
                display: inline-flex; align-items: center; gap: 4px;
                margin-top: 4px; padding: 4px 10px; border-radius: 5px;
                background: var(--sb-surf3); border: 1px solid var(--sb-border);
                color: var(--sb-text2); font-family: var(--sb-mono); font-size: 11px;
                cursor: pointer; transition: all 0.15s;
            }
            .sb-add-opt-btn:hover { border-color: var(--sb-accent2); color: var(--sb-accent2); }

            .sb-add-field-btn {
                display: flex; align-items: center; gap: 6px;
                width: 100%; padding: 8px 14px; margin-top: 8px;
                background: transparent; border: 1px dashed var(--sb-border2);
                color: var(--sb-text2); border-radius: 7px; cursor: pointer;
                font-family: var(--sb-mono); font-size: 12px; transition: all 0.15s;
            }
            .sb-add-field-btn:hover { border-color: var(--sb-accent); color: var(--sb-accent); }

            .sb-drop-zone {
                border: 1px dashed var(--sb-border2); border-radius: 8px;
                padding: 16px; text-align: center;
                color: var(--sb-text3); font-family: var(--sb-mono); font-size: 11px;
                margin-top: 8px; transition: all 0.2s;
            }
            .sb-drop-zone.active { border-color: var(--sb-accent); color: var(--sb-accent); background: rgba(108,99,255,0.05); }

            .sb-empty-state {
                text-align: center; padding: 40px 20px;
                color: var(--sb-text3); font-family: var(--sb-mono); font-size: 12px;
            }

            /* ── Preview Panel ── */
            .sb-preview {
                background: var(--sb-surface);
                border-left: 1px solid var(--sb-border);
                display: flex; flex-direction: column;
                overflow: hidden;
            }
            .sb-preview-tabs { display: flex; border-bottom: 1px solid var(--sb-border); }
            .sb-preview-tab {
                flex: 1; padding: 10px 4px; text-align: center;
                font-size: 10px; font-family: var(--sb-mono); color: var(--sb-text3);
                cursor: pointer; border-bottom: 2px solid transparent;
                transition: all 0.15s; letter-spacing: 0.07em; text-transform: uppercase;
            }
            .sb-preview-tab.active { color: var(--sb-accent); border-bottom-color: var(--sb-accent); }
            .sb-preview-body {
                flex: 1; overflow-y: auto; padding: 14px;
                background: #f3f4f8;
            }
            .sb-code {
                font-family: var(--sb-mono); font-size: 11px; line-height: 1.75;
                color: #2d3748; white-space: pre-wrap; word-break: break-all;
            }
            .sb-copy-btn {
                margin: 6px 12px 10px;
                padding: 7px; background: var(--sb-surf2);
                border: 1px solid var(--sb-border); color: var(--sb-text2);
                border-radius: 5px; font-family: var(--sb-mono); font-size: 11px;
                cursor: pointer; text-align: center; transition: all 0.15s;
            }
            .sb-copy-btn:hover { border-color: var(--sb-accent); color: var(--sb-accent); }

            /* scrollbar */
            .sb-root ::-webkit-scrollbar { width: 4px; }
            .sb-root ::-webkit-scrollbar-track { background: transparent; }
            .sb-root ::-webkit-scrollbar-thumb { background: var(--sb-border2); border-radius: 2px; }

            /* ── Relation Extra Panel ── */
            .sb-relation-extra {
                margin-top: 6px;
                padding: 10px 12px;
                background: linear-gradient(135deg, rgba(91,82,240,0.04) 0%, rgba(14,168,138,0.04) 100%);
                border: 1px solid rgba(91,82,240,0.18);
                border-radius: 7px;
            }
            .sb-display-field-sel {
                border-color: rgba(91,82,240,0.35) !important;
            }
            .sb-display-field-sel:focus { border-color: var(--sb-accent) !important; }
            .sb-display-preview {
                display: flex; align-items: center; gap: 6px;
                margin-top: 5px; padding: 4px 8px;
                background: rgba(52,211,153,0.1); border-radius: 4px;
                border: 1px solid rgba(52,211,153,0.25);
            }
            .sb-dp-label {
                font-family: var(--sb-mono); font-size: 9px;
                color: var(--sb-accent2); text-transform: uppercase; letter-spacing: 0.06em;
            }
            .sb-dp-value {
                font-family: var(--sb-mono); font-size: 10px; color: var(--sb-text2);
            }
            .sb-dp-value strong { color: var(--sb-accent2); }
            .sb-relation-hint {
                margin-top: 5px; font-family: var(--sb-mono); font-size: 10px;
                color: var(--sb-text3); line-height: 1.5;
            }
            .sb-relation-hint strong { color: var(--sb-accent); }
            .sb-badge-tip {
                display: inline-block;
                margin-left: 5px; padding: 1px 5px;
                background: rgba(91,82,240,0.1); border-radius: 3px;
                font-size: 9px; color: var(--sb-accent);
                text-transform: none; letter-spacing: 0;
            }
            .sb-fetching-hint {
                font-family: var(--sb-mono); font-size: 10px;
                color: var(--sb-text3); padding: 5px 0;
                animation: pulse 1.2s ease-in-out infinite;
            }
            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.4; }
            }
        </style>