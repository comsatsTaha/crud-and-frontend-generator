/**
 * Core logic for generating Vue 3 component code based on user configuration.
 */
export function useFrontendGenerator() {
    /**
     * Generates a complete Vue SFC string based on provided config.
     * 
     * @param {Object} config - Configuration object
     * @param {string} config.template - 'login' | 'dashboard' | 'landing' | 'table' | 'profile'
     * @param {string} config.primaryColor - e.g. '#3B82F6' or tailwind class
     * @param {boolean} config.darkMode - whether dark mode should be supported
     * @param {Array<string>} config.components - e.g. ['navbar', 'sidebar', 'footer']
     * @param {string} config.projectName - e.g. 'MyApp'
     * @returns {string} - The generated Vue text
     */
    const generateCode = (config) => {
        switch (config.template) {
            case 'login':
                return generateLogin(config);
            case 'dashboard':
                return generateDashboard(config);
            case 'landing':
                return generateLanding(config);
            case 'table':
                return generateTable(config);
            case 'profile':
                return generateProfile(config);
            default:
                return '<!-- Template not found -->';
        }
    };

    /**
     * Generates a Login Page template.
     * Includes email/password form, logo placeholder, submit button.
     */
    const generateLogin = (config) => {
        const darkClass = config.darkMode ? 'dark:bg-gray-900 dark:text-white' : '';
        const darkCardClass = config.darkMode ? 'dark:bg-gray-800 dark:border-gray-700' : '';
        const darkInputClass = config.darkMode ? 'dark:bg-gray-700 dark:border-gray-600 dark:text-white' : '';
        const darkLabelClass = config.darkMode ? 'dark:text-gray-300' : 'text-gray-700';

        const styleBlock = config.primaryColor.startsWith('#') 
            ? `<style scoped>\nbutton.bg-primary {\n  background-color: ${config.primaryColor};\n}\nbutton.bg-primary:hover {\n  opacity: 0.9;\n}\n.text-primary {\n  color: ${config.primaryColor};\n}\n</style>`
            : '';

        const primaryBtnClasses = config.primaryColor.startsWith('#')
            ? 'bg-primary text-white font-semibold rounded-lg hover:shadow-md transition duration-200'
            : `bg-${config.primaryColor} hover:bg-${config.primaryColor}-dark text-white font-semibold rounded-lg hover:shadow-md transition duration-200`;

        const primaryTextClass = config.primaryColor.startsWith('#') ? 'text-primary' : `text-${config.primaryColor}`;

        return `<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8 ${darkClass}">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-lg border border-gray-100 ${darkCardClass}">
      <div>
        <h2 class="mt-4 text-center text-3xl font-extrabold text-gray-900 ${config.darkMode ? 'dark:text-white' : ''}">
          Sign in to ${config.projectName}
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600 ${config.darkMode ? 'dark:text-gray-400' : ''}">
          Or
          <a href="#" class="font-medium hover:underline ${primaryTextClass}">
            create an account
          </a>
        </p>
      </div>
      <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
        <div class="rounded-md shadow-sm space-y-4">
          <div>
            <label for="email-address" class="block text-sm font-medium ${darkLabelClass}">Email address</label>
            <input id="email-address" name="email" type="email" autocomplete="email" required v-model="form.email"
              class="mt-1 appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-2 focus:ring-opacity-50 focus:border-transparent sm:text-sm ${darkInputClass}"
              placeholder="you@example.com" />
          </div>
          <div>
            <label for="password" class="block text-sm font-medium ${darkLabelClass}">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required v-model="form.password"
              class="mt-1 appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-2 focus:ring-opacity-50 focus:border-transparent sm:text-sm ${darkInputClass}"
              placeholder="••••••••" />
          </div>
        </div>

        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input id="remember-me" name="remember-me" type="checkbox" v-model="form.remember"
              class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded ${darkInputClass}" />
            <label for="remember-me" class="ml-2 block text-sm ${darkLabelClass}">
              Remember me
            </label>
          </div>

          <div class="text-sm">
            <a href="#" class="font-medium hover:underline ${primaryTextClass}">
              Forgot your password?
            </a>
          </div>
        </div>

        <div>
          <button type="submit"
            class="group relative w-full flex justify-center py-2.5 px-4 ${primaryBtnClasses}">
            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
              <svg class="h-5 w-5 text-white group-hover:text-opacity-80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
              </svg>
            </span>
            Sign in
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const form = ref({
  email: '',
  password: '',
  remember: false
})

const handleSubmit = () => {
  console.log('Form submitted:', form.value)
  // Implement login logic here
}
</script>

${styleBlock}`;
    };

    /**
     * Generates a Dashboard Page template.
     * Includes sidebar, topbar, stats cards, recent activity table.
     */
    const generateDashboard = (config) => {
        const darkBgClass = config.darkMode ? 'dark:bg-gray-900' : 'bg-gray-100';
        const darkSurfaceClass = config.darkMode ? 'dark:bg-gray-800 dark:border-gray-700 dark:text-white' : 'bg-white text-gray-900 border-gray-200';
        const textMutedClass = config.darkMode ? 'dark:text-gray-400' : 'text-gray-500';
        
        const hasSidebar = config.components && config.components.includes('sidebar');
        const hasNavbar = config.components && config.components.includes('navbar');
        
        const styleBlock = config.primaryColor.startsWith('#') 
            ? `<style scoped>\n.bg-primary {\n  background-color: ${config.primaryColor};\n}\n.text-primary {\n  color: ${config.primaryColor};\n}\n</style>`
            : '';

        const primaryTextClass = config.primaryColor.startsWith('#') ? 'text-primary' : `text-${config.primaryColor}`;
        const primaryBgClass = config.primaryColor.startsWith('#') ? 'bg-primary' : `bg-${config.primaryColor}`;

        let sidebarHtml = '';
        let navbarHtml = '';

        if (hasSidebar) {
            sidebarHtml = `
    <!-- Sidebar -->
    <aside class="hidden md:flex flex-col w-64 border-r ${darkSurfaceClass}">
      <div class="h-16 flex items-center px-6 border-b ${darkSurfaceClass}">
        <span class="text-xl font-bold ${primaryTextClass}">${config.projectName}</span>
      </div>
      <nav class="flex-1 px-4 py-4 space-y-2 overflow-y-auto">
        <a href="#" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg ${primaryBgClass} text-white">
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
          Dashboard
        </a>
        <a href="#" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg hover:bg-gray-100 ${config.darkMode ? 'dark:hover:bg-gray-700' : ''} transition-colors">
          <svg class="w-5 h-5 mr-3 ${textMutedClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
          Team
        </a>
        <a href="#" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg hover:bg-gray-100 ${config.darkMode ? 'dark:hover:bg-gray-700' : ''} transition-colors">
          <svg class="w-5 h-5 mr-3 ${textMutedClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
          Reports
        </a>
      </nav>
    </aside>`;
        }

        if (hasNavbar) {
            navbarHtml = `
      <!-- Top Navbar -->
      <header class="h-16 flex items-center justify-between px-6 border-b ${darkSurfaceClass}">
        <div class="flex items-center lg:hidden">
          <button class="text-gray-500 focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
          </button>
        </div>
        <div class="flex ml-auto items-center space-x-4">
          <button class="text-gray-500 hover:${primaryTextClass} focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
          </button>
          <div class="h-8 w-8 rounded-full bg-gray-300 border-2 border-white ${config.darkMode ? 'dark:border-gray-800' : ''}"></div>
        </div>
      </header>`;
        }

        return `<template>
  <div class="flex h-screen overflow-hidden ${darkBgClass} font-sans">
    ${sidebarHtml}
    
    <!-- Main content container -->
    <div class="flex-1 flex flex-col overflow-hidden">
      ${navbarHtml}

      <!-- Main Content -->
      <main class="flex-1 overflow-x-hidden overflow-y-auto ${darkBgClass} p-6">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-semibold ${config.darkMode ? 'text-white' : 'text-gray-900'}">Overview</h1>
          <button class="${primaryBgClass} text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity">
            Download Report
          </button>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div v-for="stat in stats" :key="stat.name" class="p-6 rounded-xl border shadow-sm ${darkSurfaceClass}">
            <div class="flex items-center">
              <div class="p-3 rounded-full ${primaryBgClass} bg-opacity-10 ${primaryTextClass}">
                <component :is="stat.icon" class="w-6 h-6" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium ${textMutedClass}">{{ stat.name }}</p>
                <p class="text-2xl font-semibold">{{ stat.value }}</p>
              </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
              <span :class="stat.changeType === 'increase' ? 'text-green-500' : 'text-red-500'" class="flex items-center font-medium">
                <svg v-if="stat.changeType === 'increase'" class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                <svg v-else class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                {{ stat.change }}
              </span>
              <span class="ml-2 ${textMutedClass}">vs last month</span>
            </div>
          </div>
        </div>

        <!-- Recent Activity Table -->
        <div class="rounded-xl border shadow-sm overflow-hidden ${darkSurfaceClass}">
          <div class="px-6 py-4 border-b ${config.darkMode ? 'dark:border-gray-700' : 'border-gray-200'}">
            <h3 class="text-lg font-medium">Recent Activity</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y ${config.darkMode ? 'dark:divide-gray-700' : 'divide-gray-200'}">
              <thead class="${config.darkMode ? 'dark:bg-gray-800' : 'bg-gray-50'}">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium ${textMutedClass} uppercase tracking-wider">User</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium ${textMutedClass} uppercase tracking-wider">Action</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium ${textMutedClass} uppercase tracking-wider">Date</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium ${textMutedClass} uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y ${config.darkMode ? 'dark:divide-gray-700' : 'divide-gray-200'}">
                <tr v-for="(activity, index) in recentActivities" :key="index">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="h-8 w-8 rounded-full bg-gray-200 flex-shrink-0"></div>
                      <div class="ml-4">
                        <div class="text-sm font-medium">{{ activity.user }}</div>
                        <div class="text-sm ${textMutedClass}">{{ activity.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">{{ activity.action }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm ${textMutedClass}">{{ activity.date }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 ${config.darkMode ? 'dark:bg-green-900 dark:text-green-200' : ''}">
                      {{ activity.status }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const stats = ref([
  { name: 'Total Revenue', value: '$45,231', change: '12%', changeType: 'increase', icon: 'svg' },
  { name: 'Active Users', value: '2,403', change: '8%', changeType: 'increase', icon: 'svg' },
  { name: 'New Signups', value: '342', change: '4%', changeType: 'decrease', icon: 'svg' },
  { name: 'Bounce Rate', value: '24%', change: '2%', changeType: 'decrease', icon: 'svg' },
])

const recentActivities = ref([
  { user: 'Jane Cooper', email: 'jane@example.com', action: 'Created new project', date: '4 mins ago', status: 'Completed' },
  { user: 'Cody Fisher', email: 'cody@example.com', action: 'Updated settings', date: '21 mins ago', status: 'Completed' },
  { user: 'Esther Howard', email: 'esther@example.com', action: 'Deleted user', date: '1 hour ago', status: 'Completed' },
  { user: 'Jenny Wilson', email: 'jenny@example.com', action: 'Uploaded document', date: '3 hours ago', status: 'Completed' },
])
</script>

${styleBlock}`;
    };

    /**
     * Generates a Landing Page template.
     */
    const generateLanding = (config) => {
        const darkBgClass = config.darkMode ? 'dark:bg-gray-900' : 'bg-white';
        const darkTextClass = config.darkMode ? 'dark:text-white' : 'text-gray-900';
        const darkMutedClass = config.darkMode ? 'dark:text-gray-400' : 'text-gray-500';
        const darkSurfaceClass = config.darkMode ? 'dark:bg-gray-800' : 'bg-gray-50';

        const styleBlock = config.primaryColor.startsWith('#') 
            ? `<style scoped>\n.text-primary {\n  color: ${config.primaryColor};\n}\n.bg-primary {\n  background-color: ${config.primaryColor};\n}\n.bg-primary:hover {\n  opacity: 0.9;\n}\n</style>`
            : '';

        const primaryText = config.primaryColor.startsWith('#') ? 'text-primary' : `text-${config.primaryColor}`;
        const primaryBg = config.primaryColor.startsWith('#') ? 'bg-primary' : `bg-${config.primaryColor}`;

        return `<template>
  <div class="min-h-screen ${darkBgClass} font-sans">
    <!-- Navbar -->
    <header class="py-6 px-4 sm:px-6 lg:px-8 border-b border-gray-100 ${config.darkMode ? 'dark:border-gray-800' : ''}">
      <div class="max-w-7xl mx-auto flex justify-between items-center">
        <div class="text-2xl font-bold ${primaryText}">${config.projectName}</div>
        <nav class="hidden md:flex space-x-8">
          <a href="#" class="${darkMutedClass} hover:${darkTextClass}">Features</a>
          <a href="#" class="${darkMutedClass} hover:${darkTextClass}">Pricing</a>
          <a href="#" class="${darkMutedClass} hover:${darkTextClass}">Testimonials</a>
        </nav>
        <div class="flex items-center space-x-4">
          <a href="#" class="font-medium ${darkTextClass} hover:${primaryText}">Log in</a>
          <a href="#" class="px-4 py-2 rounded-lg text-white font-medium ${primaryBg} transition-opacity">Sign up</a>
        </div>
      </div>
    </header>

    <!-- Hero Section -->
    <main>
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-16 text-center">
        <h1 class="text-5xl tracking-tight font-extrabold ${darkTextClass} sm:text-6xl md:text-7xl">
          <span class="block">The better way to</span>
          <span class="block ${primaryText}">build your web apps</span>
        </h1>
        <p class="mt-3 max-w-md mx-auto text-base ${darkMutedClass} sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
          Everything you need to create amazing digital experiences. Start saving hours of development time today with our completely customizable component library.
        </p>
        <div class="mt-10 max-w-sm mx-auto sm:max-w-none sm:flex sm:justify-center">
          <div class="space-y-4 sm:space-y-0 sm:space-x-4 flex flex-col sm:flex-row">
            <a href="#" class="w-full sm:w-auto flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white ${primaryBg} md:py-4 md:text-lg">
              Get started
            </a>
            <a href="#" class="w-full sm:w-auto flex items-center justify-center px-8 py-3 border border-gray-300 ${config.darkMode ? 'dark:border-gray-600' : ''} text-base font-medium rounded-md ${darkTextClass} bg-transparent hover:${darkSurfaceClass} md:py-4 md:text-lg">
              Live demo
            </a>
          </div>
        </div>
      </div>

      <!-- Features Grid -->
      <div class="py-16 ${darkSurfaceClass}">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="text-center">
            <h2 class="text-base font-semibold tracking-wide uppercase ${primaryText}">Features</h2>
            <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight ${darkTextClass} sm:text-4xl">
              A better way to build
            </p>
          </div>

          <div class="mt-16">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
              <div v-for="feature in features" :key="feature.name" class="p-6 bg-white ${config.darkMode ? 'dark:bg-gray-700' : ''} rounded-xl shadow-sm">
                <div class="w-12 h-12 rounded-md ${primaryBg} text-white flex items-center justify-center mb-4">
                  <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                </div>
                <h3 class="text-lg font-medium ${darkTextClass}">{{ feature.name }}</h3>
                <p class="mt-2 text-base ${darkMutedClass}">{{ feature.description }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 ${config.darkMode ? 'dark:bg-gray-950' : ''} py-12 px-4 sm:px-6 lg:px-8 mt-auto">
      <div class="max-w-7xl mx-auto text-center">
        <p class="text-base text-gray-400">&copy; 2024 ${config.projectName}, Inc. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const features = ref([
  { name: 'Lightning Fast', description: 'Built on modern web technologies for optimal performance.' },
  { name: 'Fully Responsive', description: 'Looks perfect on any device, from mobile phones to huge desktop monitors.' },
  { name: 'Highly Customisable', description: 'Tweak every detail to match your exact brand guidelines and requirements.' }
])
</script>

${styleBlock}`;
    };

    /**
     * Generates a CRUD Table template.
     */
    const generateTable = (config) => {
        const darkBgClass = config.darkMode ? 'dark:bg-gray-900' : 'bg-gray-100';
        const darkSurfaceClass = config.darkMode ? 'dark:bg-gray-800 dark:border-gray-700' : 'bg-white border-gray-200';
        const darkTextClass = config.darkMode ? 'dark:text-white' : 'text-gray-900';
        const darkMutedClass = config.darkMode ? 'dark:text-gray-400' : 'text-gray-500';

        const styleBlock = config.primaryColor.startsWith('#') 
            ? `<style scoped>\n.bg-primary {\n  background-color: ${config.primaryColor};\n}\n.bg-primary:hover {\n  opacity: 0.9;\n}\n.text-primary {\n  color: ${config.primaryColor};\n}\n</style>`
            : '';

        const primaryBg = config.primaryColor.startsWith('#') ? 'bg-primary' : `bg-${config.primaryColor}`;
        const primaryText = config.primaryColor.startsWith('#') ? 'text-primary' : `text-${config.primaryColor}`;

        return `<template>
  <div class="min-h-screen p-8 ${darkBgClass} font-sans">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold ${darkTextClass}">Users Library</h1>
          <p class="text-sm mt-1 ${darkMutedClass}">Manage all your registered users</p>
        </div>
        <div class="mt-4 md:mt-0">
          <button class="${primaryBg} text-white px-4 py-2 rounded-lg shadow-sm font-medium transition-opacity flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Add New User
          </button>
        </div>
      </div>

      <!-- Table Card -->
      <div class="${darkSurfaceClass} rounded-xl shadow-sm border overflow-hidden">
        
        <!-- Toolbar -->
        <div class="p-4 border-b ${config.darkMode ? 'dark:border-gray-700' : 'border-gray-200'} flex justify-between items-center bg-gray-50 ${config.darkMode ? 'dark:bg-gray-800/50' : ''}">
          <div class="relative max-w-sm w-full">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 ${darkMutedClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 ${config.darkMode ? 'dark:border-gray-600 dark:bg-gray-700 dark:text-white' : ''} rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Search users...">
          </div>
          <div>
            <button class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md ${darkTextClass} bg-white ${config.darkMode ? 'dark:bg-gray-700 dark:border-gray-600' : ''} hover:bg-gray-50 focus:outline-none">
              <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
              Filter
            </button>
          </div>
        </div>

        <!-- Data Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y ${config.darkMode ? 'dark:divide-gray-700' : 'divide-gray-200'}">
            <thead class="${config.darkMode ? 'dark:bg-gray-800' : 'bg-gray-50'}">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium ${darkMutedClass} uppercase tracking-wider">Name</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium ${darkMutedClass} uppercase tracking-wider">Role</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium ${darkMutedClass} uppercase tracking-wider">Status</th>
                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
              </tr>
            </thead>
            <tbody class="divide-y ${config.darkMode ? 'dark:divide-gray-700' : 'divide-gray-200'}">
              <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 ${config.darkMode ? 'dark:hover:bg-gray-700/50' : ''}">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="h-10 w-10 rounded-full bg-gray-200 flex-shrink-0 flex items-center justify-center ${primaryText} font-bold">
                      {{ user.name.charAt(0) }}
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium ${darkTextClass}">{{ user.name }}</div>
                      <div class="text-sm ${darkMutedClass}">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm ${darkTextClass}">{{ user.role }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                    :class="user.active ? 'bg-green-100 text-green-800 ${config.darkMode ? 'dark:bg-green-900 dark:text-green-200' : ''}' : 'bg-red-100 text-red-800 ${config.darkMode ? 'dark:bg-red-900 dark:text-red-200' : ''}'">
                    {{ user.active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <a href="#" class="${primaryText} hover:opacity-80 mr-3">Edit</a>
                  <a href="#" class="text-red-600 hover:text-red-900">Delete</a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-4 py-3 border-t ${config.darkMode ? 'dark:border-gray-700' : 'border-gray-200'} flex items-center justify-between sm:px-6 bg-white ${config.darkMode ? 'dark:bg-gray-800' : ''}">
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm ${darkMutedClass}">
                Showing <span class="font-medium">1</span> to <span class="font-medium">10</span> of <span class="font-medium">97</span> results
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white ${config.darkMode ? 'dark:bg-gray-700 dark:border-gray-600' : ''} text-sm font-medium ${darkMutedClass} hover:bg-gray-50">
                  <span class="sr-only">Previous</span>
                  <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                </a>
                <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium ${primaryBg} text-white">1</a>
                <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white ${config.darkMode ? 'dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300' : ''} text-sm font-medium ${darkTextClass} hover:bg-gray-50">2</a>
                <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white ${config.darkMode ? 'dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300' : ''} text-sm font-medium ${darkTextClass} hover:bg-gray-50">3</a>
                <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white ${config.darkMode ? 'dark:bg-gray-700 dark:border-gray-600' : ''} text-sm font-medium ${darkMutedClass} hover:bg-gray-50">
                  <span class="sr-only">Next</span>
                  <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                </a>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const users = ref([
  { id: 1, name: 'Lindsay Walton', email: 'lindsay.walton@example.com', role: 'Front-end Developer', active: true },
  { id: 2, name: 'Courtney Henry', email: 'courtney.henry@example.com', role: 'Designer', active: false },
  { id: 3, name: 'Tom Cook', email: 'tom.cook@example.com', role: 'Director of Product', active: true },
  { id: 4, name: 'Whitney Francis', email: 'whitney.francis@example.com', role: 'Copywriter', active: true },
  { id: 5, name: 'Leonard Krasner', email: 'leonard.krasner@example.com', role: 'Senior Designer', active: true },
])
</script>

${styleBlock}`;
    };

    /**
     * Generates a Profile Page template.
     */
    const generateProfile = (config) => {
        const darkBgClass = config.darkMode ? 'dark:bg-gray-900' : 'bg-gray-50';
        const darkSurfaceClass = config.darkMode ? 'dark:bg-gray-800 border-gray-700' : 'bg-white border-gray-200';
        const darkTextClass = config.darkMode ? 'dark:text-white' : 'text-gray-900';
        const darkMutedClass = config.darkMode ? 'dark:text-gray-400' : 'text-gray-500';

        const styleBlock = config.primaryColor.startsWith('#') 
            ? `<style scoped>\n.bg-primary {\n  background-color: ${config.primaryColor};\n}\n.bg-primary:hover {\n  opacity: 0.9;\n}\n.text-primary {\n  color: ${config.primaryColor};\n}\n.border-primary {\n  border-color: ${config.primaryColor};\n}\n</style>`
            : '';

        const primaryBg = config.primaryColor.startsWith('#') ? 'bg-primary' : `bg-${config.primaryColor}`;
        const primaryText = config.primaryColor.startsWith('#') ? 'text-primary' : `text-${config.primaryColor}`;
        const primaryBorder = config.primaryColor.startsWith('#') ? 'border-primary' : `border-${config.primaryColor}`;

        return `<template>
  <div class="min-h-screen ${darkBgClass} py-10 font-sans">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <div class="mb-8">
        <h1 class="text-3xl font-bold ${darkTextClass}">Profile Settings</h1>
        <p class="mt-1 text-sm ${darkMutedClass}">Update your personal information and preferences.</p>
      </div>

      <div class="${darkSurfaceClass} shadow rounded-lg mb-8 overflow-hidden">
        <!-- Avatar Section -->
        <div class="p-6 border-b ${config.darkMode ? 'dark:border-gray-700' : 'border-gray-200'}">
          <div class="flex items-center">
            <div class="h-24 w-24 rounded-full bg-gray-200 flex-shrink-0 relative overflow-hidden group">
              <img src="https://ui-avatars.com/api/?name=John+Doe&size=150" alt="Avatar" class="w-full h-full object-cover">
              <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                <span class="text-white text-xs font-medium">Change</span>
              </div>
            </div>
            <div class="ml-6">
              <h2 class="text-xl font-medium ${darkTextClass}">John Doe</h2>
              <p class="text-sm ${darkMutedClass} mb-2">john.doe@example.com</p>
              <div class="flex space-x-3">
                <button class="px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded text-gray-700 bg-white hover:bg-gray-50 ${config.darkMode ? 'dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600' : ''}">Upload New</button>
                <button class="px-3 py-1.5 text-sm font-medium text-red-600 hover:text-red-800">Remove</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Section -->
        <div class="p-6">
          <form @submit.prevent="saveProfile" class="space-y-6">
            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
              <div class="sm:col-span-3">
                <label for="first-name" class="block text-sm font-medium ${darkTextClass}">First name</label>
                <div class="mt-1">
                  <input type="text" id="first-name" v-model="form.firstName" class="shadow-sm focus:ring-${config.primaryColor} focus:${primaryBorder} block w-full sm:text-sm border-gray-300 rounded-md ${config.darkMode ? 'dark:bg-gray-700 dark:border-gray-600 dark:text-white' : ''}">
                </div>
              </div>

              <div class="sm:col-span-3">
                <label for="last-name" class="block text-sm font-medium ${darkTextClass}">Last name</label>
                <div class="mt-1">
                  <input type="text" id="last-name" v-model="form.lastName" class="shadow-sm focus:ring-${config.primaryColor} focus:${primaryBorder} block w-full sm:text-sm border-gray-300 rounded-md ${config.darkMode ? 'dark:bg-gray-700 dark:border-gray-600 dark:text-white' : ''}">
                </div>
              </div>

              <div class="sm:col-span-6">
                <label for="email" class="block text-sm font-medium ${darkTextClass}">Email address</label>
                <div class="mt-1">
                  <input id="email" type="email" v-model="form.email" class="shadow-sm focus:ring-${config.primaryColor} focus:${primaryBorder} block w-full sm:text-sm border-gray-300 rounded-md ${config.darkMode ? 'dark:bg-gray-700 dark:border-gray-600 dark:text-white' : ''}">
                </div>
              </div>

              <div class="sm:col-span-6">
                <label for="bio" class="block text-sm font-medium ${darkTextClass}">Bio</label>
                <div class="mt-1">
                  <textarea id="bio" rows="3" v-model="form.bio" class="shadow-sm focus:ring-${config.primaryColor} focus:${primaryBorder} block w-full sm:text-sm border-gray-300 rounded-md ${config.darkMode ? 'dark:bg-gray-700 dark:border-gray-600 dark:text-white' : ''}"></textarea>
                </div>
                <p class="mt-2 text-sm ${darkMutedClass}">Write a few sentences about yourself.</p>
              </div>
            </div>

            <div class="pt-5 border-t ${config.darkMode ? 'dark:border-gray-700' : 'border-gray-200'} flex justify-end">
              <button type="button" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none ${config.darkMode ? 'dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600' : ''}">
                Cancel
              </button>
              <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white ${primaryBg} focus:outline-none">
                Save Changes
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const form = ref({
  firstName: 'John',
  lastName: 'Doe',
  email: 'john.doe@example.com',
  bio: 'Software engineer and UI/UX designer building awesome tools for developers.'
})

const saveProfile = () => {
  console.log('Profile saved:', form.value)
  // Save logic here
}
</script>

${styleBlock}`;
    };

    return {
        generateCode
    };
}
