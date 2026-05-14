<template>
  <div class="w-full h-full flex flex-col absolute inset-0">
    <div class="bg-gray-800 text-white text-xs px-3 py-1 flex justify-between items-center z-10 rounded-t-lg shadow-sm">
      <span class="font-semibold text-gray-300">Live Preview</span>
      <span class="text-gray-400">Tailwind CSS (CDN)</span>
    </div>
    <div class="flex-1 w-full bg-white relative overflow-hidden border border-gray-300 shadow-inner rounded-b-lg">
      <iframe 
        ref="previewFrame"
        class="w-full h-full border-none m-0 p-0 block"
        title="Live Preview"
      ></iframe>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'

const props = defineProps({
  code: {
    type: String,
    required: true
  },
  template: {
    type: String,
    required: true
  }
})

const previewFrame = ref(null)

const renderPreview = () => {
  if (!previewFrame.value) return

  // We extract HTML from <template> and inject Tailwind from CDN for the preview.
  const templateMatch = props.code.match(/<template>([\s\S]*)<\/template>/)
  const templateHtml = templateMatch ? templateMatch[1] : '<div class="p-4 text-red-500">Could not extract template</div>'

  const styleMatch = props.code.match(/<style[^>]*>([\s\S]*)<\/style>/)
  const styleCss = styleMatch ? styleMatch[1] : ''

  const htmlContent = `
    <!DOCTYPE html>
    <html class="antialiased">
    <head>
      <script src="https://cdn.tailwindcss.com"><\/script>
      <style>
        body { margin: 0; padding: 0; }
        /* Add some scoped style mock */
        ${styleCss}
      </style>
      <script>
        tailwind.config = {
          darkMode: 'class'
        }
      <\/script>
    </head>
    <body class="bg-gray-50 h-screen w-full m-0 p-0 overflow-x-hidden">
      ${templateHtml}
      
      <!-- Script to trigger simple vue reactivity mock if needed -->
    </body>
    </html>
  `

  const doc = previewFrame.value.contentWindow.document
  doc.open()
  doc.write(htmlContent)
  doc.close()
}

watch(() => props.code, () => {
  renderPreview()
})

onMounted(() => {
  renderPreview()
})
</script>
