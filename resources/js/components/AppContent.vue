<script setup lang="ts">
import { SidebarInset } from '@/components/ui/sidebar';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

interface Props {
    variant?: 'header' | 'sidebar';
    class?: string;
}

const props = defineProps<Props>();
const className = computed(() => props.class);

const page = usePage();
</script>

<template>
    <SidebarInset v-if="props.variant === 'sidebar'" :class="className">
        <div v-if="page.props.flash?.success" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 m-4 rounded" role="alert">
            <p>{{ page.props.flash.success }}</p>
        </div>
        <div v-if="page.props.flash?.error" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 m-4 rounded" role="alert">
            <p>{{ page.props.flash.error }}</p>
        </div>
        <slot />
    </SidebarInset>
    <main v-else class="mx-auto flex h-full w-full max-w-7xl flex-1 flex-col gap-4 rounded-xl" :class="className">
        <div v-if="page.props.flash?.success" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded" role="alert">
            <p>{{ page.props.flash.success }}</p>
        </div>
        <div v-if="page.props.flash?.error" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">
            <p>{{ page.props.flash.error }}</p>
        </div>
        <slot />
    </main>
</template>
