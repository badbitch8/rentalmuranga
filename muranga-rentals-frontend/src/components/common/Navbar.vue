<template>
  <nav class="bg-slate-950/95 border-b border-slate-800 shadow-slate-950/40 sticky top-0 z-50 backdrop-blur-xl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <!-- Logo and Brand -->
        <div class="flex items-center">
          <router-link to="/" class="flex items-center space-x-2">
            <svg class="h-8 w-8 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span class="text-xl font-bold text-white">Murang'a Rentals</span>
          </router-link>
        </div>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center space-x-8">
          <router-link to="/" class="text-slate-300 hover:text-gold transition">
            Home
          </router-link>
          <router-link to="/properties" class="text-slate-300 hover:text-gold transition">
            Properties
          </router-link>
          
          <!-- Authenticated User Menu -->
          <div v-if="authStore.isAuthenticated" class="flex items-center space-x-4">
            <router-link to="/dashboard" class="text-slate-300 hover:text-gold transition">
              Dashboard
            </router-link>
            
            <!-- User Dropdown -->
            <div class="relative" ref="dropdownRef">
              <button 
                @click="toggleDropdown"
                class="flex items-center space-x-2 text-slate-300 hover:text-gold transition"
              >
                <div class="h-8 w-8 rounded-full bg-gold/15 border border-gold/30 flex items-center justify-center text-gold font-semibold">
                  {{ userInitials }}
                </div>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <!-- Dropdown Menu -->
              <div 
                v-show="showDropdown"
                class="absolute right-0 mt-2 w-48 bg-slate-950/95 border border-slate-800 rounded-2xl shadow-2xl shadow-black/30 py-2 z-50"
              >
                <div class="px-4 py-3 border-b border-slate-800">
                  <p class="text-sm font-semibold text-white">{{ authStore.userName }}</p>
                  <p class="text-xs text-slate-400">{{ authStore.userEmail }}</p>
                </div>
                <router-link 
                  to="/profile" 
                  class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-900 rounded-lg"
                  @click="showDropdown = false"
                >
                  My Profile
                </router-link>
                <router-link 
                  to="/bookings" 
                  class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-900 rounded-lg"
                  @click="showDropdown = false"
                >
                  My Bookings
                </router-link>
                <router-link 
                  to="/favorites" 
                  class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-900 rounded-lg"
                  @click="showDropdown = false"
                >
                  Favorites
                </router-link>
                <button 
                  @click="handleLogout"
                  class="block w-full text-left px-4 py-2 text-sm text-rose-300 hover:bg-slate-900 rounded-lg"
                >
                  Logout
                </button>
              </div>
            </div>
          </div>

          <!-- Guest Menu -->
          <div v-else class="flex items-center space-x-4">
            <router-link 
              to="/login" 
              class="text-slate-300 hover:text-gold transition"
            >
              Login
            </router-link>
            <router-link 
              to="/register" 
              class="btn-primary"
            >
              Sign Up
            </router-link>
          </div>
        </div>

        <!-- Mobile Menu Button -->
        <div class="md:hidden flex items-center">
          <button 
            @click="toggleMobileMenu"
            class="text-gray-700 hover:text-primary-600"
          >
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path v-if="!showMobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div v-show="showMobileMenu" class="md:hidden border-t border-slate-800 bg-slate-950/95">
      <div class="px-2 pt-2 pb-3 space-y-1">
        <router-link 
          to="/" 
          class="block px-3 py-2 text-slate-300 hover:bg-slate-900 rounded-lg"
          @click="showMobileMenu = false"
        >
          Home
        </router-link>
        <router-link 
          to="/properties" 
          class="block px-3 py-2 text-slate-300 hover:bg-slate-900 rounded-lg"
          @click="showMobileMenu = false"
        >
          Properties
        </router-link>
        
        <div v-if="authStore.isAuthenticated">
          <router-link 
            to="/dashboard" 
            class="block px-3 py-2 text-slate-300 hover:bg-slate-900 rounded-lg"
            @click="showMobileMenu = false"
          >
            Dashboard
          </router-link>
          <router-link 
            to="/profile" 
            class="block px-3 py-2 text-slate-300 hover:bg-slate-900 rounded-lg"
            @click="showMobileMenu = false"
          >
            Profile
          </router-link>
          <button 
            @click="handleLogout"
            class="block w-full text-left px-3 py-2 text-rose-300 hover:bg-slate-900 rounded-lg"
          >
            Logout
          </button>
        </div>
        
        <div v-else>
          <router-link 
            to="/login" 
            class="block px-3 py-2 text-slate-300 hover:bg-slate-900 rounded-lg"
            @click="showMobileMenu = false"
          >
            Login
          </router-link>
          <router-link 
            to="/register" 
            class="block px-3 py-2 text-gold hover:bg-slate-900 rounded-lg font-semibold"
            @click="showMobileMenu = false"
          >
            Sign Up
          </router-link>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const showDropdown = ref(false)
const showMobileMenu = ref(false)
const dropdownRef = ref(null)

const userInitials = computed(() => {
  const name = authStore.userName
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
}

const toggleMobileMenu = () => {
  showMobileMenu.value = !showMobileMenu.value
}

const handleLogout = async () => {
  try {
    await authStore.logout()
    showDropdown.value = false
    showMobileMenu.value = false
    router.push('/login')
  } catch (error) {
    console.error('Logout error:', error)
  }
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    showDropdown.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>