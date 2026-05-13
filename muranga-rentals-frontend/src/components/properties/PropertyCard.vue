<template>
  <div class="card hover:shadow-xl transition-shadow duration-300 cursor-pointer" @click="viewProperty">
    <!-- Image -->
    <div class="relative h-48 -mx-6 -mt-6 mb-4 overflow-hidden rounded-t-lg">
      <img 
        :src="propertyImage" 
        :alt="property.name"
        class="w-full h-full object-cover hover:scale-110 transition-transform duration-300"
      />
      
      <!-- Favorite Button -->
      <button 
        @click.stop="toggleFavorite"
        class="absolute top-3 right-3 p-2 bg-slate-800/90 rounded-full shadow-lg hover:bg-slate-700 transition"
        :class="{ 'text-red-500': isFavorite, 'text-slate-400': !isFavorite }"
      >
        <svg class="h-6 w-6" :fill="isFavorite ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
        </svg>
      </button>

      <!-- Property Type Badge -->
      <div class="absolute top-3 left-3 px-3 py-1 bg-primary-600 text-white text-xs font-semibold rounded-full">
        {{ formatPropertyType(property.type) }}
      </div>

      <!-- Distance Badge -->
      <div v-if="property.distance_to_mut" class="absolute bottom-3 left-3 px-3 py-1 bg-slate-800/90 text-slate-100 text-xs font-semibold rounded-full shadow">
        {{ property.distance_to_mut }}km to MUT
      </div>
    </div>

    <!-- Content -->
    <div>
      <!-- Title -->
      <h3 class="text-lg font-bold text-white mb-2 line-clamp-1">
        {{ property.name }}
      </h3>

      <!-- Location -->
      <div class="flex items-center text-slate-400 text-sm mb-3">
        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <span class="line-clamp-1">{{ property.location }}</span>
      </div>

      <!-- Features -->
      <div class="flex items-center space-x-4 text-sm text-slate-400 mb-4">
        <div class="flex items-center">
          <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          <span>{{ property.bedrooms }} Bed</span>
        </div>
        <div class="flex items-center">
          <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <span>{{ property.bathrooms }} Bath</span>
        </div>
        <div v-if="property.square_feet" class="flex items-center">
          <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
          </svg>
          <span>{{ property.square_feet }} sqft</span>
        </div>
      </div>

      <!-- Amenities -->
      <div v-if="property.amenities && property.amenities.length > 0" class="flex flex-wrap gap-2 mb-4">
        <span 
          v-for="amenity in property.amenities.slice(0, 3)" 
          :key="amenity"
          class="px-2 py-1 bg-slate-700 text-slate-300 text-xs rounded"
        >
          {{ formatAmenity(amenity) }}
        </span>
        <span 
          v-if="property.amenities.length > 3"
          class="px-2 py-1 bg-slate-700 text-slate-300 text-xs rounded"
        >
          +{{ property.amenities.length - 3 }} more
        </span>
      </div>

      <!-- Price and CTA -->
      <div class="flex items-center justify-between pt-4 border-t">
        <div>
          <p class="text-2xl font-bold text-primary-600">
            KES {{ formatPrice(property.price) }}
          </p>
          <p class="text-xs text-slate-500">per month</p>
        </div>
        <button 
          @click.stop="viewProperty"
          class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition text-sm font-semibold"
        >
          View Details
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { usePropertiesStore } from '@/stores/properties'

const props = defineProps({
  property: {
    type: Object,
    required: true
  }
})

const router = useRouter()
const propertiesStore = usePropertiesStore()

const propertyImage = computed(() => {
  if (props.property.images && props.property.images.length > 0) {
    return props.property.images[0].image_path
  }
  return 'https://via.placeholder.com/400x300?text=No+Image'
})

const isFavorite = computed(() => {
  return propertiesStore.isFavorite(props.property.id)
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-KE').format(price)
}

const formatPropertyType = (type) => {
  return type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatAmenity = (amenity) => {
  return amenity.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const viewProperty = () => {
  router.push(`/properties/${props.property.id}`)
}

const toggleFavorite = async () => {
  try {
    await propertiesStore.toggleFavorite(props.property.id)
  } catch (error) {
    console.error('Failed to toggle favorite:', error)
  }
}
</script>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>