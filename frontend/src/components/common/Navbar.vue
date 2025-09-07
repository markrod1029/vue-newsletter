<template>
  <header class="navbar">
    <div class="navbar-brand">
      <router-link to="/" class="navbar-logo">
        Campus Newsletter
      </router-link>
    </div>

    <nav class="navbar-nav">
      <router-link 
        v-for="item in navigation" 
        :key="item.name" 
        :to="item.to" 
        class="nav-link"
        :class="{ 'nav-link-active': isCurrentRoute(item.to) }"
      >
        {{ item.name }}
      </router-link>
    </nav>

    <div class="navbar-user">
      <div class="user-menu">
        <button 
          @click="isProfileMenuOpen = !isProfileMenuOpen" 
          class="user-button"
        >
          <div class="user-avatar">
            {{ userInitials }}
          </div>
        </button>

        <div v-show="isProfileMenuOpen" class="user-dropdown">
          <div class="user-info">
            <p class="user-name">{{ user.name }}</p>
            <p class="user-email">{{ user.email }}</p>
            <p class="user-role">{{ userRole }}</p>
          </div>
          
          <router-link 
            v-if="isAdmin" 
            to="/admin" 
            class="dropdown-item"
            @click="isProfileMenuOpen = false"
          >
            Admin Panel
          </router-link>
          
          <router-link 
            v-if="isStudent" 
            to="/student" 
            class="dropdown-item"
            @click="isProfileMenuOpen = false"
          >
            Student Dashboard
          </router-link>
          
          <button 
            @click="handleLogout" 
            class="dropdown-item"
          >
            Sign out
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<script>
import { useAuthStore } from '../../stores/auth'
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'

export default {
  name: 'Navbar',
  setup() {
    const authStore = useAuthStore()
    const router = useRouter()
    const isProfileMenuOpen = ref(false)

    const navigation = computed(() => {
      return [
        { name: 'Home', to: '/' },
        { name: 'News', to: '/feed' },
        { name: 'Events', to: '/events' },
      ]
    })

    const user = computed(() => authStore.user || {})
    const userInitials = computed(() => {
      if (!user.value.name) return 'U'
      return user.value.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)
    })

    const userRole = computed(() => {
      return user.value.roles?.[0]?.name || 'user'
    })

    const isAdmin = computed(() => {
      return user.value.roles?.some(role => role.name === 'admin')
    })

    const isStudent = computed(() => {
      return user.value.roles?.some(role => role.name === 'student')
    })

    const isCurrentRoute = (route) => {
      return router.currentRoute.value.path === route
    }

    const handleLogout = async () => {
      await authStore.logout()
      router.push('/login')
      isProfileMenuOpen.value = false
    }

    return {
      navigation,
      user,
      userInitials,
      userRole,
      isAdmin,
      isStudent,
      isProfileMenuOpen,
      isCurrentRoute,
      handleLogout
    }
  }
}
</script>

<style scoped>
.navbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 1rem;
  height: 64px;
  background-color: white;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 1000;
}

.navbar-brand {
  flex-shrink: 0;
}

.navbar-logo {
  font-size: 1.25rem;
  font-weight: bold;
  color: #333;
  text-decoration: none;
}

.navbar-nav {
  display: flex;
  gap: 1.5rem;
}

.nav-link {
  color: #666;
  text-decoration: none;
  padding: 0.5rem 0;
  font-weight: 500;
  transition: color 0.2s;
}

.nav-link:hover {
  color: #007bff;
}

.nav-link-active {
  color: #007bff;
  border-bottom: 2px solid #007bff;
}

.navbar-user {
  position: relative;
}

.user-menu {
  position: relative;
}

.user-button {
  border: none;
  background: none;
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 50%;
  transition: background-color 0.2s;
}

.user-button:hover {
  background-color: #f8f9fa;
}

.user-avatar {
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  background-color: #007bff;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.875rem;
}

.user-dropdown {
  position: absolute;
  right: 0;
  top: 100%;
  margin-top: 0.5rem;
  background-color: white;
  border-radius: 0.375rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  min-width: 200px;
  z-index: 1000;
}

.user-info {
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.user-name {
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.user-email {
  color: #6b7280;
  font-size: 0.875rem;
  margin-bottom: 0.25rem;
}

.user-role {
  color: #6b7280;
  font-size: 0.75rem;
  text-transform: capitalize;
}

.dropdown-item {
  display: block;
  width: 100%;
  padding: 0.75rem 1rem;
  text-align: left;
  border: none;
  background: none;
  color: #374151;
  text-decoration: none;
  cursor: pointer;
  transition: background-color 0.2s;
}

.dropdown-item:hover {
  background-color: #f9fafb;
}

@media (max-width: 768px) {
  .navbar-nav {
    display: none;
  }
  
  .navbar {
    padding: 0 0.5rem;
  }
}
</style>