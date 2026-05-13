<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container-wide py-6 md:py-8">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Saved Properties</h1>
        <p class="text-sm md:text-base text-gray-600">
          Properties you've saved for later
        </p>
      </div>

      <!-- Filters -->
      <div class="card mb-6">
        <div class="flex flex-col md:flex-row gap-4">
          <div class="flex-1">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search saved properties..."
              class="input-field"
            />
          </div>
          <select v-model="sortBy" class="input-field md:w-48">
            <option value="recent">Recently Added</option>
            <option value="price_low">Price: Low to High</option>
            <option value="price_high">Price: High to Low</option>
            <option value="name">Name: A to Z</option>
          </select>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-12">
        <LoadingSpinner size="lg" text="Loading favorites..." />
      </div>

      <!-- Error State -->
      <ErrorAlert v-else-if="error" :message="error" />

      <!-- Favorites Grid -->
      <div v-else-if="filteredFavorites.length > 0">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
          <div
            v-for="property in filteredFavorites"
            :key="property.id"
            class="card-hover p-0 overflow-hidden"
          >
            <!-- Property Image -->
            <div class="relative">
              <img
                :src="property.images?.[0]?.url || '/placeholder-property.jpg'"
                :alt="property.title"
                class="w-full h-48 object-cover cursor-pointer"
                @click="viewProperty(property.id)"
              />
              <button
                @click="removeFavorite(property.id)"
                class="absolute top-3 right-3 w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-md hover:bg-gray-100 transition-colors"
                title="Remove from favorites"
              >
                <svg class="icon-md text-red-600" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>
              </button>
              <div class="absolute bottom-3 left-3">
                <span v-if="property.is_available" class="badge badge-success">Available</span>
                <span v-else class="badge badge-error">Occupied</span>
              </div>
            </div>

            <!-- Property Details -->
            <div class="p-4">
              <h3
                class="text-lg font-semibold text-gray-900 mb-2 cursor-pointer hover:text-blue-600 transition-colors line-clamp-1"
                @click="viewProperty(property.id)"
              >
                {{ property.title }}
              </h3>
              
              <p class="text-sm text-gray-600 mb-3 flex items-center gap-1">
                <svg class="icon-sm flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="truncate">{{ property.location }}</span>
              </p>

              <!-- Property Features -->
              <div class="flex items-center gap-3 mb-3 text-xs text-gray-600">
                <span class="flex items-center gap-1">
                  <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                  </svg>
                  {{ property.bedrooms }} bed
                </span>
                <span class="flex items-center gap-1">
                  <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                  </svg>
                  {{ property.bathrooms }} bath
                </span>
              </div>

              <!-- Price and Actions -->
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xl font-bold text-blue-600">
                    KES {{ formatNumber(property.rent_amount) }}
                  </p>
                  <p class="text-xs text-gray-500">per month</p>
                </div>
                <button
                  @click="viewProperty(property.id)"
                  class="btn-primary text-sm"
                >
                  View Details
                </button>
              </div>

              <!-- Saved Date -->
              <p class="text-xs text-gray-500 mt-3 pt-3 border-t border-gray-200">
                Saved {{ formatTimeAgo(property.saved_at) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="flex justify-center mt-8">
          <nav class="flex items-center gap-2">
            <button
              @click="currentPage--"
              :disabled="currentPage === 1"
              class="btn-outline text-sm"
              :class="{ 'opacity-50 cursor-not-allowed': currentPage === 1 }"
            >
              Previous
            </button>
            <span class="text-sm text-gray-600 px-4">
              Page {{ currentPage }} of {{ totalPages }}
            </span>
            <button
              @click="currentPage++"
              :disabled="currentPage === totalPages"
              class="btn-outline text-sm"
              :class="{ 'opacity-50 cursor-not-allowed': currentPage === totalPages }"
            >
              Next
            </button>
          </nav>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-16">
        <svg class="icon-xl mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
        </svg>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No saved properties</h3>
        <p class="text-sm text-gray-600 mb-6">
          Start saving properties you're interested in to view them here
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

const loading = ref(false)
const error = ref(null)
const favorites = ref([])
const searchQuery = ref('')
const sortBy = ref('recent')
const currentPage = ref(1)
const itemsPerPage = 9

const filteredFavorites = computed(() => {
  let filtered = [...favorites.value]
  
  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(property =>
      property.title?.toLowerCase().includes(query) ||
      property.location?.toLowerCase().includes(query)
    )
  }
  
  // Sort
  switch (sortBy.value) {
    case 'price_low':
      filtered.sort((a, b) => a.rent_amount - b.rent_amount)
      break
    case 'price_high':
      filtered.sort((a, b) => b.rent_amount - a.rent_amount)
      break
    case 'name':
      filtered.sort((a, b) => a.title.localeCompare(b.title))
      break
    case 'recent':
    default:
      filtered.sort((a, b) => new Date(b.saved_at) - new Date(a.saved_at))
  }
  
  // Pagination
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return filtered.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(favorites.value.length / itemsPerPage)
})

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US').format(num)
}

const formatTimeAgo = (date) => {
  const seconds = Math.floor((new Date() - new Date(date)) / 1000)
  if (seconds < 60) return 'just now'
  if (seconds < 3600) return `${Math.floor(seconds / 60)} minutes ago`
  if (seconds < 86400) return `${Math.floor(seconds / 3600)} hours ago`
  if (seconds < 604800) return `${Math.floor(seconds / 86400)} days ago`
  return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const viewProperty = (id) => {
  router.push(`/properties/${id}`)
}

const removeFavorite = async (id) => {
  if (!confirm('Remove this property from your favorites?')) return
  
  try {
    // TODO: Implement API call
    // await api.delete(`/favorites/${id}`)
    favorites.value = favorites.value.filter(p => p.id !== id)
    console.log('Removed favorite:', id)
  } catch (err) {
    error.value = 'Failed to remove favorite. Please try again.'
    console.error('Error removing favorite:', err)
  }
}

const loadFavorites = async () => {
  loading.value = true
  error.value = null
  
  try {
    // TODO: Implement API call
    // const response = await api.get('/favorites')
    // favorites.value = response.data.data
    
    // Mock data
    favorites.value = [
      {
        id: 1,
        title: 'Modern 1BR Apartment near MUT',
        location: 'Murang\'a Town, 500m from campus',
        rent_amount: 15000,
        bedrooms: 1,
        bathrooms: 1,
        is_available: true,
        images: [{ url: '/placeholder-property.jpg' }],
        saved_at: new Date(Date.now() - 86400000).toISOString()
      },
      {
        id: 2,
        title: 'Cozy Bedsitter',
        location: 'Near Campus Gate',
        rent_amount: 8000,
        bedrooms: 0,
        bathrooms: 1,
        is_available: true,
        images: [{ url: '/placeholder-property.jpg' }],
        saved_at: new Date(Date.now() - 172800000).toISOString()
      },
      {
        id: 3,
        title: 'Spacious 2BR House',
        location: 'Murang\'a Town Center',
        rent_amount: 25000,
        bedrooms: 2,
        bathrooms: 2,
        is_available: false,
        images: [{ url: '/placeholder-property.jpg' }],
        saved_at: new Date(Date.now() - 259200000).toISOString()
      }
    ]
  } catch (err) {
    error.value = 'Failed to load favorites. Please try again.'
    console.error('Error loading favorites:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadFavorites()
})
</script>