<template>
  <div class="min-h-screen bg-gray-100 flex flex-col font-sans">
    <!-- Header -->
    <header class="bg-white shadow">
      <div class="max-w-screen-2xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Drag & Drop Builder</h1>
        <div class="flex items-center space-x-4">
          <input 
            type="text" 
            v-model="routeConfig.routeName" 
            placeholder="Route URL (e.g. /my-new-page)" 
            class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
          />
          <input 
            type="text" 
            v-model="routeConfig.componentName" 
            placeholder="Component Name (e.g. MyPage)" 
            class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
          />
          <button 
            @click="generatePage"
            :disabled="isGenerating || layout.length === 0"
            class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md shadow-sm hover:bg-blue-700 disabled:opacity-50 flex items-center transition"
          >
            <svg v-if="isGenerating" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            Generate Page
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 w-full max-w-screen-2xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex overflow-hidden">
      <div class="flex w-full gap-6 h-[calc(100vh-120px)]">
        
        <!-- Left Sidebar (Components Palette) -->
        <div class="w-64 bg-white shadow rounded-lg p-4 flex flex-col overflow-y-auto">
          <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Components</h3>
          <div class="space-y-3">
            <h4 class="text-xs font-bold text-gray-400 mt-2">LAYOUT COLUMNS</h4>
            <div 
              draggable="true" @dragstart="onDragStart($event, 'row', { cols: 1 })"
              class="p-2 bg-blue-50 border border-blue-200 text-blue-700 rounded cursor-grab hover:bg-blue-100 flex items-center text-sm font-medium shadow-sm"
            >
              <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16" stroke-width="2" stroke-linecap="round"/></svg> 1 Column Row
            </div>
            <div 
              draggable="true" @dragstart="onDragStart($event, 'row', { cols: 2 })"
              class="p-2 bg-blue-50 border border-blue-200 text-blue-700 rounded cursor-grab hover:bg-blue-100 flex items-center text-sm font-medium shadow-sm"
            >
              <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 4h7v16H4zm9 0h7v16h-7z" stroke-width="2" stroke-linecap="round"/></svg> 2 Columns Row
            </div>
            <div 
              draggable="true" @dragstart="onDragStart($event, 'row', { cols: 3 })"
              class="p-2 bg-blue-50 border border-blue-200 text-blue-700 rounded cursor-grab hover:bg-blue-100 flex items-center text-sm font-medium shadow-sm"
            >
              <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 4h4v16H4zm6 0h4v16h-4zm6 0h4v16h-4z" stroke-width="2" stroke-linecap="round"/></svg> 3 Columns Row
            </div>

            <h4 class="text-xs font-bold text-gray-400 mt-4">ELEMENTS</h4>
            <div 
              v-for="cmp in availableComponents" 
              :key="cmp.type"
              draggable="true"
              @dragstart="onDragStart($event, cmp.type)"
              class="p-3 bg-gray-50 border border-gray-200 rounded cursor-grab hover:bg-blue-50 hover:border-blue-300 transition-colors flex items-center shadow-sm"
            >
              <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
              <span class="font-medium text-sm text-gray-700">{{ cmp.label }}</span>
            </div>
          </div>
          
          <div class="mt-8">
             <div v-if="successMessage" class="p-3 bg-green-100 text-green-700 text-sm rounded-md border border-green-200">
               {{ successMessage }}
               <a :href="'/' + routeConfig.routeName.replace(/^\/+/, '')" target="_blank" class="block mt-2 font-bold underline">View Page</a>
             </div>
             <div v-if="errorMessage" class="p-3 bg-red-100 text-red-700 text-sm rounded-md border border-red-200">
               {{ errorMessage }}
             </div>
          </div>

          <div v-if="activeComponent && activeComponent.type !== 'row'" class="mt-8 pt-6 border-t border-gray-200 flex-1 overflow-y-auto">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Properties</h3>
            <div class="space-y-4 pb-4">
              <div class="text-xs font-bold text-blue-600 mb-2">{{ availableComponents.find(c => c.type === activeComponent.type)?.label || activeComponent.type }}</div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Heading</label>
                <input type="text" v-model="activeComponent.content.heading" placeholder="e.g. My Custom Title" class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Subtext</label>
                <textarea v-model="activeComponent.content.subtext" rows="3" placeholder="e.g. Some description text..." class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Button Text</label>
                <input type="text" v-model="activeComponent.content.buttonText" placeholder="e.g. Click Here" class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side (Drop Zone / Canvas) -->
        <div class="flex-1 bg-white shadow rounded-lg flex flex-col relative overflow-hidden">
          <div class="bg-gray-100 border-b px-4 py-2 text-xs font-semibold text-gray-500 flex justify-between items-center">
            Canvas Preview
            <button @click="clearLayout" class="text-red-500 hover:text-red-700">Clear</button>
          </div>
          <div 
            class="flex-1 overflow-y-auto p-4 bg-gray-200 relative transition-colors"
            :class="{'bg-blue-50 ring-2 ring-inset ring-blue-400': isDragOver}"
            @dragover.prevent="onDragOver"
            @dragleave="onDragLeave"
            @drop="onDrop"
          >
            <div v-if="layout.length === 0" class="absolute inset-0 flex items-center justify-center pointer-events-none">
              <div class="text-center text-gray-400">
                <svg class="mx-auto h-12 w-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                <p>Drag components here to build your page</p>
              </div>
            </div>

            <!-- Virtual Layout representation -->
            <div class="space-y-4 pb-20">
              <div 
                v-for="(item, index) in layout" 
                :key="item.id"
                class="bg-white border rounded shadow-sm relative group overflow-hidden"
              >
                <!-- Toolbar overlay inside element -->
                <div class="absolute top-0 right-0 p-2 opacity-0 group-hover:opacity-100 transition-opacity bg-black/5 rounded-bl z-10 flex space-x-2">
                  <button @click="moveUp(layout, index)" :disabled="index === 0" class="p-1 bg-white rounded shadow text-gray-600 hover:text-blue-600 disabled:opacity-30">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                  </button>
                  <button @click="moveDown(layout, index)" :disabled="index === layout.length - 1" class="p-1 bg-white rounded shadow text-gray-600 hover:text-blue-600 disabled:opacity-30">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                  </button>
                  <button @click="removeComponent(layout, index)" class="p-1 bg-white rounded shadow text-gray-600 hover:text-red-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                  </button>
                </div>

                <!-- If purely a Row Component -->
                <div v-if="item.type === 'row'" class="p-4 bg-gray-50 border-2 border-dashed border-gray-300 pointer-events-auto">
                  <div class="text-xs font-bold text-gray-400 mb-2">GRID ROW ({{item.cols}} COLUMNS)</div>
                  <div :class="['grid gap-4', 'grid-cols-' + item.cols]">
                    <!-- Columns -->
                    <div 
                      v-for="(colItems, colIndex) in item.columns" :key="colIndex"
                      class="min-h-[120px] bg-white border border-gray-200 rounded p-2 transition-colors relative"
                      :class="{'bg-blue-50 ring-2 ring-blue-300': item.dragOverCol === colIndex}"
                      @dragover.prevent="item.dragOverCol = colIndex"
                      @dragleave="item.dragOverCol = null"
                      @drop.stop="onDropToColumn($event, item, colIndex)"
                    >
                      <div v-if="colItems.length === 0" class="absolute inset-0 flex items-center justify-center text-gray-400 text-sm pointer-events-none">Drop here</div>
                      
                      <div v-for="(subItem, subIndex) in colItems" :key="subItem.id" 
                           @click.stop="setActive(subItem)"
                           class="bg-gray-100 border border-gray-300 p-2 rounded mb-2 relative group overflow-hidden pointer-events-auto cursor-pointer"
                           :class="{'ring-2 ring-blue-500': activeComponent && activeComponent.id === subItem.id}">
                        <div class="absolute top-0 right-0 p-1 bg-white opacity-0 group-hover:opacity-100 shadow rounded-bl z-10 flex space-x-1">
                          <button @click.stop="moveUp(colItems, subIndex)" :disabled="subIndex === 0" class="text-gray-500 hover:text-blue-500"><svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M5 15l7-7 7 7"></path></svg></button>
                          <button @click.stop="moveDown(colItems, subIndex)" :disabled="subIndex === colItems.length - 1" class="text-gray-500 hover:text-blue-500"><svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 9l-7 7-7-7"></path></svg></button>
                          <button @click.stop="removeComponent(colItems, subIndex)" class="text-gray-500 hover:text-red-500"><svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                        </div>
                        <div class="text-xs font-bold text-gray-500 flex items-center justify-center h-16">{{ availableComponents.find(c => c.type === subItem.type)?.label || subItem.type }}</div>
                        <div v-if="subItem.content && (subItem.content.heading || subItem.content.subtext)" class="mt-1 text-[10px] text-gray-400 truncate px-1 border-t pt-1">
                           {{ subItem.content.heading || subItem.content.subtext }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Generic Preview Block for Normal Components -->
                <div v-else 
                     @click.stop="setActive(item)"
                     class="px-6 py-8 border-b transition-colors bg-white hover:bg-gray-50 relative pointer-events-auto cursor-pointer"
                     :class="{'ring-2 ring-inset ring-blue-500': activeComponent && activeComponent.id === item.id}"
                >
                  <div class="text-xs font-bold text-gray-400 font-mono tracking-widest uppercase mb-4">{{ availableComponents.find(c => c.type === item.type)?.label || item.type }}</div>
                  
                  <div v-if="item.content && (item.content.heading || item.content.subtext)" class="mb-4 bg-gray-50 p-3 rounded border border-dashed border-gray-300">
                    <div v-if="item.content.heading" class="font-bold text-sm text-gray-800">{{ item.content.heading }}</div>
                    <div v-if="item.content.subtext" class="text-xs text-gray-500 mt-1">{{ item.content.subtext }}</div>
                    <div v-if="item.content.buttonText" class="text-xs font-medium text-blue-600 mt-2 bg-blue-50 inline-block px-2 py-1 rounded">{{ item.content.buttonText }}</div>
                  </div>
                  
                  <div v-if="['navbar', 'sidebar_nav'].includes(item.type)" class="w-full h-12 bg-gray-100 rounded border border-gray-200"></div>
                  <div v-else-if="['hero', 'hero_split', 'cta', 'newsletter'].includes(item.type)" class="w-full h-32 bg-blue-50 rounded border border-blue-100"></div>
                  <div v-else-if="['footer', 'footer_large'].includes(item.type)" class="w-full h-20 bg-gray-800 rounded"></div>
                  <div v-else-if="['table', 'contact', 'form', 'login_form', 'user_profile'].includes(item.type)" class="w-full max-w-sm mx-auto h-40 bg-white rounded shadow-sm border border-gray-200 flex flex-col p-4 space-y-2"><div class="h-4 bg-gray-100 w-1/2 rounded"></div><div class="h-8 bg-gray-50 border rounded w-full"></div><div class="h-8 bg-gray-50 border rounded w-full"></div></div>
                  <div v-else class="flex gap-4">
                    <div class="flex-1 h-24 bg-gray-100 rounded border border-gray-200"></div>
                    <div class="flex-1 h-24 bg-gray-100 rounded border border-gray-200"></div>
                    <div class="flex-1 h-24 bg-gray-100 rounded border border-gray-200"></div>
                  </div>
                </div> <!-- Closes the generic preview block (div 11) -->
              </div> <!-- Closes the v-for element (div 9) -->
            </div> <!-- Closes the virtual layout wrapper (div 8) -->
          </div> <!-- Closes the canvas drop zone (div 6) -->
        </div> <!-- Closes the right side wrapper (div 4) -->
        
      </div> <!-- Closes the main gap-6 wrapper (div 2) -->
    </main> <!-- Closes the main element -->
  </div> <!-- Closes the root div -->
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

const availableComponents = [
  { type: 'navbar', label: 'Navbar (Simple)' },
  { type: 'hero', label: 'Hero (Centered)' },
  { type: 'hero_split', label: 'Hero (Split image)' },
  { type: 'features', label: 'Features Grid' },
  { type: 'stats', label: 'Stats Row' },
  { type: 'pricing', label: 'Pricing Tiers' },
  { type: 'faq', label: 'FAQ Section' },
  { type: 'testimonials', label: 'Testimonials' },
  { type: 'logo_cloud', label: 'Logo Cloud' },
  { type: 'newsletter', label: 'Newsletter Signup' },
  { type: 'team', label: 'Team Section' },
  { type: 'blog_list', label: 'Blog Posts Grid' },
  { type: 'cta', label: 'Call to Action' },
  { type: 'contact', label: 'Contact Form' },
  { type: 'login_form', label: 'Login Form' },
  { type: 'sidebar_nav', label: 'Sidebar Nav' },
  { type: 'metrics', label: 'Metrics Dashboard' },
  { type: 'user_profile', label: 'User Profile' },
  { type: 'table', label: 'Data Table' },
  { type: 'footer', label: 'Footer (Simple)' },
  { type: 'footer_large', label: 'Footer (Complex)' },
]

const layout = ref([])
const isDragOver = ref(false)

const routeConfig = ref({
  routeName: 'my-new-page',
  componentName: 'MyNewPage'
})

const isGenerating = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const activeComponent = ref(null)

const setActive = (item) => {
  activeComponent.value = item;
  if (item.type !== 'row' && !item.content) {
    item.content = {
      heading: '',
      subtext: '',
      buttonText: ''
    };
  }
}

const onDragStart = (e, type, config = null) => {
  e.dataTransfer.setData('componentType', type)
  if (config) {
    e.dataTransfer.setData('config', JSON.stringify(config))
  }
  e.dataTransfer.effectAllowed = 'copy'
}

const onDragOver = (e) => {
  isDragOver.value = true
}

const onDragLeave = () => {
  isDragOver.value = false
}

const onDrop = (e) => {
  isDragOver.value = false
  const type = e.dataTransfer.getData('componentType')
  let config = e.dataTransfer.getData('config')
  if (config) config = JSON.parse(config)
  
  if (type) {
    const newItem = {
      id: Date.now() + Math.random().toString(36).substr(2, 9),
      type
    }
    if (type === 'row' && config) {
      newItem.cols = config.cols
      newItem.columns = Array.from({length: config.cols}, () => [])
      newItem.dragOverCol = null
    } else {
      newItem.content = { heading: '', subtext: '', buttonText: '' }
    }
    layout.value.push(newItem)
    if (type !== 'row') setActive(newItem)
  }
}

const onDropToColumn = (e, rowItem, colIndex) => {
  rowItem.dragOverCol = null
  const type = e.dataTransfer.getData('componentType')
  if (type && type !== 'row') { // Don't nest rows inside rows to keep it simple
    const newSubItem = {
      id: Date.now() + Math.random().toString(36).substr(2, 9),
      type,
      content: { heading: '', subtext: '', buttonText: '' }
    };
    rowItem.columns[colIndex].push(newSubItem)
    setActive(newSubItem)
  } else if (type === 'row') {
    alert("You cannot nest a row inside another row!")
  }
}

const removeComponent = (arr, index) => {
  if (activeComponent.value && activeComponent.value.id === arr[index].id) {
    activeComponent.value = null;
  }
  arr.splice(index, 1)
}

const moveUp = (arr, index) => {
  if (index > 0) {
    const temp = arr[index]
    arr[index] = arr[index - 1]
    arr[index - 1] = temp
  }
}

const moveDown = (arr, index) => {
  if (index < arr.length - 1) {
    const temp = arr[index]
    arr[index] = arr[index + 1]
    arr[index + 1] = temp
  }
}

const clearLayout = () => {
  if(confirm('Are you sure you want to clear the canvas?')) {
    layout.value = []
    successMessage.value = ''
    errorMessage.value = ''
  }
}

const generatePage = async () => {
  if (!routeConfig.value.routeName || !routeConfig.value.componentName) {
    errorMessage.value = 'Please provide both Route Name and Component Name.'
    return
  }
  
  isGenerating.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const response = await axios.post('/frontend-generator/generate', {
      routeName: routeConfig.value.routeName,
      componentName: routeConfig.value.componentName,
      layout: layout.value
    })
    
    successMessage.value = response.data.message || `Page structured and route registered successfully!`
    
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Error generating page.'
  } finally {
    isGenerating.value = false
  }
}
</script>
