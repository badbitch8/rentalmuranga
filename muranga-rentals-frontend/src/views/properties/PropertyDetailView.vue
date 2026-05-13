<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Loading State -->
    <LoadingSpinner v-if="loading" size="lg" text="Loading property details..." fullScreen />

    <!-- Error State -->
    <div v-else-if="error" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <ErrorAlert type="error" :message="error" />
      <div class="mt-4">
        <router-link
          to="/properties"
          class="text-blue-600 hover:text-blue-700 font-medium"
        >
          ← Back to Properties
        </router-link>
      </div>
    </div>

    <!-- Property Details -->
    <div v-else-if="property" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Breadcrumb -->
      <nav class="mb-6">
        <ol class="flex items-center space-x-2 text-sm">
          <li>
            <router-link to="/" class="text-gray-500 hover:text-gray-700">Home</router-link>
          </li>
          <li><span class="text-gray-400">/</span></li>
          <li>
            <router-link to="/properties" class="text-gray-500 hover:text-gray-700">Properties</router-link>
          </li>
          <li><span class="text-gray-400">/</span></li>
          <li class="text-gray-900 font-medium">{{ property.title }}</li>
        </ol>
      </nav>

      <div class="lg:grid lg:grid-cols-3 lg:gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
          <!-- Image Gallery -->
          <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="relative">
              <img
                :src="currentImage"
                :alt="property.title"
                class="w-full h-96 object-cover"
              />
              
              <!-- Image Navigation -->
              <div v-if="property.images && property.images.length > 1" class="absolute bottom-4 left-1/2 transform -translate-x-1/2">
                <div class="flex space-x-2">
                  <button
                    v-for="(image, index) in property.images"
                    :key="index"
                    @click="currentImageIndex = index"
                    class="w-3 h-3 rounded-full transition-colors"
                    :class="currentImageIndex === index ? 'bg-white' : 'bg-white/50'"
                  ></button>
                </div>
              </div>

              <!-- Previous/Next Buttons -->
              <button
                v-if="property.images && property.images.length > 1"
                @click="previousImage"
                class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black/50 text-white p-2 rounded-full hover:bg-black/70"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
              </button>
              <button
                v-if="property.images && property.images.length > 1"
                @click="nextImage"
                class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black/50 text-white p-2 rounded-full hover:bg-black/70"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </button>
            </div>

            <!-- Thumbnail Gallery -->
            <div v-if="property.images && property.images.length > 1" class="p-4 grid grid-cols-5 gap-2">
              <button
                v-for="(image, index) in property.images.slice(0, 5)"
                :key="index"
                @click="currentImageIndex = index"
                class="relative aspect-square rounded overflow-hidden"
                :class="currentImageIndex === index ? 'ring-2 ring-blue-600' : ''"
              >
                <img :src="image.url" :alt="`Thumbnail ${index + 1}`" class="w-full h-full object-cover" />
              </button>
            </div>
          </div>

          <!-- Property Info -->
          <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-start justify-between mb-4">
              <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ property.title }}</h1>
                <p class="text-gray-600 flex items-center">
                  <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                  </svg>
                  {{ property.location }}
                </p>
              </div>
              <button
                @click="toggleFavorite"
                class="p-2 rounded-full hover:bg-gray-100 transition-colors"
              >
                <svg
                  class="w-6 h-6"
                  :class="isFavorite ? 'text-red-500 fill-current' : 'text-gray-400'"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
              </button>
            </div>

            <!-- Price & Type -->
            <div class="flex items-center justify-between py-4 border-t border-b border-gray-200">
              <div>
                <p class="text-3xl font-bold text-blue-600">
                  KES {{ property.price.toLocaleString() }}
                  <span class="text-lg text-gray-600">/month</span>
                </p>
                <p class="text-sm text-gray-500 mt-1">
                  Deposit: KES {{ property.deposit.toLocaleString() }}
                </p>
              </div>
              <span class="px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                {{ formatPropertyType(property.type) }}
              </span>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-4 gap-4 py-6">
              <div class="text-center">
                <div class="text-2xl font-bold text-gray-900">{{ property.bedrooms }}</div>
                <div class="text-sm text-gray-600">Bedrooms</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-gray-900">{{ property.bathrooms }}</div>
                <div class="text-sm text-gray-600">Bathrooms</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-gray-900">{{ property.square_meters }}</div>
                <div class="text-sm text-gray-600">m²</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-gray-900">{{ property.distance_from_mut }}</div>
                <div class="text-sm text-gray-600">km from MUT</div>
              </div>
            </div>

            <!-- Description -->
            <div class="py-6 border-t border-gray-200">
              <h2 class="text-xl font-semibold mb-3">Description</h2>
              <p class="text-gray-700 whitespace-pre-line">{{ property.description }}</p>
            </div>

            <!-- Amenities -->
            <div class="py-6 border-t border-gray-200">
              <h2 class="text-xl font-semibold mb-3">Amenities</h2>
              <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                <div v-for="amenity in property.amenities" :key="amenity" class="flex items-center">
                  <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                  </svg>
                  <span class="text-gray-700">{{ formatAmenity(amenity) }}</span>
                </div>
              </div>
            </div>

            <!-- Rules -->
            <div v-if="property.rules" class="py-6 border-t border-gray-200">
              <h2 class="text-xl font-semibold mb-3">House Rules</h2>
              <p class="text-gray-700 whitespace-pre-line">{{ property.rules }}</p>
            </div>
          </div>

          <!-- Reviews Section -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Reviews & Ratings</h2>
            <div class="text-center py-8 text-gray-500">
              <p>Reviews coming soon...</p>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 mt-6 lg:mt-0">
          <!-- Booking Card -->
          <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
            <h3 class="text-lg font-semibold mb-4">Book this property</h3>
            
            <!-- Availability Status -->
            <div class="mb-4">
              <span
                v-if="property.status === 'available'"
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800"
              >
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Available Now
              </span>
              <span
                v-else
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800"
              >
                Not Available
              </span>
            </div>

            <!-- Landlord Info -->
            <div class="mb-6 pb-6 border-b border-gray-200">
              <p class="text-sm text-gray-600 mb-2">Listed by</p>
              <div class="flex items-center">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                  <span class="text-blue-600 font-semibold">{{ property.landlord?.name?.charAt(0) }}</span>
                </div>
                <div class="ml-3">
                  <p class="font-medium text-gray-900">{{ property.landlord?.name }}</p>
                  <p class="text-sm text-gray-500">Verified Landlord</p>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
              <button
                v-if="authStore.isAuthenticated"
                @click="handleBooking"
                :disabled="property.status !== 'available'"
                class="w-full px-4 py-3 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ property.status === 'available' ? 'Book Now' : 'Not Available' }}
              </button>
              <router-link
                v-else
                to="/login"
                class="block w-full px-4 py-3 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition-colors text-center"
              >
                Login to Book
              </router-link>

              <button
                v-if="authStore.isAuthenticated"
                @click="handleMessage"
                class="w-full px-4 py-3 bg-white border-2 border-blue-600 text-blue-600 font-semibold rounded-md hover:bg-blue-50 transition-colors"
              >
                Message Landlord
              </button>

              <button
                @click="handleShare"
                class="w-full px-4 py-3 bg-gray-100 text-gray-700 font-semibold rounded-md hover:bg-gray-200 transition-colors"
              >
                Share Property
              </button>
            </div>

            <!-- Contact Info -->
            <div class="mt-6 pt-6 border-t border-gray-200">
              <p class="text-sm text-gray-600 mb-2">Need help?</p>
              <p class="text-sm text-gray-900">Call: +254 700 000 000</p>
              <p class="text-sm text-gray-900">Email: support@murangarentals.com</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { usePropertiesStore } from '@/stores/properties'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import ErrorAlert from '@/components/common/ErrorAlert.vue'
import api from '@/services/api'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const propertiesStore = usePropertiesStore()

const property = ref(null)
const loading = ref(false)
const error = ref('')
const currentImageIndex = ref(0)
const isFavorite = ref(false)

const currentImage = computed(() => {
  if (property.value?.images && property.value.images.length > 0) {
    return property.value.images[currentImageIndex.value]?.url
  }
  return '/placeholder-property.jpg'
})

const loadProperty = async () => {
  loading.value = true
  error.value = ''
  
  try {
    // Use store for consistency
    await propertiesStore.fetchPropertyById(route.params.id)
    property.value = propertiesStore.currentProperty

    // Check if property is in favorites
    if (authStore.isAuthenticated) {
      await checkFavoriteStatus()
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load property details'
  } finally {
    loading.value = false
  }
}


const checkFavoriteStatus = async () => {
  try {
    await propertiesStore.fetchFavorites()
    isFavorite.value = propertiesStore.favorites.includes(property.value.id)
  } catch (err) {
    console.error('Failed to check favorite status:', err)
  }
}


const toggleFavorite = async () => {
  if (!authStore.isAuthenticated) {
    router.push('/login')
    return
  }

  try {
    await propertiesStore.toggleFavorite(property.value.id)
    // local isFavorite follows store favorites after toggle
    isFavorite.value = propertiesStore.favorites.includes(property.value.id)
  } catch (err) {
    console.error('Failed to toggle favorite:', err)
  }
}


const previousImage = () => {
  if (currentImageIndex.value > 0) {
    currentImageIndex.value--
  } else {
    currentImageIndex.value = property.value.images.length - 1
  }
}

const nextImage = () => {
  if (currentImageIndex.value < property.value.images.length - 1) {
    currentImageIndex.value++
  } else {
    currentImageIndex.value = 0
  }
}

const formatPropertyType = (type) => {
  const types = {
    bedsitter: 'Bedsitter',
    single_room: 'Single Room',
    one_bedroom: '1 Bedroom',
    two_bedroom: '2 Bedroom',
    studio: 'Studio'
  }
  return types[type] || type
}

const formatAmenity = (amenity) => {
  return amenity.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const handleBooking = () => {
  router.push(`/bookings/create?property=${property.value.id}`)
}

const handleMessage = () => {
  router.push(`/messages?landlord=${property.value.landlord_id}`)
}

const handleShare = () => {
  if (navigator.share) {
    navigator.share({
      title: property.value.title,
      text: property.value.description,
      url: window.location.href
    })
  } else {
    // Fallback: copy to clipboard
    navigator.clipboard.writeText(window.location.href)
    alert('Link copied to clipboard!')
  }
}

onMounted(() => {
  loadProperty()
})
</script>