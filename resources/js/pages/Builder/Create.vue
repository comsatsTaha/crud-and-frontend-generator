<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Builder Dashboard', href: '/builder' },
    { title: 'Create Entity', href: '/builder/create' },
];

const form = useForm({
    name: '',
    table_name: '',
    icon: '',
    soft_deletes: false,
});

const submit = () => {
    form.post('/builder');
};

const autoFillTableName = () => {
    if (!form.table_name && form.name) {
        // Simple pluralization and snake_case for demo purposes
        // Ideally this would be more robust
        let tableName = form.name.toLowerCase().replace(/\s+/g, '_');
        if (!tableName.endsWith('s')) {
            tableName += 's';
        }
        form.table_name = tableName;
    }
};
</script>

<template>
    <Head title="Create Entity" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-2xl mx-4 mt-8">
            <div class="bg-white dark:bg-sidebar rounded-xl border border-sidebar-border shadow-sm overflow-hidden p-6">
                <h2 class="text-xl font-semibold mb-6 text-gray-900 dark:text-gray-100">Create New Entity</h2>
                
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Entity Name (Model)</label>
                        <input
                            id="name"
                            v-model="form.name"
                            @blur="autoFillTableName"
                            type="text"
                            placeholder="e.g. Product"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-sidebar-border/30 dark:border-sidebar-border dark:text-white sm:text-sm px-3 py-2 border"
                            required
                        />
                        <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label for="table_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Database Table Name</label>
                        <input
                            id="table_name"
                            v-model="form.table_name"
                            type="text"
                            placeholder="e.g. products"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-sidebar-border/30 dark:border-sidebar-border dark:text-white sm:text-sm px-3 py-2 border"
                            required
                        />
                        <p class="mt-1 text-xs text-gray-500">Must be unique and in snake_case</p>
                        <p v-if="form.errors.table_name" class="mt-2 text-sm text-red-600">{{ form.errors.table_name }}</p>
                    </div>

                    <div>
                        <label for="icon" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lucide Icon Name (Optional)</label>
                        <input
                            id="icon"
                            v-model="form.icon"
                            type="text"
                            placeholder="e.g. Box"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-sidebar-border/30 dark:border-sidebar-border dark:text-white sm:text-sm px-3 py-2 border"
                        />
                    </div>

                    <div class="flex items-center">
                        <input
                            id="soft_deletes"
                            v-model="form.soft_deletes"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-sidebar-border/30 dark:border-sidebar-border"
                        />
                        <label for="soft_deletes" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Enable Soft Deletes
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-4 mt-8 pt-4 border-t border-gray-200 dark:border-sidebar-border">
                        <Link href="/builder" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors disabled:opacity-50"
                        >
                            Create Entity & Continue
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
