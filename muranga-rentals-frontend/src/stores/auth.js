import { defineStore } from 'pinia'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('auth_token') || null,
    isAuthenticated: !!localStorage.getItem('auth_token'),
    loading: false,
    error: null
  }),

  getters: {
    isLandlord: (state) => state.user?.role === 'landlord',
    isTenant: (state) => state.user?.role === 'tenant',
    isAdmin: (state) => state.user?.role === 'admin',
    userName: (state) => state.user?.name || 'Guest',
    userEmail: (state) => state.user?.email || ''
  },

  actions: {
    async login(credentials) {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.post('/login', credentials)
        this.setAuth(response.data.data)
        return { success: true, data: response.data }
      } catch (error) {
        this.error = error.response?.data?.message || 'Login failed'
        throw error
      } finally {
        this.loading = false
      }
    },

    async register(userData) {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.post('/register', userData)
        this.setAuth(response.data.data)
        return { success: true, data: response.data }
      } catch (error) {
        this.error = error.response?.data?.message || 'Registration failed'
        throw error
      } finally {
        this.loading = false
      }
    },

    async logout() {
      this.loading = true
      
      try {
        await api.post('/logout')
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.clearAuth()
        this.loading = false
      }
    },

    async fetchUser() {
      if (!this.token) return
      
      try {
        const response = await api.get('/me')
        this.user = response.data.data
        localStorage.setItem('user', JSON.stringify(response.data.data))
      } catch (error) {
        console.error('Fetch user error:', error)
        this.clearAuth()
      }
    },

    async updateProfile(profileData) {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.put('/profile', profileData)
        this.user = response.data.data
        localStorage.setItem('user', JSON.stringify(response.data.data))
        return { success: true, data: response.data }
      } catch (error) {
        this.error = error.response?.data?.message || 'Profile update failed'
        throw error
      } finally {
        this.loading = false
      }
    },

    setAuth(data) {
      this.user = data.user
      this.token = data.token
      this.isAuthenticated = true
      localStorage.setItem('user', JSON.stringify(data.user))
      localStorage.setItem('auth_token', data.token)
    },

    clearAuth() {
      this.user = null
      this.token = null
      this.isAuthenticated = false
      this.error = null
      localStorage.removeItem('user')
      localStorage.removeItem('auth_token')
    }
  }
})

// Made with Bob
