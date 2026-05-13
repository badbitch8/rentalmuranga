<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-r from-blue-600 to-blue-800 text-white">
      <div class="container-wide py-16 md:py-20">
        <div class="text-center">
          <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-4 md:mb-6">
            Find Your Perfect Home in Murang'a
          </h1>
          <p class="text-lg md:text-xl mb-6 md:mb-8 text-blue-100">
            Trusted rental marketplace for MUT students and local professionals
          </p>
          
          <!-- Search Bar -->
          <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-lg p-3 md:p-4">
            <div class="flex flex-col md:flex-row gap-3">
              <div class="flex-1">
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search by location, property type..."
                  class="input-field"
                />
              </div>
              <div class="md:w-48">
                <select
                  v-model="searchType"
                  class="input-field"
                >
                  <option value="">All Property Types</option>
                  <option value="bedsitter">Bedsitter</option>
                  <option value="single_room">Single Room</option>
                  <option value="one_bedroom">1 Bedroom</option>
                  <option value="two_bedroom">2 Bedroom</option>
                  <option value="studio">Studio</option>
                </select>
              </div>
              <button
                @click="handleSearch"
                class="btn-primary whitespace-nowrap"
              >
                Search
              </button>
            </div>
          </div>

          <!-- Quick Stats -->
          <div class="mt-8 md:mt-12 grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
            <div class="text-center">
              <div class="text-2xl md:text-3xl font-bold">500+</div>
              <div class="text-sm text-blue-100">Properties</div>
            </div>
            <div class="text-center">
              <div class="text-2xl md:text-3xl font-bold">2,000+</div>
              <div class="text-sm text-blue-100">Happy Tenants</div>
            </div>
            <div class="text-center">
              <div class="text-2xl md:text-3xl font-bold">150+</div>
              <div class="text-sm text-blue-100">Verified Landlords</div>
            </div>
            <div class="text-center">
              <div class="text-2xl md:text-3xl font-bold">98%</div>
              <div class="text-sm text-blue-100">Satisfaction Rate</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Featured Properties -->
    <section class="py-12 md:py-16 bg-gray-50">
      <div class="container-wide">
        <div class="text-center mb-8 md:mb-12">
          <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Featured Properties</h2>
          <p class="text-sm md:text-base text-gray-600">Handpicked properties near Murang'a University</p>
        </div>

        <LoadingSpinner v-if="loadingProperties" size="lg" text="Loading properties..." />

        <div v-else-if="featuredProperties.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
          <PropertyCard
            v-for="property in featuredProperties"
            :key="property.id"
            :property="property"
          />
        </div>

        <div v-else class="text-center py-12">
          <p class="text-gray-500">No properties available at the moment.</p>
        </div>

        <div class="text-center mt-6 md:mt-8">
          <router-link
            to="/properties"
            class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors"
          >
            View All Properties
          </router-link>
        </div>
      </div>
    </section>

    <!-- How It Works -->
    <section class="py-12 md:py-16 bg-white">
      <div class="container-wide">
        <div class="text-center mb-8 md:mb-12">
          <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">How It Works</h2>
          <p class="text-sm md:text-base text-gray-600">Find your perfect home in 3 simple steps</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
          <!-- Step 1 -->
          <div class="text-center">
            <div class="inline-flex items-center justify-center w-10 h-10 bg-blue-100 text-blue-600 rounded-full mb-3">
              <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
            </div>
            <h3 class="text-lg font-semibold mb-2">1. Search</h3>
            <p class="text-sm text-gray-600">Browse verified properties near MUT with detailed information and photos</p>
          </div>

          <!-- Step 2 -->
          <div class="text-center">
            <div class="inline-flex items-center justify-center w-10 h-10 bg-blue-100 text-blue-600 rounded-full mb-3">
              <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
              </svg>
            </div>
            <h3 class="text-lg font-semibold mb-2">2. Connect</h3>
            <p class="text-sm text-gray-600">Chat directly with landlords and schedule property viewings</p>
          </div>

          <!-- Step 3 -->
          <div class="text-center">
            <div class="inline-flex items-center justify-center w-10 h-10 bg-blue-100 text-blue-600 rounded-full mb-3">
              <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
              </svg>
            </div>
            <h3 class="text-lg font-semibold mb-2">3. Move In</h3>
            <p class="text-sm text-gray-600">Book, pay securely via M-Pesa, and sign your contract digitally</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Features -->
    <section class="py-12 md:py-16 bg-gray-50">
      <div class="container-wide">
        <div class="text-center mb-8 md:mb-12">
          <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Why Choose Us</h2>
          <p class="text-sm md:text-base text-gray-600">The best rental experience in Murang'a County</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
          <div class="card p-6">
            <div class="text-blue-600 mb-3">
              <svg class="icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
              </svg>
            </div>
            <h3 class="text-lg font-semibold mb-2">Verified Properties</h3>
            <p class="text-sm text-gray-600">All properties and landlords are verified for your safety</p>
          </div>

          <div class="card p-6">
            <div class="text-blue-600 mb-3">
              <svg class="icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <h3 class="text-lg font-semibold mb-2">Secure Payments</h3>
            <p class="text-sm text-gray-600">Pay safely using M-Pesa with instant confirmation</p>
          </div>

          <div class="card p-6">
            <div class="text-blue-600 mb-3">
              <svg class="icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
            </div>
            <h3 class="text-lg font-semibold mb-2">24/7 Support</h3>
            <p class="text-sm text-gray-600">Our team is always here to help you</p>
          </div>

          <div class="card p-6">
            <div class="text-blue-600 mb-3">
              <svg class="icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
              </svg>
            </div>
            <h3 class="text-lg font-semibold mb-2">Digital Contracts</h3>
            <p class="text-sm text-gray-600">Sign rental agreements digitally, no paperwork hassle</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-12 md:py-16 bg-blue-600 text-white">
      <div class="container-narrow text-center">
        <h2 class="text-2xl md:text-3xl font-bold mb-3 md:mb-4">Ready to Find Your Home?</h2>
        <p class="text-base md:text-lg mb-6 md:mb-8 text-blue-100">Join thousands of students and professionals in Murang'a</p>
        <div class="flex flex-col sm:flex-row gap-3 md:gap-4 justify-center">
          <router-link
            to="/register"
            class="inline-flex items-center justify-center px-6 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-100 transition-colors"
          >
            Get Started
          </router-link>
          <router-link
            to="/properties"
            class="inline-flex items-center justify-center px-6 py-3 border-2 border-white text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors"
          >
            Browse Properties
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { usePropertiesStore } from '@/stores/properties'
import PropertyCard from '@/components/properties/PropertyCard.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'

const router = useRouter()
const propertiesStore = usePropertiesStore()

const searchQuery = ref('')
const searchType = ref('')
const featuredProperties = ref([])
const loadingProperties = ref(false)

const handleSearch = () => {
  const query = {
    search: searchQuery.value,
    type: searchType.value
  }
  router.push({ path: '/properties', query })
}

const loadFeaturedProperties = async () => {
  loadingProperties.value = true
  try {
    await propertiesStore.fetchProperties({ per_page: 6, featured: true })
    featuredProperties.value = propertiesStore.properties
  } catch (error) {
    console.error('Failed to load featured properties:', error)
  } finally {
    loadingProperties.value = false
  }
}

onMounted(() => {
  loadFeaturedProperties()
})
</script>
