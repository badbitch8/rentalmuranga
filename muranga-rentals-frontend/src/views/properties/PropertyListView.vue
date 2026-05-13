<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <h1 class="text-3xl font-bold text-gray-900">Browse Properties</h1>
        <p class="mt-2 text-gray-600">Find your perfect home in Murang'a County</p>
      </div>
    </div>

    <div class="container-wide py-8">
      <div class="lg:grid lg:grid-cols-4 lg:gap-8">
        <!-- Filters Sidebar -->
        <aside class="hidden lg:block lg:col-span-1">
          <div class="card sticky top-4 p-6">
            <h2 class="text-lg font-semibold mb-4">Filters</h2>
            
            <!-- Property Type -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Property Type
              </label>
              <select
                v-model="filters.type"
                @change="applyFilters"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">All Types</option>
                <option value="bedsitter">Bedsitter</option>
                <option value="single_room">Single Room</option>
                <option value="one_bedroom">1 Bedroom</option>
                <option value="two_bedroom">2 Bedroom</option>
                <option value="studio">Studio</option>
              </select>
            </div>

            <!-- Price Range -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Price Range (KES)
              </label>
              <div class="space-y-2">
                <input
                  v-model.number="filters.min_price"
                  type="number"
                  placeholder="Min"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <input
                  v-model.number="filters.max_price"
                  type="number"
                  placeholder="Max"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>

            <!-- Bedrooms -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Bedrooms
              </label>
              <select
                v-model="filters.bedrooms"
                @change="applyFilters"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Any</option>
                <option value="0">Studio</option>
                <option value="1">1+</option>
                <option value="2">2+</option>
                <option value="3">3+</option>
              </select>
            </div>

            <!-- Bathrooms -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Bathrooms
              </label>
              <select
                v-model="filters.bathrooms"
                @change="applyFilters"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Any</option>
                <option value="1">1+</option>
                <option value="2">2+</option>
              </select>
            </div>

            <!-- Amenities -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Amenities
              </label>
              <div class="space-y-2">
                <label class="flex items-center">
                  <input
                    v-model="filters.wifi"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <span class="ml-2 text-sm text-gray-700">WiFi</span>
                </label>
                <label class="flex items-center">
                  <input
                    v-model="filters.parking"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <span class="ml-2 text-sm text-gray-700">Parking</span>
                </label>
                <label class="flex items-center">
                  <input
                    v-model="filters.water"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <span class="ml-2 text-sm text-gray-700">Water 24/7</span>
                </label>
                <label class="flex items-center">
                  <input
                    v-model="filters.security"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <span class="ml-2 text-sm text-gray-700">Security</span>
                </label>
              </div>
            </div>

            <!-- Apply Filters Button -->
            <button
              @click="applyFilters"
              class="w-full px-4 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition-colors"
            >
              Apply Filters
            </button>

            <!-- Clear Filters -->
            <button
              @click="clearFilters"
              class="w-full mt-2 px-4 py-2 bg-gray-200 text-gray-700 font-semibold rounded-md hover:bg-gray-300 transition-colors"
            >
              Clear All
            </button>
          </div>
        </aside>

        <!-- Main Content -->
        <main class="lg:col-span-3">
          <!-- Search Bar -->
          <div class="card p-4 mb-6">
            <div class="flex gap-4">
              <input
                v-model="searchQuery"
                @keyup.enter="handleSearch"
                type="text"
                placeholder="Search by location, property name..."
                class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <button
                @click="handleSearch"
                class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition-colors"
              >
                Search
              </button>
            </div>
          </div>

          <!-- Results Header -->
          <div class="flex items-center justify-between mb-6">
            <div>
              <p class="text-gray-600">
                <span class="font-semibold">{{ propertiesStore.total }}</span> properties found
              </p>
            </div>
            <div class="flex items-center gap-4">
              <label class="text-sm text-gray-600">Sort by:</label>
              <select
                v-model="sortBy"
                @change="applyFilters"
                class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="created_at">Newest</option>
                <option value="price_asc">Price: Low to High</option>
                <option value="price_desc">Price: High to Low</option>
                <option value="distance">Distance from MUT</option>
              </select>
            </div>
          </div>

          <!-- Loading State -->
          <LoadingSpinner v-if="loading" size="lg" text="Loading properties..." />

          <!-- Error State -->
          <ErrorAlert
            v-else-if="error"
            type="error"
            :message="error"
            @close="error = ''"
          />

          <!-- Empty State -->
          <div v-else-if="properties.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">No properties found</h3>
            <p class="mt-1 text-gray-500">Try adjusting your filters or search criteria</p>
            <button
              @click="clearFilters"
              class="mt-4 px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition-colors"
            >
              Clear Filters
            </button>
          </div>

          <!-- Properties Grid -->
          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <PropertyCard
              v-for="property in properties"
              :key="property.id"
              :property="property"
            />
          </div>

          <!-- Pagination -->
          <div v-if="propertiesStore.lastPage > 1" class="mt-8 flex justify-center">
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
              <!-- Previous Button -->
              <button
                @click="changePage(propertiesStore.currentPage - 1)"
                :disabled="propertiesStore.currentPage === 1"
                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span class="sr-only">Previous</span>
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
              </button>

              <!-- Page Numbers -->
              <button
                v-for="page in visiblePages"
                :key="page"
                @click="changePage(page)"
                :class="[
                  'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                  page === propertiesStore.currentPage
                    ? 'z-10 bg-blue-600 border-blue-600 text-white'
                    : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'
                ]"
              >
                {{ page }}
              </button>

              <!-- Next Button -->
              <button
                @click="changePage(propertiesStore.currentPage + 1)"
                :disabled="propertiesStore.currentPage === propertiesStore.lastPage"
                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span class="sr-only">Next</span>
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                </svg>
              </button>
            </nav>
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usePropertiesStore } from '@/stores/properties'
import PropertyCard from '@/components/properties/PropertyCard.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import ErrorAlert from '@/components/common/ErrorAlert.vue'

const route = useRoute()
const router = useRouter()
const propertiesStore = usePropertiesStore()

const searchQuery = ref(route.query.search || '')
const sortBy = ref('created_at')
const loading = ref(false)
const error = ref('')

const filters = reactive({
  type: route.query.type || '',
  min_price: route.query.min_price || '',
  max_price: route.query.max_price || '',
  bedrooms: route.query.bedrooms || '',
  bathrooms: route.query.bathrooms || '',
  wifi: false,
  parking: false,
  water: false,
  security: false
})

const properties = computed(() => propertiesStore.properties)

const visiblePages = computed(() => {
  const current = propertiesStore.currentPage
  const last = propertiesStore.lastPage
  const delta = 2
  const range = []
  
  for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
    range.push(i)
  }
  
  if (current - delta > 2) {
    range.unshift('...')
  }
  if (current + delta < last - 1) {
    range.push('...')
  }
  
  range.unshift(1)
  if (last > 1) {
    range.push(last)
  }
  
  return range.filter(p => p !== '...' || range.indexOf(p) === range.lastIndexOf(p))
})

const loadProperties = async () => {
  loading.value = true
  error.value = ''

  try {
    // Map view filters -> store filters contract
    const amenities = []
    if (filters.wifi) amenities.push('wifi')
    if (filters.parking) amenities.push('parking')
    if (filters.water) amenities.push('water')
    if (filters.security) amenities.push('security')

    propertiesStore.setFilters({
      type: filters.type || null,
      min_price: filters.min_price || null,
      max_price: filters.max_price || null,
      bedrooms: filters.bedrooms || null,
      location: searchQuery.value || null,
      // Store expects amenities as array
      amenities
    })

    // Store fetchProperties signature: fetchProperties(page)
    // Sorting isn't part of store.filters currently; pass via query params in future.
    // For now: keep UI sort selection, but only apply when backend supports it through other filters.

    await propertiesStore.fetchProperties(propertiesStore.currentPage)
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load properties'
  } finally {
    loading.value = false
  }
}


const handleSearch = () => {
  propertiesStore.currentPage = 1
  applyFilters()
}

const applyFilters = () => {
  const query = {
    search: searchQuery.value || undefined,
    type: filters.type || undefined,
    min_price: filters.min_price || undefined,
    max_price: filters.max_price || undefined,
    bedrooms: filters.bedrooms || undefined,
    bathrooms: filters.bathrooms || undefined,
    sort_by: sortBy.value !== 'created_at' ? sortBy.value : undefined
  }

  router.push({ query })
  propertiesStore.currentPage = 1
  loadProperties()
}


const clearFilters = () => {
  searchQuery.value = ''
  filters.type = ''
  filters.min_price = ''
  filters.max_price = ''
  filters.bedrooms = ''
  filters.bathrooms = ''
  filters.wifi = false
  filters.parking = false
  filters.water = false
  filters.security = false
  sortBy.value = 'created_at'
  propertiesStore.currentPage = 1
  
  router.push({ query: {} })
  loadProperties()
}

const changePage = (page) => {
  if (page < 1 || page > propertiesStore.lastPage) return
  propertiesStore.currentPage = page
  loadProperties()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Watch for route changes
watch(() => route.query, () => {
  if (route.name === 'properties') {
    loadProperties()
  }
})

onMounted(() => {
  loadProperties()
})
</script>