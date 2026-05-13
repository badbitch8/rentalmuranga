<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container-wide py-6 md:py-8">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">My Bookings</h1>
        <p class="text-sm md:text-base text-gray-600">
          View and manage your property bookings
        </p>
      </div>

      <!-- Tabs -->
      <div class="mb-6 border-b border-gray-200">
        <nav class="flex space-x-8" aria-label="Tabs">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              activeTab === tab.id
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors'
            ]"
          >
            {{ tab.name }}
            <span
              v-if="tab.count > 0"
              :class="[
                activeTab === tab.id ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600',
                'ml-2 py-0.5 px-2 rounded-full text-xs font-medium'
              ]"
            >
              {{ tab.count }}
            </span>
          </button>
        </nav>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-12">
        <LoadingSpinner size="lg" text="Loading bookings..." />
      </div>

      <!-- Error State -->
      <ErrorAlert v-else-if="error" :message="error" />

      <!-- Bookings List -->
      <div v-else-if="filteredBookings.length > 0" class="space-y-4">
        <div
          v-for="booking in filteredBookings"
          :key="booking.id"
          class="card-hover cursor-pointer"
          @click="viewBookingDetails(booking.id)"
        >
          <div class="flex flex-col md:flex-row gap-4">
            <!-- Property Image -->
            <div class="md:w-48 flex-shrink-0">
              <img
                :src="booking.property?.images?.[0]?.url || '/placeholder-property.jpg'"
                :alt="booking.property?.title"
                class="w-full h-48 md:h-full object-cover rounded-lg"
              />
            </div>

            <!-- Booking Details -->
            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between mb-3">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 mb-1">
                    {{ booking.property?.title }}
                  </h3>
                  <p class="text-sm text-gray-600 flex items-center gap-1">
                    <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    {{ booking.property?.location }}
                  </p>
                </div>
                <span :class="getStatusBadgeClass(booking.status)" class="badge">
                  {{ formatStatus(booking.status) }}
                </span>
              </div>

              <!-- Booking Info Grid -->
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-3">
                <div>
                  <p class="text-xs text-gray-500 mb-1">Booking ID</p>
                  <p class="text-sm font-medium text-gray-900">#{{ booking.id }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500 mb-1">Move-in Date</p>
                  <p class="text-sm font-medium text-gray-900">{{ formatDate(booking.move_in_date) }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500 mb-1">Duration</p>
                  <p class="text-sm font-medium text-gray-900">{{ booking.duration_months }} months</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500 mb-1">Total Amount</p>
                  <p class="text-sm font-semibold text-blue-600">KES {{ formatNumber(booking.total_amount) }}</p>
                </div>
              </div>

              <!-- Landlord Info -->
              <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                  <span class="text-xs font-semibold text-gray-600">
                    {{ booking.property?.landlord?.name?.charAt(0) }}
                  </span>
                </div>
                <div>
                  <p class="text-xs text-gray-500">Landlord</p>
                  <p class="text-sm font-medium text-gray-900">{{ booking.property?.landlord?.name }}</p>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex flex-wrap gap-2">
                <button
                  v-if="booking.status === 'pending'"
                  @click.stop="cancelBooking(booking.id)"
                  class="btn-outline text-sm"
                >
                  Cancel Booking
                </button>
                <button
                  v-if="booking.status === 'confirmed' && !booking.payment_status"
                  @click.stop="makePayment(booking.id)"
                  class="btn-primary text-sm"
                >
                  Make Payment
                </button>
                <button
                  @click.stop="contactLandlord(booking.property?.landlord?.id)"
                  class="btn-outline text-sm flex items-center gap-1"
                >
                  <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                  </svg>
                  Contact Landlord
                </button>
                <button
                  @click.stop="viewBookingDetails(booking.id)"
                  class="btn-outline text-sm"
                >
                  View Details
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-16">
        <svg class="icon-xl mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No {{ activeTab }} bookings</h3>
        <p class="text-sm text-gray-600 mb-6">
          {{ getEmptyStateMessage() }}
        </p>
        <router-link to="/properties" class="btn-primary inline-block">
          Browse Properties
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import ErrorAlert from '@/components/common/ErrorAlert.vue'

const router = useRouter()

const activeTab = ref('all')
const loading = ref(false)
const error = ref(null)
const bookings = ref([])

const tabs = computed(() => [
  { id: 'all', name: 'All Bookings', count: bookings.value.length },
  { id: 'pending', name: 'Pending', count: bookings.value.filter(b => b.status === 'pending').length },
  { id: 'confirmed', name: 'Confirmed', count: bookings.value.filter(b => b.status === 'confirmed').length },
  { id: 'active', name: 'Active', count: bookings.value.filter(b => b.status === 'active').length },
  { id: 'completed', name: 'Completed', count: bookings.value.filter(b => b.status === 'completed').length }
])

const filteredBookings = computed(() => {
  if (activeTab.value === 'all') return bookings.value
  return bookings.value.filter(booking => booking.status === activeTab.value)
})

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

const formatStatus = (status) => {
  return status.charAt(0).toUpperCase() + status.slice(1)
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

const getEmptyStateMessage = () => {
  const messages = {
    all: 'You haven\'t made any bookings yet. Start by browsing available properties.',
    pending: 'You don\'t have any pending bookings at the moment.',
    confirmed: 'No confirmed bookings. Your confirmed bookings will appear here.',
    active: 'You don\'t have any active rentals currently.',
    completed: 'No completed bookings in your history.'
  }
  return messages[activeTab.value] || messages.all
}

const viewBookingDetails = (id) => {
  router.push(`/bookings/${id}`)
}

const cancelBooking = async (id) => {
  if (!confirm('Are you sure you want to cancel this booking?')) return
  
  try {
    // TODO: Implement API call to cancel booking
    console.log('Cancelling booking:', id)
    // await api.delete(`/bookings/${id}`)
    // Reload bookings
    await loadBookings()
  } catch (err) {
    error.value = 'Failed to cancel booking. Please try again.'
  }
}

const makePayment = (id) => {
  router.push(`/bookings/${id}/payment`)
}

const contactLandlord = (landlordId) => {
  router.push(`/messages?user=${landlordId}`)
}

const loadBookings = async () => {
  loading.value = true
  error.value = null
  
  try {
    // TODO: Implement API call to fetch bookings
    // const response = await api.get('/bookings')
    // bookings.value = response.data.data
    
    // Mock data for now
    bookings.value = [
      {
        id: 1,
        status: 'confirmed',
        move_in_date: '2024-02-01',
        duration_months: 12,
        total_amount: 45000,
        payment_status: false,
        property: {
          id: 1,
          title: 'Modern 1BR Apartment near MUT',
          location: 'Murang\'a Town',
          images: [{ url: '/placeholder-property.jpg' }],
          landlord: {
            id: 1,
            name: 'John Kamau'
          }
        }
      },
      {
        id: 2,
        status: 'pending',
        move_in_date: '2024-03-01',
        duration_months: 6,
        total_amount: 30000,
        payment_status: false,
        property: {
          id: 2,
          title: 'Cozy Bedsitter',
          location: 'Near Campus',
          images: [{ url: '/placeholder-property.jpg' }],
          landlord: {
            id: 2,
            name: 'Mary Wanjiku'
          }
        }
      }
    ]
  } catch (err) {
    error.value = 'Failed to load bookings. Please try again.'
    console.error('Error loading bookings:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadBookings()
})
</script>