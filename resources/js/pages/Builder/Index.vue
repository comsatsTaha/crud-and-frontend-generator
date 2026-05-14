<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { defineProps } from 'vue';

const props = defineProps<{
    entities: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Builder Dashboard', href: '/builder' },
];
</script>

<template>
    <Head title="CRUD Builder" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Entities</h1>
                <Link
                    href="/builder/create"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
                >
                    + Create New Entity
                </Link>
            </div>

            <div class="bg-white dark:bg-sidebar rounded-xl border border-sidebar-border shadow-sm overflow-hidden">
                <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                    <thead class="bg-gray-50 dark:bg-sidebar-border/30 text-xs uppercase text-gray-700 dark:text-gray-300">
                        <tr>
                            <th scope="col" class="px-6 py-4">Name</th>
                            <th scope="col" class="px-6 py-4">Table Name</th>
                            <th scope="col" class="px-6 py-4">Generated</th>
                            <th scope="col" class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="entities.length === 0">
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                No entities found. Create one to get started!
                            </td>
                        </tr>
                        <tr v-for="entity in entities" :key="entity.id" class="border-b border-sidebar-border dark:border-sidebar-border/50 hover:bg-gray-50 dark:hover:bg-sidebar-border/20">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ entity.name }}
                            </td>
                            <td class="px-6 py-4">
                                {{ entity.table_name }}
                            </td>
                            <td class="px-6 py-4">
                                <span v-if="entity.is_generated" class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Yes</span>
                                <span v-else class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">No</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <Link :href="`/builder/${entity.id}/edit`" class="text-blue-600 hover:underline dark:text-blue-500 mr-4">Edit</Link>
                                <Link :href="`/builder/${entity.id}`" method="delete" as="button" class="text-red-600 hover:underline dark:text-red-500">Delete</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
