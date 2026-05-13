<template>
  <div class="min-h-screen bg-slate-950 text-slate-100">
    <div class="container-wide py-6 md:py-8">
      <!-- Welcome Section -->
      <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">
          Landlord Dashboard
        </h1>
        <p class="text-sm md:text-base text-slate-400">
          Manage your properties, bookings, and tenant communications
        </p>
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-slate-400 mb-1">Total Properties</p>
              <p class="text-2xl font-bold text-white">{{ stats.totalProperties }}</p>
            </div>
            <div class="w-10 h-10 bg-slate-900/70 rounded-lg flex items-center justify-center">
              <svg class="icon-md text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-slate-400 mb-1">Active Tenants</p>
              <p class="text-2xl font-bold text-white">{{ stats.activeTenants }}</p>
            </div>
            <div class="w-10 h-10 bg-slate-900/70 rounded-lg flex items-center justify-center">
              <svg class="icon-md text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-slate-400 mb-1">Monthly Revenue</p>
              <p class="text-2xl font-bold text-white">{{ formatCurrency(stats.monthlyRevenue) }}</p>
            </div>
            <div class="w-10 h-10 bg-slate-900/70 rounded-lg flex items-center justify-center">
              <svg class="icon-md text-violet-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-slate-400 mb-1">Pending Requests</p>
              <p class="text-2xl font-bold text-white">{{ stats.pendingRequests }}</p>
            </div>
            <div class="w-10 h-10 bg-slate-900/70 rounded-lg flex items-center justify-center">
              <svg class="icon-md text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Properties & Bookings -->
        <div class="lg:col-span-2 space-y-6">
          <!-- My Properties -->
          <div class="card">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-semibold text-white">My Properties</h2>
              <router-link to="/landlord/properties/create" class="btn-primary text-sm">
                + Add Property
              </router-link>
            </div>

            <LoadingSpinner v-if="loadingProperties" size="sm" />

            <div v-else-if="properties.length > 0" class="space-y-3">
              <div
                v-for="property in properties"
                :key="property.id"
                class="border border-slate-800 rounded-2xl p-4 hover:border-slate-700 transition-colors bg-slate-950/70"
              >
                <div class="flex items-start gap-4">
                  <img
                    :src="property.images?.[0]?.url || '/placeholder-property.jpg'"
                    :alt="property.title"
                    class="w-20 h-20 object-cover rounded-2xl flex-shrink-0"
                  />
                  <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between mb-2">
                      <div>
                        <h3 class="font-semibold text-white mb-1">{{ property.title }}</h3>
                        <p class="text-sm text-slate-400">{{ property.location }}</p>
                      </div>
                      <span :class="property.is_available ? 'badge-success' : 'badge-error'" class="badge">
                        {{ property.is_available ? 'Available' : 'Occupied' }}
                      </span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                      <span class="font-semibold text-gold">KES {{ formatNumber(property.rent_amount) }}/mo</span>
                      <div class="flex gap-2">
                        <button @click="editProperty(property.id)" class="text-gold hover:text-amber-300">
                          <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                          </svg>
                        </button>
                        <button @click="viewProperty(property.id)" class="text-slate-300 hover:text-white">
                          <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                          </svg>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="text-center py-8 text-slate-400">
              <svg class="icon-xl mx-auto mb-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
              </svg>
              <p class="text-sm mb-3">No properties listed yet</p>
              <router-link to="/landlord/properties/create" class="btn-primary inline-block">
                Add Your First Property
              </router-link>
            </div>
          </div>

          <!-- Recent Bookings -->
          <div class="card">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-semibold text-white">Recent Bookings</h2>
              <router-link to="/landlord/bookings" class="link text-sm">View All</router-link>
            </div>

            <LoadingSpinner v-if="loadingBookings" size="sm" />

            <div v-else-if="recentBookings.length > 0" class="space-y-3">
              <div
                v-for="booking in recentBookings"
                :key="booking.id"
                class="border border-slate-800 rounded-2xl p-4 bg-slate-950/70"
              >
                <div class="flex items-start justify-between mb-2">
                  <div>
                    <h3 class="font-semibold text-white mb-1">{{ booking.tenant?.name }}</h3>
                    <p class="text-sm text-slate-400">{{ booking.property?.title }}</p>
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
              <p class="text-sm">No bookings yet</p>
            </div>
          </div>
        </div>

        <!-- Right Column - Actions & Notifications -->
        <div class="space-y-6">
          <!-- Quick Actions -->
          <div class="card">
            <h2 class="text-lg font-semibold text-white mb-4">Quick Actions</h2>
            <div class="space-y-2">
              <router-link to="/landlord/properties/create" class="btn-primary w-full flex items-center justify-center gap-2">
                <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Property
              </router-link>
              <router-link to="/landlord/bookings" class="btn-outline w-full flex items-center justify-center gap-2">
                <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Manage Bookings
              </router-link>
              <router-link to="/messages" class="btn-outline w-full flex items-center justify-center gap-2">
                <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                Messages
              </router-link>
            </div>
          </div>

          <!-- Maintenance Requests -->
          <div class="card">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-semibold text-white">Maintenance Requests</h2>
              <span class="badge badge-warning">{{ maintenanceRequests.length }}</span>
            </div>

            <div v-if="maintenanceRequests.length > 0" class="space-y-3">
              <div
                v-for="request in maintenanceRequests"
                :key="request.id"
                class="border border-slate-800 rounded-2xl p-3 bg-slate-950/70"
              >
                <div class="flex items-start justify-between mb-2">
                  <h3 class="text-sm font-semibold text-white">{{ request.title }}</h3>
                  <span :class="getPriorityBadgeClass(request.priority)" class="badge text-xs">
                    {{ request.priority }}
                  </span>
                </div>
                <p class="text-xs text-slate-400 mb-2">{{ request.property?.title }}</p>
                <p class="text-xs text-slate-400">{{ formatTimeAgo(request.created_at) }}</p>
              </div>
            </div>

            <div v-else class="text-center py-6 text-slate-400">
              <p class="text-sm">No pending requests</p>
            </div>
          </div>

          <!-- Revenue Chart Placeholder -->
          <div class="card">
            <h2 class="text-lg font-semibold text-white mb-4">Revenue Overview</h2>
            <div class="space-y-3">
              <div class="flex items-center justify-between text-sm">
                <span class="text-slate-400">This Month</span>
                <span class="font-semibold text-white">KES {{ formatNumber(stats.monthlyRevenue) }}</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-slate-400">Last Month</span>
                <span class="font-semibold text-white">KES {{ formatNumber(stats.lastMonthRevenue) }}</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-slate-400">Growth</span>
                <span class="font-semibold text-emerald-300">+{{ stats.revenueGrowth }}%</span>
              </div>
            </div>
          </div>

          <!-- Tips -->
          <div class="card bg-slate-900/75 border-slate-800">
            <div class="flex items-start gap-3">
              <div class="w-8 h-8 bg-slate-900/70 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="icon-sm text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
              </div>
              <div>
                <h3 class="text-sm font-semibold text-white mb-1">Landlord Tip</h3>
                <p class="text-xs text-slate-400">
                  Keep your property listings updated with high-quality photos and accurate descriptions to attract more tenants.
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
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'

const router = useRouter()

const stats = ref({
  totalProperties: 0,
  activeTenants: 0,
  monthlyRevenue: 0,
  lastMonthRevenue: 0,
  revenueGrowth: 0,
  pendingRequests: 0
})

const properties = ref([])
const recentBookings = ref([])
const maintenanceRequests = ref([])
const loadingProperties = ref(false)
const loadingBookings = ref(false)

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US').format(num)
}

const formatCurrency = (num) => {
  return `${(num / 1000).toFixed(0)}K`
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  })
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

const getPriorityBadgeClass = (priority) => {
  const classes = {
    low: 'badge',
    medium: 'badge-warning',
    high: 'badge-error'
  }
  return classes[priority] || 'badge'
}

const editProperty = (id) => {
  router.push(`/landlord/properties/${id}/edit`)
}

const viewProperty = (id) => {
  router.push(`/properties/${id}`)
}

const loadDashboardData = async () => {
  // TODO: Implement API calls to fetch dashboard data
  // For now, using mock data
  stats.value = {
    totalProperties: 5,
    activeTenants: 12,
    monthlyRevenue: 450000,
    lastMonthRevenue: 420000,
    revenueGrowth: 7.1,
    pendingRequests: 3
  }
}

onMounted(() => {
  loadDashboardData()
})
</script>