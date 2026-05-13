<template>
  <div class="min-h-screen bg-slate-950 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <router-link to="/" class="flex justify-center">
        <h1 class="text-3xl font-bold text-gold">Murang'a Rentals</h1>
      </router-link>
      <h2 class="mt-6 text-center text-3xl font-extrabold text-white">
        Sign in to your account
      </h2>
      <p class="mt-2 text-center text-sm text-slate-400">
        Or
        <router-link to="/register" class="font-medium text-gold hover:text-gold/80">
          create a new account
        </router-link>
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-slate-800/90 backdrop-blur-sm py-8 px-4 shadow-2xl sm:rounded-lg sm:px-10 border border-slate-700">
        <!-- Error Alert -->
        <ErrorAlert
          v-if="error"
          type="error"
          :message="error"
          @close="error = ''"
          class="mb-4"
        />

        <!-- Login Form -->
        <form @submit.prevent="handleLogin" class="space-y-6">
          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-slate-300">
              Email address
            </label>
            <div class="mt-1">
              <input
                id="email"
                v-model="form.email"
                type="email"
                autocomplete="email"
                required
                class="appearance-none block w-full px-3 py-2 border border-slate-600 rounded-md shadow-sm placeholder-slate-500 bg-slate-700 text-white focus:outline-none focus:ring-gold focus:border-gold sm:text-sm"
                :class="{ 'border-red-300': errors.email }"
                placeholder="you@example.com"
              />
              <p v-if="errors.email" class="mt-1 text-sm text-red-600">
                {{ errors.email }}
              </p>
            </div>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block text-sm font-medium text-slate-300">
              Password
            </label>
            <div class="mt-1 relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                required
                class="appearance-none block w-full px-3 py-2 border border-slate-600 rounded-md shadow-sm placeholder-slate-500 bg-slate-700 text-white focus:outline-none focus:ring-gold focus:border-gold sm:text-sm"
                :class="{ 'border-red-300': errors.password }"
                placeholder="••••••••"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3 flex items-center"
              >
                <svg v-if="!showPassword" class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg v-else class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
              </button>
              <p v-if="errors.password" class="mt-1 text-sm text-red-600">
                {{ errors.password }}
              </p>
            </div>
          </div>

          <!-- Remember Me & Forgot Password -->
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input
                id="remember-me"
                v-model="form.remember"
                type="checkbox"
                class="h-4 w-4 text-gold focus:ring-gold border-slate-600 rounded bg-slate-700"
              />
              <label for="remember-me" class="ml-2 block text-sm text-slate-300">
                Remember me
              </label>
            </div>

            <div class="text-sm">
              <router-link to="/forgot-password" class="font-medium text-gold hover:text-gold/80">
                Forgot your password?
              </router-link>
            </div>
          </div>

          <!-- Submit Button -->
          <div>
            <button
              type="submit"
              :disabled="loading"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gold hover:bg-gold/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gold disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <LoadingSpinner v-if="loading" size="sm" color="white" class="mr-2" />
              <span>{{ loading ? 'Signing in...' : 'Sign in' }}</span>
            </button>
          </div>
        </form>

        <!-- Divider -->
        <div class="mt-6">
          <div class="relative">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-slate-600"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-2 bg-slate-800/90 text-slate-400">Quick Login (Demo)</span>
            </div>
          </div>
        </div>

        <!-- Demo Accounts -->
        <div class="mt-6 grid grid-cols-1 gap-3">
          <button
            @click="quickLogin('tenant')"
            type="button"
            class="w-full inline-flex justify-center py-2 px-4 border border-slate-600 rounded-md shadow-sm bg-slate-700 text-sm font-medium text-slate-300 hover:bg-slate-600"
          >
            Login as Tenant (Student)
          </button>
          <button
            @click="quickLogin('landlord')"
            type="button"
            class="w-full inline-flex justify-center py-2 px-4 border border-slate-600 rounded-md shadow-sm bg-slate-700 text-sm font-medium text-slate-300 hover:bg-slate-600"
          >
            Login as Landlord
          </button>
          <button
            @click="quickLogin('admin')"
            type="button"
            class="w-full inline-flex justify-center py-2 px-4 border border-slate-600 rounded-md shadow-sm bg-slate-700 text-sm font-medium text-slate-300 hover:bg-slate-600"
          >
            Login as Admin
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import ErrorAlert from '@/components/common/ErrorAlert.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  email: '',
  password: '',
  remember: false
})

const errors = reactive({
  email: '',
  password: ''
})

const error = ref('')
const loading = ref(false)
const showPassword = ref(false)

// Demo credentials
const demoAccounts = {
  tenant: {
    email: 'james.omondi@student.mut.ac.ke',
    password: 'password123'
  },
  landlord: {
    email: 'john.kamau@gmail.com',
    password: 'password123'
  },
  admin: {
    email: 'admin@murangarentals.com',
    password: 'password123'
  }
}

const validateForm = () => {
  let isValid = true
  errors.email = ''
  errors.password = ''

  if (!form.email) {
    errors.email = 'Email is required'
    isValid = false
  } else if (!/\S+@\S+\.\S+/.test(form.email)) {
    errors.email = 'Email is invalid'
    isValid = false
  }

  if (!form.password) {
    errors.password = 'Password is required'
    isValid = false
  } else if (form.password.length < 6) {
    errors.password = 'Password must be at least 6 characters'
    isValid = false
  }

  return isValid
}

const handleLogin = async () => {
  if (!validateForm()) return

  loading.value = true
  error.value = ''

  try {
    await authStore.login(form.email, form.password, form.remember)
    
    // Redirect based on user role
    const user = authStore.user
    if (user.role === 'admin') {
      router.push('/admin/dashboard')
    } else if (user.role === 'landlord') {
      router.push('/landlord/dashboard')
    } else {
      router.push('/tenant/dashboard')
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Invalid email or password'
  } finally {
    loading.value = false
  }
}

const quickLogin = async (role) => {
  const account = demoAccounts[role]
  form.email = account.email
  form.password = account.password
  form.remember = false
  
  await handleLogin()
}
</script>