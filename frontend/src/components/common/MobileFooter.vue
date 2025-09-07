<template>
  <footer class="mobile-footer " >
    <nav class="footer-nav" >
      <!-- Left: Menu -->
      <button class="footer-link" @click="isMenuOpen = true">
        <!-- SVG menu icon -->
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <line x1="3" y1="6" x2="21" y2="6"/>
          <line x1="3" y1="12" x2="21" y2="12"/>
          <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
        <small>Menu</small>
      </button>

      <!-- Center: Search -->
      <router-link to="/news-feed" class="footer-link center-btn">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <circle cx="11" cy="11" r="8"/>
          <line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
      </router-link>

      <!-- Right: Dashboard -->
      <router-link 
        v-if="isStudent" 
        to="/student" 
        class="footer-link" 
        :class="{ active: isCurrentRoute('/student') }"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <rect x="3" y="3" width="7" height="7"/>
          <rect x="14" y="3" width="7" height="7"/>
          <rect x="14" y="14" width="7" height="7"/>
          <rect x="3" y="14" width="7" height="7"/>
        </svg>
        <small>Student</small>
      </router-link>

      <router-link 
        v-if="isAdmin" 
        to="/admin" 
        class="footer-link" 
        :class="{ active: isCurrentRoute('/admin') }"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <circle cx="12" cy="12" r="3"/>
          <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 
                   1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 
                   1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 
                   0-1.82.33l-.06.06a2 2 0 1 
                   1-2.83-2.83l.06-.06a1.65 1.65 0 0 
                   0 .33-1.82 1.65 1.65 0 0 
                   0-1.51-1H3a2 2 0 0 1 0-4h.09a1.65 
                   1.65 0 0 0 1.51-1 1.65 1.65 0 0 
                   0-.33-1.82l-.06-.06a2 2 0 1 
                   1 2.83-2.83l.06.06a1.65 1.65 
                   0 0 0 1.82.33h.09A1.65 1.65 
                   0 0 0 9 4.6V4a2 2 0 1 
                   1 4 0v.09a1.65 1.65 0 0 
                   0 1 1.51h.09a1.65 1.65 
                   0 0 0 1.82-.33l.06-.06a2 2 
                   0 1 1 2.83 2.83l-.06.06a1.65 
                   1.65 0 0 0-.33 1.82v.09c.36.68 
                   1.05 1.14 1.82 1.14H21a2 2 0 0 1 0 4h-.09c-.77 0-1.46.46-1.51 1z"/>
        </svg>
        <small>Admin</small>
      </router-link>
    </nav>

    <!-- Modal Menu -->
    <div v-if="isMenuOpen" class="menu-modal" @click.self="isMenuOpen = false">
      <div class="menu-content">
        <router-link to="/" class="menu-item" @click="isMenuOpen = false">Home</router-link>
        <router-link to="/feed" class="menu-item" @click="isMenuOpen = false">Posts</router-link>
        <router-link to="/events" class="menu-item" @click="isMenuOpen = false">Events</router-link>
      </div>
    </div>
  </footer>
</template>

<script>
import { useAuthStore } from '../../stores/auth'
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'

export default {
  name: "MobileFooter",
  setup() {
    const authStore = useAuthStore()
    const router = useRouter()
    const isMenuOpen = ref(false)

    const isAdmin = computed(() =>
      authStore.user?.roles?.some(r => r.name === "admin")
    )
    const isStudent = computed(() =>
      authStore.user?.roles?.some(r => r.name === "student")
    )

    const isCurrentRoute = (route) => router.currentRoute.value.path === route

    return { isAdmin, isStudent, isCurrentRoute, isMenuOpen }
  }
}
</script>

<style scoped>
.mobile-footer {
  display: none;
}

@media (max-width: 768px) {
  .mobile-footer {
    display: block;
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    background: #fff;
    box-shadow: 0 -1px 5px rgba(0,0,0,0.1);
    z-index: 1100;
  }

  .footer-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 60px;
    padding: 0 1rem;
  }

  .footer-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #666;
    text-decoration: none;
    font-size: 0.7rem;
    background: none;
    border: none;
  }

  .footer-link.active {
    color: #007bff;
  }

  .icon {
    width: 24px;
    height: 24px;
    stroke-width: 2;
  }

  .center-btn {
    background: #007bff;
    color: white;
    padding: 14px;
    border-radius: 50%;
    margin-top: -30px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
  }

  .menu-modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: flex-end;
    z-index: 1200;
  }

  .menu-content {
    background: #fff;
    width: 100%;
    padding: 1rem;
    border-radius: 12px 12px 0 0;
  }

  .menu-item {
    display: block;
    padding: 1rem;
    text-align: center;
    color: #333;
    font-size: 1rem;
    text-decoration: none;
    border-bottom: 1px solid #eee;
  }

  .menu-item:last-child {
    border-bottom: none;
  }

  .menu-item:hover {
    background: #f9f9f9;
  }
}
</style>
