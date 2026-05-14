<template>
  <div class="bg-white shadow rounded-lg p-4">
    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Select Template</h3>
    <div class="space-y-3">
      <div 
        v-for="tpl in templates" 
        :key="tpl.id"
        @click="$emit('update:modelValue', tpl.id)"
        class="border rounded-md p-3 cursor-pointer transition flex items-center justify-between"
        :class="modelValue === tpl.id ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:bg-gray-50'"
      >
        <div>
          <h4 class="text-sm font-medium" :class="modelValue === tpl.id ? 'text-blue-700' : 'text-gray-900'">{{ tpl.name }}</h4>
          <p class="text-xs text-gray-500 mt-1">{{ tpl.description }}</p>
        </div>
        <div v-if="modelValue === tpl.id" class="text-blue-500">
          <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  modelValue: {
    type: String,
    required: true
  }
})

defineEmits(['update:modelValue'])

const templates = [
  { id: 'login', name: 'Login Page', description: 'Simple email/password authentication form.' },
  { id: 'dashboard', name: 'Admin Dashboard', description: 'Admin panel with sidebar, nav, and stats.' },
  { id: 'landing', name: 'Landing Page', description: 'Marketing page with hero and features.' },
  { id: 'table', name: 'CRUD Table', description: 'Data table with search and pagination.' },
  { id: 'profile', name: 'User Profile', description: 'User account management page.' }
]
</script>
