<template>
  <transition name="fade">
    <div 
      v-if="show"
      class="rounded-lg p-4 mb-4"
      :class="alertClass"
      role="alert"
    >
      <div class="flex items-start">
        <!-- Icon -->
        <div class="flex-shrink-0">
          <svg 
            v-if="type === 'error'" 
            class="w-5 h-5"
            :class="iconColorClass"
            fill="currentColor" 
            viewBox="0 0 20 20"
          >
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
          </svg>
          
          <svg 
            v-else-if="type === 'warning'" 
            class="w-5 h-5"
            :class="iconColorClass"
            fill="currentColor" 
            viewBox="0 0 20 20"
          >
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
          </svg>
          
          <svg 
            v-else-if="type === 'success'" 
            class="w-5 h-5"
            :class="iconColorClass"
            fill="currentColor" 
            viewBox="0 0 20 20"
          >
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
          
          <svg 
            v-else 
            class="w-5 h-5"
            :class="iconColorClass"
            fill="currentColor" 
            viewBox="0 0 20 20"
          >
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
          </svg>
        </div>

        <!-- Content -->
        <div class="ml-3 flex-1">
          <h3 v-if="title" class="text-sm font-medium" :class="titleColorClass">
            {{ title }}
          </h3>
          <div class="text-sm" :class="[title ? 'mt-1' : '', messageColorClass]">
            <p v-if="message">{{ message }}</p>
            <slot v-else></slot>
          </div>
          
          <!-- Error details (for development) -->
          <div v-if="details && showDetails" class="mt-2">
            <button
              @click="detailsExpanded = !detailsExpanded"
              class="text-xs font-medium underline"
              :class="messageColorClass"
            >
              {{ detailsExpanded ? 'Hide' : 'Show' }} Details
            </button>
            <pre 
              v-if="detailsExpanded" 
              class="mt-2 text-xs overflow-auto p-2 rounded bg-black bg-opacity-10"
              :class="messageColorClass"
            >{{ details }}</pre>
          </div>
        </div>

        <!-- Close button -->
        <div v-if="dismissible" class="ml-auto pl-3">
          <button
            @click="close"
            class="inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2"
            :class="closeButtonClass"
          >
            <span class="sr-only">Dismiss</span>
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'error',
    validator: (value) => ['error', 'warning', 'success', 'info'].includes(value)
  },
  title: {
    type: String,
    default: ''
  },
  message: {
    type: String,
    default: ''
  },
  details: {
    type: [String, Object],
    default: null
  },
  showDetails: {
    type: Boolean,
    default: false
  },
  dismissible: {
    type: Boolean,
    default: true
  },
  autoDismiss: {
    type: Boolean,
    default: false
  },
  dismissAfter: {
    type: Number,
    default: 5000
  },
  modelValue: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:modelValue', 'close'])

const show = ref(props.modelValue)
const detailsExpanded = ref(false)
let dismissTimer = null

const alertClass = computed(() => {
  const classes = {
    error: 'bg-red-50 border border-red-200',
    warning: 'bg-yellow-50 border border-yellow-200',
    success: 'bg-green-50 border border-green-200',
    info: 'bg-blue-50 border border-blue-200'
  }
  return classes[props.type]
})

const iconColorClass = computed(() => {
  const classes = {
    error: 'text-red-400',
    warning: 'text-yellow-400',
    success: 'text-green-400',
    info: 'text-blue-400'
  }
  return classes[props.type]
})

const titleColorClass = computed(() => {
  const classes = {
    error: 'text-red-800',
    warning: 'text-yellow-800',
    success: 'text-green-800',
    info: 'text-blue-800'
  }
  return classes[props.type]
})

const messageColorClass = computed(() => {
  const classes = {
    error: 'text-red-700',
    warning: 'text-yellow-700',
    success: 'text-green-700',
    info: 'text-blue-700'
  }
  return classes[props.type]
})

const closeButtonClass = computed(() => {
  const classes = {
    error: 'text-red-500 hover:bg-red-100 focus:ring-red-600',
    warning: 'text-yellow-500 hover:bg-yellow-100 focus:ring-yellow-600',
    success: 'text-green-500 hover:bg-green-100 focus:ring-green-600',
    info: 'text-blue-500 hover:bg-blue-100 focus:ring-blue-600'
  }
  return classes[props.type]
})

const close = () => {
  show.value = false
  emit('update:modelValue', false)
  emit('close')
  clearDismissTimer()
}

const clearDismissTimer = () => {
  if (dismissTimer) {
    clearTimeout(dismissTimer)
    dismissTimer = null
  }
}

const startDismissTimer = () => {
  if (props.autoDismiss && props.dismissAfter > 0) {
    dismissTimer = setTimeout(() => {
      close()
    }, props.dismissAfter)
  }
}

watch(() => props.modelValue, (newValue) => {
  show.value = newValue
  if (newValue) {
    startDismissTimer()
  }
})

onMounted(() => {
  if (show.value) {
    startDismissTimer()
  }
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>