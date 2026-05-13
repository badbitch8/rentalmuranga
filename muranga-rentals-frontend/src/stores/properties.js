import { defineStore } from 'pinia'
import api from '@/services/api'

export const usePropertiesStore = defineStore('properties', {
  state: () => ({
    properties: [],
    currentProperty: null,
    favorites: [],
    loading: false,
    error: null,
    pagination: {
      currentPage: 1,
      lastPage: 1,
      perPage: 15,
      total: 0
    },
    filters: {
      min_price: null,
      max_price: null,
      bedrooms: null,
      type: null,
      location: null,
      max_distance: null,
      amenities: []
    }
  }),

  getters: {
    filteredProperties: (state) => state.properties,
    hasMore: (state) => state.pagination.currentPage < state.pagination.lastPage,
    totalProperties: (state) => state.pagination.total
  },

  actions: {
    async fetchProperties(page = 1) {
      this.loading = true
      this.error = null

      try {
        const params = {
          page,
          per_page: this.pagination.perPage,
          ...this.filters
        }

        // Remove null/empty filters
        Object.keys(params).forEach(key => {
          if (params[key] === null || params[key] === '' || 
              (Array.isArray(params[key]) && params[key].length === 0)) {
            delete params[key]
          }
        })

        const response = await api.get('/properties', { params })
        
        this.properties = response.data.data.data
        this.pagination = {
          currentPage: response.data.data.current_page,
          lastPage: response.data.data.last_page,
          perPage: response.data.data.per_page,
          total: response.data.data.total
        }

        return { success: true, data: response.data }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch properties'
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchPropertyById(id) {
      this.loading = true
      this.error = null

      try {
        const response = await api.get(`/properties/${id}`)
        this.currentProperty = response.data.data
        return { success: true, data: response.data }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch property'
        throw error
      } finally {
        this.loading = false
      }
    },

    async searchProperties(keyword) {
      this.loading = true
      this.error = null

      try {
        const response = await api.get('/properties/search', {
          params: { keyword, ...this.filters }
        })
        
        this.properties = response.data.data.data
        return { success: true, data: response.data }
      } catch (error) {
        this.error = error.response?.data?.message || 'Search failed'
        throw error
      } finally {
        this.loading = false
      }
    },

    async toggleFavorite(propertyId) {
      try {
        const response = await api.post(`/properties/${propertyId}/favorite`)
        
        // Update local favorites list
        const index = this.favorites.findIndex(id => id === propertyId)
        if (index > -1) {
          this.favorites.splice(index, 1)
        } else {
          this.favorites.push(propertyId)
        }

        return { success: true, data: response.data }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to toggle favorite'
        throw error
      }
    },

    async fetchFavorites() {
      this.loading = true
      this.error = null

      try {
        const response = await api.get('/favorites')
        this.favorites = response.data.data.map(prop => prop.id)
        return { success: true, data: response.data }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch favorites'
        throw error
      } finally {
        this.loading = false
      }
    },

    setFilters(newFilters) {
      this.filters = { ...this.filters, ...newFilters }
    },

    clearFilters() {
      this.filters = {
        min_price: null,
        max_price: null,
        bedrooms: null,
        type: null,
        location: null,
        max_distance: null,
        amenities: []
      }
    },

    isFavorite(propertyId) {
      return this.favorites.includes(propertyId)
    }
  }
})

// Made with Bob
