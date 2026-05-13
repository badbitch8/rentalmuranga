<template>
  <div class="flex items-center justify-center" :class="containerClass">
    <div class="relative">
      <!-- Spinner -->
      <div 
        class="animate-spin rounded-full border-t-2 border-b-2"
        :class="[sizeClass, colorClass]"
      ></div>
      
      <!-- Center dot (optional) -->
      <div 
        v-if="showDot"
        class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 rounded-full"
        :class="dotClass"
      ></div>
    </div>
    
    <!-- Loading text -->
    <p v-if="text" class="mt-4 text-gray-600" :class="textSizeClass">
      {{ text }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  color: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'white', 'gray'].includes(value)
  },
  text: {
    type: String,
    default: ''
  },
  fullScreen: {
    type: Boolean,
    default: false
  },
  showDot: {
    type: Boolean,
    default: false
  }
})

const sizeClass = computed(() => {
  const sizes = {
    xs: 'w-4 h-4',
    sm: 'w-6 h-6',
    md: 'w-10 h-10',
    lg: 'w-16 h-16',
    xl: 'w-24 h-24'
  }
  return sizes[props.size]
})

const colorClass = computed(() => {
  const colors = {
    primary: 'border-blue-600',
    secondary: 'border-green-600',
    white: 'border-white',
    gray: 'border-gray-600'
  }
  return colors[props.color]
})

const dotClass = computed(() => {
  const sizes = {
    xs: 'w-1 h-1',
    sm: 'w-1.5 h-1.5',
    md: 'w-2 h-2',
    lg: 'w-3 h-3',
    xl: 'w-4 h-4'
  }
  const colors = {
    primary: 'bg-blue-600',
    secondary: 'bg-green-600',
    white: 'bg-white',
    gray: 'bg-gray-600'
  }
  return `${sizes[props.size]} ${colors[props.color]}`
})

const textSizeClass = computed(() => {
  const sizes = {
    xs: 'text-xs',
    sm: 'text-sm',
    md: 'text-base',
    lg: 'text-lg',
    xl: 'text-xl'
  }
  return sizes[props.size]
})

const containerClass = computed(() => {
  return props.fullScreen 
    ? 'fixed inset-0 bg-white bg-opacity-90 z-50 flex-col' 
    : 'py-8 flex-col'
})
</script>

<style scoped>
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}
</style>