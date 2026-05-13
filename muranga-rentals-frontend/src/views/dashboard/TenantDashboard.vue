<template>
  <div class="min-h-screen bg-slate-950 text-slate-100">
    <div class="container-wide py-6 md:py-8">
      <!-- Welcome Section -->
      <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">
          Welcome back, {{ user?.name }}!
        </h1>
        <p class="text-sm md:text-base text-slate-400">
          Manage your bookings, favorites, and profile from your dashboard
        </p>
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-slate-400 mb-1">Active Bookings</p>
              <p class="text-2xl font-bold text-white">{{ stats.activeBookings }}</p>
            </div>
            <div class="w-10 h-10 bg-slate-900/70 rounded-lg flex items-center justify-center">
              <svg class="icon-md text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-slate-400 mb-1">Saved Properties</p>
              <p class="text-2xl font-bold text-white">{{ stats.savedProperties }}</p>
            </div>
            <div class="w-10 h-10 bg-slate-900/70 rounded-lg flex items-center justify-center">
              <svg class="icon-md text-rose-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-slate-400 mb-1">Messages</p>
              <p class="text-2xl font-bold text-white">{{ stats.unreadMessages }}</p>
            </div>
            <div class="w-10 h-10 bg-slate-900/70 rounded-lg flex items-center justify-center">
              <svg class="icon-md text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-slate-400 mb-1">Maintenance</p>
              <p class="text-2xl font-bold text-white">{{ stats.maintenanceRequests }}</p>
            </div>
            <div class="w-10 h-10 bg-slate-900/70 rounded-lg flex items-center justify-center">
              <svg class="icon-md text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Recent Activity -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Current Bookings -->
          <div class="card">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-semibold text-white">Current Bookings</h2>
              <router-link to="/bookings" class="link text-sm">View All</router-link>
            </div>

            <LoadingSpinner v-if="loadingBookings" size="sm" />

            <div v-else-if="currentBookings.length > 0" class="space-y-3">
              <div
                v-for="booking in currentBookings"
                :key="booking.id"
                class="border border-slate-800 rounded-2xl p-4 hover:border-slate-700 transition-colors bg-slate-950/70"
              >
                <div class="flex items-start justify-between mb-2">
                  <div class="flex-1">
                    <h3 class="font-semibold text-white mb-1">{{ booking.property?.title }}</h3>
                    <p class="text-sm text-slate-400">{{ booking.property?.location }}</p>
                  </div>
                  <span :class="getStatusBadgeClass(booking.status)" class="badge">
                    {{ booking.status }}
                  </span>
                </div>
                <div class="flex items-center justify-between text-sm text-slate-400">
                  <span>Move-in: {{ formatDate(booking.move_in_date) }}</span>
                  <span class="font-semibold text-white">KES {{ formatNumber(booking.total_amount) }}</span>
                </div>
              </div>
            </div>

            <div v-else class="text-center py-8 text-slate-400">
              <svg class="icon-xl mx-auto mb-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
              <p class="text-sm">No active bookings</p>
              <router-link to="/properties" class="btn-primary mt-3 inline-block">
                Browse Properties
              </router-link>
            </div>
          </div>

          <!-- Saved Properties -->
          <div class="card">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-semibold text-white">Saved Properties</h2>
              <router-link to="/favorites" class="link text-sm">View All</router-link>
            </div>

            <LoadingSpinner v-if="loadingFavorites" size="sm" />

            <div v-else-if="savedProperties.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div
                v-for="property in savedProperties.slice(0, 4)"
                :key="property.id"
                class="border border-slate-800 rounded-2xl overflow-hidden hover:border-slate-700 transition-colors cursor-pointer bg-slate-950/70"
                @click="$router.push(`/properties/${property.id}`)"
              >
                <img
                  :src="property.images?.[0]?.url || '/placeholder-property.jpg'"
                  :alt="property.title"
                  class="w-full h-32 object-cover"
                />
                <div class="p-3">
                  <h3 class="font-semibold text-sm text-white mb-1 line-clamp-1">{{ property.title }}</h3>
                  <p class="text-xs text-slate-400 mb-2">{{ property.location }}</p>
                  <p class="text-sm font-bold text-gold">KES {{ formatNumber(property.rent_amount) }}/mo</p>
                </div>
              </div>
            </div>

            <div v-else class="text-center py-8 text-slate-400">
              <svg class="icon-xl mx-auto mb-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
              </svg>
              <p class="text-sm">No saved properties yet</p>
            </div>
          </div>
        </div>

        <!-- Right Column - Quick Actions & Info -->
        <div class="space-y-6">
          <!-- Quick Actions -->
          <div class="card">
            <h2 class="text-lg font-semibold text-white mb-4">Quick Actions</h2>
            <div class="space-y-2">
              <router-link to="/properties" class="btn-outline w-full flex items-center justify-center gap-2">
                <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Search Properties
              </router-link>
              <router-link to="/messages" class="btn-outline w-full flex items-center justify-center gap-2">
                <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                View Messages
              </router-link>
              <router-link to="/profile" class="btn-outline w-full flex items-center justify-center gap-2">
                <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Edit Profile
              </router-link>
            </div>
          </div>

          <!-- Recent Messages -->
          <div class="card">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-semibold text-white">Recent Messages</h2>
              <router-link to="/messages" class="link text-sm">View All</router-link>
            </div>

            <div v-if="recentMessages.length > 0" class="space-y-3">
              <div
                v-for="message in recentMessages"
                :key="message.id"
                class="flex items-start gap-3 p-2 hover:bg-slate-900 rounded-2xl cursor-pointer transition-colors"
                @click="$router.push('/messages')"
              >
                <div class="w-10 h-10 bg-slate-900/80 rounded-full flex items-center justify-center flex-shrink-0">
                  <span class="text-sm font-semibold text-slate-300">{{ message.sender?.name?.charAt(0) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-semibold text-white">{{ message.sender?.name }}</p>
                  <p class="text-xs text-slate-400 truncate">{{ message.content }}</p>
                  <p class="text-xs text-slate-500 mt-1">{{ formatTimeAgo(message.created_at) }}</p>
                </div>
              </div>
            </div>

            <div v-else class="text-center py-6 text-slate-400">
              <p class="text-sm">No messages yet</p>
            </div>
          </div>

          <!-- Tips -->
          <div class="card bg-slate-900/75 border-slate-800">
            <div class="flex items-start gap-3">
              <div class="w-8 h-8 bg-slate-900/70 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="icon-sm text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div>
                <h3 class="text-sm font-semibold text-white mb-1">Rental Tip</h3>
                <p class="text-xs text-slate-400">
                  Always schedule a property viewing before making a booking. Verify all amenities and ask about utility costs.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'

const authStore = useAuthStore()
const user = computed(() => authStore.user)

const stats = ref({
  activeBookings: 0,
  savedProperties: 0,
  unreadMessages: 0,
  maintenanceRequests: 0
})

const currentBookings = ref([])
const savedProperties = ref([])
const recentMessages = ref([])
const loadingBookings = ref(false)
const loadingFavorites = ref(false)

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  })
}

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US').format(num)
}

const formatTimeAgo = (date) => {
  const seconds = Math.floor((new Date() - new Date(date)) / 1000)
  if (seconds < 60) return 'Just now'
  if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`
  if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`
  return `${Math.floor(seconds / 86400)}d ago`
}

const getStatusBadgeClass = (status) => {
  const classes = {
    pending: 'badge-warning',
    confirmed: 'badge-success',
    active: 'badge-primary',
    completed: 'badge',
    cancelled: 'badge-error'
  }
  return classes[status] || 'badge'
}

const loadDashboardData = async () => {
  // TODO: Implement API calls to fetch dashboard data
  // For now, using mock data
  stats.value = {
    activeBookings: 2,
    savedProperties: 5,
    unreadMessages: 3,
    maintenanceRequests: 1
  }
}

onMounted(() => {
  loadDashboardData()
})
</script>