<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container-narrow py-6 md:py-8">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">My Profile</h1>
        <p class="text-sm md:text-base text-gray-600">
          Manage your account information and preferences
        </p>
      </div>

      <!-- Profile Card -->
      <div class="card mb-6">
        <div class="flex flex-col md:flex-row items-center gap-6">
          <!-- Avatar -->
          <div class="relative">
            <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center">
              <span class="text-3xl font-bold text-gray-600">
                {{ user?.name?.charAt(0) }}
              </span>
            </div>
            <button
              @click="changeAvatar"
              class="absolute bottom-0 right-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center hover:bg-blue-700 transition-colors"
            >
              <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </button>
          </div>

          <!-- User Info -->
          <div class="flex-1 text-center md:text-left">
            <h2 class="text-xl font-bold text-gray-900 mb-1">{{ user?.name }}</h2>
            <p class="text-sm text-gray-600 mb-2">{{ user?.email }}</p>
            <div class="flex flex-wrap gap-2 justify-center md:justify-start">
              <span class="badge badge-primary">{{ formatRole(user?.role) }}</span>
              <span v-if="user?.is_verified" class="badge badge-success">Verified</span>
              <span v-else class="badge badge-warning">Unverified</span>
            </div>
          </div>
        </div>
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
          </button>
        </nav>
      </div>

      <!-- Personal Information Tab -->
      <div v-if="activeTab === 'personal'" class="card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h3>
        <form @submit.prevent="updateProfile" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
              <input v-model="form.name" type="text" class="input-field" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input v-model="form.email" type="email" class="input-field" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
              <input v-model="form.phone" type="tel" class="input-field" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
              <input v-model="form.date_of_birth" type="date" class="input-field" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
            <textarea v-model="form.bio" rows="3" class="input-field" placeholder="Tell us about yourself..."></textarea>
          </div>
          <div class="flex justify-end gap-3">
            <button type="button" @click="resetForm" class="btn-outline">Cancel</button>
            <button type="submit" :disabled="saving" class="btn-primary">
              {{ saving ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Security Tab -->
      <div v-if="activeTab === 'security'" class="space-y-6">
        <!-- Change Password -->
        <div class="card">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Change Password</h3>
          <form @submit.prevent="changePassword" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
              <input v-model="passwordForm.current_password" type="password" class="input-field" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
              <input v-model="passwordForm.new_password" type="password" class="input-field" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
              <input v-model="passwordForm.confirm_password" type="password" class="input-field" required />
            </div>
            <div class="flex justify-end">
              <button type="submit" :disabled="changingPassword" class="btn-primary">
                {{ changingPassword ? 'Updating...' : 'Update Password' }}
              </button>
            </div>
          </form>
        </div>

        <!-- Two-Factor Authentication -->
        <div class="card">
          <div class="flex items-start justify-between mb-4">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-1">Two-Factor Authentication</h3>
              <p class="text-sm text-gray-600">Add an extra layer of security to your account</p>
            </div>
            <button class="btn-outline text-sm">
              {{ user?.two_factor_enabled ? 'Disable' : 'Enable' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Notifications Tab -->
      <div v-if="activeTab === 'notifications'" class="card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Notification Preferences</h3>
        <div class="space-y-4">
          <div class="flex items-center justify-between py-3 border-b border-gray-200">
            <div>
              <p class="text-sm font-medium text-gray-900">Email Notifications</p>
              <p class="text-xs text-gray-600">Receive updates via email</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input v-model="notifications.email" type="checkbox" class="sr-only peer" />
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
          </div>
          <div class="flex items-center justify-between py-3 border-b border-gray-200">
            <div>
              <p class="text-sm font-medium text-gray-900">SMS Notifications</p>
              <p class="text-xs text-gray-600">Receive updates via SMS</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input v-model="notifications.sms" type="checkbox" class="sr-only peer" />
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
          </div>
          <div class="flex items-center justify-between py-3 border-b border-gray-200">
            <div>
              <p class="text-sm font-medium text-gray-900">Booking Updates</p>
              <p class="text-xs text-gray-600">Get notified about booking status changes</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input v-model="notifications.bookings" type="checkbox" class="sr-only peer" />
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
          </div>
          <div class="flex items-center justify-between py-3">
            <div>
              <p class="text-sm font-medium text-gray-900">Marketing Communications</p>
              <p class="text-xs text-gray-600">Receive promotional offers and updates</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input v-model="notifications.marketing" type="checkbox" class="sr-only peer" />
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
          </div>
        </div>
        <div class="flex justify-end mt-6">
          <button @click="saveNotificationPreferences" class="btn-primary">
            Save Preferences
          </button>
        </div>
      </div>

      <!-- Danger Zone -->
      <div class="card border-red-200 bg-red-50 mt-6">
        <h3 class="text-lg font-semibold text-red-900 mb-2">Danger Zone</h3>
        <p class="text-sm text-red-800 mb-4">
          Once you delete your account, there is no going back. Please be certain.
        </p>
        <button @click="deleteAccount" class="btn-outline border-red-600 text-red-600 hover:bg-red-50">
          Delete Account
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const user = computed(() => authStore.user)
const activeTab = ref('personal')
const saving = ref(false)
const changingPassword = ref(false)

const tabs = [
  { id: 'personal', name: 'Personal Info' },
  { id: 'security', name: 'Security' },
  { id: 'notifications', name: 'Notifications' }
]

const form = reactive({
  name: '',
  email: '',
  phone: '',
  date_of_birth: '',
  bio: ''
})

const passwordForm = reactive({
  current_password: '',
  new_password: '',
  confirm_password: ''
})

const notifications = reactive({
  email: true,
  sms: false,
  bookings: true,
  marketing: false
})

const formatRole = (role) => {
  return role?.charAt(0).toUpperCase() + role?.slice(1) || 'User'
}

const changeAvatar = () => {
  // TODO: Implement avatar upload
  console.log('Change avatar clicked')
}

const updateProfile = async () => {
  saving.value = true
  try {
    // TODO: Implement API call
    // await api.put('/profile', form)
    console.log('Updating profile:', form)
    alert('Profile updated successfully!')
  } catch (error) {
    console.error('Error updating profile:', error)
    alert('Failed to update profile')
  } finally {
    saving.value = false
  }
}

const changePassword = async () => {
  if (passwordForm.new_password !== passwordForm.confirm_password) {
    alert('Passwords do not match')
    return
  }
  
  changingPassword.value = true
  try {
    // TODO: Implement API call
    // await api.put('/profile/password', passwordForm)
    console.log('Changing password')
    alert('Password changed successfully!')
    Object.keys(passwordForm).forEach(key => passwordForm[key] = '')
  } catch (error) {
    console.error('Error changing password:', error)
    alert('Failed to change password')
  } finally {
    changingPassword.value = false
  }
}

const saveNotificationPreferences = async () => {
  try {
    // TODO: Implement API call
    // await api.put('/profile/notifications', notifications)
    console.log('Saving notification preferences:', notifications)
    alert('Preferences saved successfully!')
  } catch (error) {
    console.error('Error saving preferences:', error)
    alert('Failed to save preferences')
  }
}

const deleteAccount = async () => {
  if (!confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
    return
  }
  
  if (!confirm('This will permanently delete all your data. Are you absolutely sure?')) {
    return
  }
  
  try {
    // TODO: Implement API call
    // await api.delete('/profile')
    console.log('Deleting account')
    authStore.logout()
    router.push('/')
  } catch (error) {
    console.error('Error deleting account:', error)
    alert('Failed to delete account')
  }
}

const resetForm = () => {
  if (user.value) {
    form.name = user.value.name || ''
    form.email = user.value.email || ''
    form.phone = user.value.phone || ''
    form.date_of_birth = user.value.date_of_birth || ''
    form.bio = user.value.bio || ''
  }
}

onMounted(() => {
  resetForm()
})
</script>