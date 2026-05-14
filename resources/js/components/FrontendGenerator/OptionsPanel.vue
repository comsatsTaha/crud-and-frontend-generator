<template>
  <div class="bg-white shadow rounded-lg p-4">
    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Customization Options</h3>
    
    <div class="space-y-5">
      <!-- Project Name -->
      <div>
        <label class="block text-sm font-medium text-gray-700">Project Name</label>
        <input 
          type="text" 
          v-model="localConfig.projectName"
          @input="emitUpdate"
          class="mt-1 shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md py-2 px-3 border"
        />
      </div>

      <!-- Primary Color -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Primary Color (Hex)</label>
        <div class="flex items-center space-x-2">
          <input 
            type="color" 
            v-model="localConfig.primaryColor"
            @input="emitUpdate"
            class="h-8 w-12 rounded border border-gray-300 cursor-pointer p-0"
          />
          <span class="text-sm font-mono text-gray-600">{{ localConfig.primaryColor }}</span>
        </div>
      </div>

      <!-- Dark Mode Toggle -->
      <div class="flex items-center justify-between">
        <label class="block text-sm font-medium text-gray-700">Enable Dark Mode</label>
        <button 
          type="button"
          class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          :class="localConfig.darkMode ? 'bg-blue-600' : 'bg-gray-200'"
          @click="toggleDarkMode"
        >
          <span
            aria-hidden="true"
            class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"
            :class="localConfig.darkMode ? 'translate-x-5' : 'translate-x-0'"
          />
        </button>
      </div>

      <!-- Components (Only relevant for some templates like Dashboard/Landing) -->
      <div v-if="['dashboard', 'landing'].includes(config.template)">
        <label class="block text-sm font-medium text-gray-700 mb-2">Include Layout Components</label>
        <div class="space-y-2">
          <div class="flex items-start">
            <div class="flex items-center h-5">
              <input 
                type="checkbox" 
                value="sidebar" 
                v-model="localConfig.components" 
                @change="emitUpdate"
                class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded" 
              />
            </div>
            <div class="ml-3 text-sm">
              <label class="font-medium text-gray-700">Sidebar</label>
            </div>
          </div>
          <div class="flex items-start">
            <div class="flex items-center h-5">
              <input 
                type="checkbox" 
                value="navbar" 
                v-model="localConfig.components" 
                @change="emitUpdate"
                class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded" 
              />
            </div>
            <div class="ml-3 text-sm">
              <label class="font-medium text-gray-700">Top Navbar</label>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  config: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update:config'])

const localConfig = ref({ ...props.config })

watch(() => props.config, (newVal) => {
  localConfig.value = { ...newVal }
}, { deep: true })

const toggleDarkMode = () => {
  localConfig.value.darkMode = !localConfig.value.darkMode
  emitUpdate()
}

const emitUpdate = () => {
  emit('update:config', localConfig.value)
}
</script>
