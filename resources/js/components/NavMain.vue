<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import type { Component } from 'vue';

interface NavItem {
    title: string;
    url: string;
    icon: Component;
}

const slugify = (text) => {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '-')        // spaces -> -
        .replace(/[^\w\-]+/g, '')    // remove special chars
        .replace(/\-\-+/g, '-')      // multiple - to single
        .replace(/^-+/, '')          // start - remove
        .replace(/-+$/, '');         // end - remove
};
defineProps<{
    items: NavItem[];
}>();

const page = usePage<SharedData>();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton as-child :is-active="item.url === page.url">
                   <Link :href="`/${slugify(item.title)}`">
    <component :is="item.icon" />
    <span>{{ item.title }}</span>
</Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
