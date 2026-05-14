<template>
  <div class="w-full h-full flex flex-col absolute inset-0 bg-gray-900 rounded-b-lg overflow-hidden">
    <!-- Header Actions -->
    <div class="flex justify-between items-center px-4 py-2 bg-gray-800 text-white border-b border-gray-700 select-none">
      <span class="text-xs font-semibold text-gray-300">Generated Vue SFC</span>
      <div class="space-x-2">
        <button 
          @click="copyCode" 
          class="inline-flex items-center px-2.5 py-1.5 border border-gray-600 shadow-sm text-xs font-medium rounded text-gray-300 bg-gray-700 hover:bg-gray-600 focus:outline-none"
        >
          <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
          </svg>
          {{ copied ? 'Copied!' : 'Copy Code' }}
        </button>
        <button 
          @click="downloadCode" 
          class="inline-flex items-center px-2.5 py-1.5 border border-transparent shadow-sm text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700 focus:outline-none"
        >
          <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
          </svg>
          Download .vue
        </button>
      </div>
    </div>
    
    <!-- Code Editor/Viewer -->
    <div class="flex-1 overflow-auto bg-[#1e1e1e] p-4 text-sm text-gray-300 font-mono text-left whitespace-pre">
      <code>{{ code }}</code>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  code: {
    type: String,
    required: true
  }
})

const copied = ref(false)

const copyCode = async () => {
  try {
    await navigator.clipboard.writeText(props.code)
    copied.value = true
    setTimeout(() => copied.value = false, 2000)
  } catch (e) {
    console.error('Failed to copy code: ', e)
  }
}

const downloadCode = () => {
  const blob = new Blob([props.code], { type: 'text/plain' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'GeneratedComponent.vue'
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
}
</script>
