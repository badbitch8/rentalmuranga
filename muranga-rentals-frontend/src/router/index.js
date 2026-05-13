import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Lazy load views for better performance
const HomeView = () => import('@/views/HomeView.vue')
const LoginView = () => import('@/views/auth/LoginView.vue')
const RegisterView = () => import('@/views/auth/RegisterView.vue')

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
      meta: { title: 'Home - Murang\'a Rentals' }
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: { 
        title: 'Login - Murang\'a Rentals',
        guest: true 
      }
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
      meta: { 
        title: 'Register - Murang\'a Rentals',
        guest: true 
      }
    },
    {
      path: '/properties',
      name: 'properties',
      component: () => import('@/views/properties/PropertyListView.vue'),
      meta: { title: 'Browse Properties - Murang\'a Rentals' }
    },
    {
      path: '/properties/:id',
      name: 'property-detail',
      component: () => import('@/views/properties/PropertyDetailView.vue'),
      meta: { title: 'Property Details - Murang\'a Rentals' }
    },
    {
      path: '/tenant/dashboard',
      name: 'tenant-dashboard',
      component: () => import('@/views/dashboard/TenantDashboard.vue'),
      meta: { 
        title: 'Tenant Dashboard - Murang\'a Rentals',
        requiresAuth: true,
        role: 'tenant'
      }
    },
    {
      path: '/landlord/dashboard',
      name: 'landlord-dashboard',
      component: () => import('@/views/dashboard/LandlordDashboard.vue'),
      meta: { 
        title: 'Landlord Dashboard - Murang\'a Rentals',
        requiresAuth: true,
        role: 'landlord'
      }
    },
    {
      path: '/admin/dashboard',
      name: 'admin-dashboard',
      component: () => import('@/views/dashboard/AdminDashboard.vue'),
      meta: { 
        title: 'Admin Dashboard - Murang\'a Rentals',
        requiresAuth: true,
        role: 'admin'
      }
    },
    {
      path: '/profile',
      name: 'profile',
      component: () => import('@/views/profile/ProfileView.vue'),
      meta: { 
        title: 'My Profile - Murang\'a Rentals',
        requiresAuth: true
      }
    },
    {
      path: '/bookings',
      name: 'bookings',
      component: () => import('@/views/bookings/BookingsView.vue'),
      meta: { 
        title: 'My Bookings - Murang\'a Rentals',
        requiresAuth: true
      }
    },
    {
      path: '/messages',
      name: 'messages',
      component: () => import('@/views/messages/MessagesView.vue'),
      meta: { 
        title: 'Messages - Murang\'a Rentals',
        requiresAuth: true
      }
    },
    {
      path: '/favorites',
      name: 'favorites',
      component: () => import('@/views/favorites/FavoritesView.vue'),
      meta: { 
        title: 'My Favorites - Murang\'a Rentals',
        requiresAuth: true
      }
    },
    {
      path: '/about',
      name: 'about',
      component: () => import('@/views/static/AboutView.vue'),
      meta: { title: 'About Us - Murang\'a Rentals' }
    },
    {
      path: '/contact',
      name: 'contact',
      component: () => import('@/views/static/ContactView.vue'),
      meta: { title: 'Contact Us - Murang\'a Rentals' }
    },
    {
      path: '/terms',
      name: 'terms',
      component: () => import('@/views/static/TermsView.vue'),
      meta: { title: 'Terms of Service - Murang\'a Rentals' }
    },
    {
      path: '/privacy',
      name: 'privacy',
      component: () => import('@/views/static/PrivacyView.vue'),
      meta: { title: 'Privacy Policy - Murang\'a Rentals' }
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('@/views/NotFoundView.vue'),
      meta: { title: '404 - Page Not Found' }
    }
  ],
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  }
})

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Set page title
  document.title = to.meta.title || 'Murang\'a Rentals'
  
  // Check if route requires authentication
  if (to.meta.requiresAuth) {
    if (!authStore.isAuthenticated) {
      // Redirect to login if not authenticated
      next({
        name: 'login',
        query: { redirect: to.fullPath }
      })
      return
    }
    
    // Check role-based access
    if (to.meta.role && authStore.user?.role !== to.meta.role) {
      // Redirect to appropriate dashboard based on user role
      if (authStore.user?.role === 'admin') {
        next({ name: 'admin-dashboard' })
      } else if (authStore.user?.role === 'landlord') {
        next({ name: 'landlord-dashboard' })
      } else {
        next({ name: 'tenant-dashboard' })
      }
      return
    }
  }
  
  // Redirect authenticated users away from guest-only pages
  if (to.meta.guest && authStore.isAuthenticated) {
    if (authStore.user?.role === 'admin') {
      next({ name: 'admin-dashboard' })
    } else if (authStore.user?.role === 'landlord') {
      next({ name: 'landlord-dashboard' })
    } else {
      next({ name: 'tenant-dashboard' })
    }
    return
  }
  
  next()
})

export default router

// Made with Bob
