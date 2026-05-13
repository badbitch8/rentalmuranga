# 🎨 Murang'a Rentals - Frontend Setup Guide

Complete guide to set up the Vue.js frontend for the rental marketplace.

---

## 📋 Prerequisites

- ✅ Node.js 18+ installed
- ✅ npm or yarn installed
- ✅ Backend API running (Laravel server)
- ✅ Basic knowledge of Vue.js 3

---

## 🚀 Quick Setup

### **Step 1: Install Node.js** (if not installed)

1. Download from: https://nodejs.org/
2. Install LTS version (18.x or higher)
3. Verify installation:
```bash
node --version
npm --version
```

---

### **Step 2: Initialize Vue.js Project**

```bash
# Navigate to project root
cd c:\Users\HP\Rental

# Create Vue.js project
npm create vue@latest muranga-rentals-frontend

# Follow prompts:
# ✔ Project name: muranga-rentals-frontend
# ✔ Add TypeScript? No
# ✔ Add JSX Support? No
# ✔ Add Vue Router? Yes
# ✔ Add Pinia? Yes (for state management)
# ✔ Add Vitest? No
# ✔ Add an End-to-End Testing Solution? No
# ✔ Add ESLint? Yes
# ✔ Add Prettier? Yes

# Navigate to frontend folder
cd muranga-rentals-frontend

# Install dependencies
npm install
```

---

### **Step 3: Install Additional Dependencies**

```bash
# Install Axios for API calls
npm install axios

# Install UI Framework (Tailwind CSS)
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p

# Install icons
npm install @heroicons/vue

# Install date utilities
npm install date-fns

# Install form validation
npm install vee-validate yup

# Install notifications
npm install vue-toastification

# Install charts (for analytics)
npm install chart.js vue-chartjs

# Install image viewer
npm install viewerjs
```

---

### **Step 4: Configure Tailwind CSS**

Update `tailwind.config.js`:

```javascript
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#f0f9ff',
          100: '#e0f2fe',
          200: '#bae6fd',
          300: '#7dd3fc',
          400: '#38bdf8',
          500: '#0ea5e9',
          600: '#0284c7',
          700: '#0369a1',
          800: '#075985',
          900: '#0c4a6e',
        },
      },
    },
  },
  plugins: [],
}
```

Update `src/assets/main.css`:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

/* Custom styles */
@layer components {
  .btn-primary {
    @apply bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition;
  }
  
  .btn-secondary {
    @apply bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 transition;
  }
  
  .input-field {
    @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent;
  }
  
  .card {
    @apply bg-white rounded-lg shadow-md p-6;
  }
}
```

---

### **Step 5: Configure API Client**

Create `src/services/api.js`:

```javascript
import axios from 'axios'

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Add auth token to requests
api.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Handle response errors
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api
```

---

### **Step 6: Set Up Pinia Store**

Create `src/stores/auth.js`:

```javascript
import { defineStore } from 'pinia'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('auth_token') || null,
    isAuthenticated: !!localStorage.getItem('auth_token')
  }),

  getters: {
    isLandlord: (state) => state.user?.role === 'landlord',
    isTenant: (state) => state.user?.role === 'tenant',
    isAdmin: (state) => state.user?.role === 'admin'
  },

  actions: {
    async login(credentials) {
      const response = await api.post('/login', credentials)
      this.setAuth(response.data.data)
      return response.data
    },

    async register(userData) {
      const response = await api.post('/register', userData)
      this.setAuth(response.data.data)
      return response.data
    },

    async logout() {
      try {
        await api.post('/logout')
      } finally {
        this.clearAuth()
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
      localStorage.removeItem('user')
      localStorage.removeItem('auth_token')
    }
  }
})
```

---

### **Step 7: Configure Vue Router**

Update `src/router/index.js`:

```javascript
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('@/views/HomeView.vue')
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/auth/LoginView.vue')
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/views/auth/RegisterView.vue')
    },
    {
      path: '/properties',
      name: 'properties',
      component: () => import('@/views/properties/PropertyListView.vue')
    },
    {
      path: '/properties/:id',
      name: 'property-details',
      component: () => import('@/views/properties/PropertyDetailView.vue')
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('@/views/dashboard/DashboardView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/landlord',
      name: 'landlord-dashboard',
      component: () => import('@/views/landlord/LandlordDashboard.vue'),
      meta: { requiresAuth: true, role: 'landlord' }
    },
    {
      path: '/tenant',
      name: 'tenant-dashboard',
      component: () => import('@/views/tenant/TenantDashboard.vue'),
      meta: { requiresAuth: true, role: 'tenant' }
    }
  ]
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.meta.role && authStore.user?.role !== to.meta.role) {
    next('/dashboard')
  } else {
    next()
  }
})

export default router
```

---

### **Step 8: Start Development Server**

```bash
# Start Vue.js dev server
npm run dev

# Server will start at http://localhost:5173
```

---

## 📁 Project Structure

```
muranga-rentals-frontend/
├── public/
│   └── favicon.ico
├── src/
│   ├── assets/
│   │   ├── main.css
│   │   └── logo.png
│   ├── components/
│   │   ├── common/
│   │   │   ├── Navbar.vue
│   │   │   ├── Footer.vue
│   │   │   └── LoadingSpinner.vue
│   │   ├── properties/
│   │   │   ├── PropertyCard.vue
│   │   │   ├── PropertyFilter.vue
│   │   │   └── PropertySearch.vue
│   │   ├── bookings/
│   │   │   └── BookingForm.vue
│   │   └── dashboard/
│   │       ├── StatsCard.vue
│   │       └── RecentActivity.vue
│   ├── views/
│   │   ├── HomeView.vue
│   │   ├── auth/
│   │   │   ├── LoginView.vue
│   │   │   └── RegisterView.vue
│   │   ├── properties/
│   │   │   ├── PropertyListView.vue
│   │   │   └── PropertyDetailView.vue
│   │   ├── dashboard/
│   │   │   └── DashboardView.vue
│   │   ├── landlord/
│   │   │   └── LandlordDashboard.vue
│   │   └── tenant/
│   │       └── TenantDashboard.vue
│   ├── stores/
│   │   ├── auth.js
│   │   ├── properties.js
│   │   └── bookings.js
│   ├── services/
│   │   ├── api.js
│   │   ├── propertyService.js
│   │   └── bookingService.js
│   ├── router/
│   │   └── index.js
│   ├── App.vue
│   └── main.js
├── index.html
├── package.json
├── vite.config.js
└── tailwind.config.js
```

---

## 🎨 Key Features to Build

### **Phase 1: Core Pages**
1. ✅ Home page with hero section
2. ✅ Property listing page
3. ✅ Property details page
4. ✅ Login/Register pages
5. ✅ User dashboard

### **Phase 2: User Features**
1. ⏳ Property search & filters
2. ⏳ Booking system
3. ⏳ Payment integration
4. ⏳ Reviews & ratings
5. ⏳ Messaging system

### **Phase 3: Dashboards**
1. ⏳ Landlord dashboard
2. ⏳ Tenant dashboard
3. ⏳ Admin dashboard
4. ⏳ Analytics & reports

---

## 🔧 Development Commands

```bash
# Start dev server
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview

# Lint code
npm run lint

# Format code
npm run format
```

---

## 🌐 Environment Variables

Create `.env` file:

```env
VITE_API_URL=http://127.0.0.1:8000/api/v1
VITE_APP_NAME=Murang'a Rentals
```

---

## 📱 Responsive Design

The frontend will be fully responsive:
- **Mobile:** 320px - 767px
- **Tablet:** 768px - 1023px
- **Desktop:** 1024px+

---

## 🎯 Next Steps

1. ✅ Complete setup above
2. ⏳ Build core components
3. ⏳ Create views
4. ⏳ Integrate with backend API
5. ⏳ Test all features
6. ⏳ Deploy to production

---

**Ready to build the frontend!** 🚀

**Start with:** `npm run dev` and open http://localhost:5173