<template>
  <div id="app">
    <Navbar v-if="isAuthenticated" />
    <div class="app-container">
      <!-- <Sidebar v-if="isAuthenticated" /> -->
      <main class="main-content">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script>
import Navbar from './components/common/Navbar.vue'
// import Sidebar from './components/common/Sidebar.vue'
import { useAuthStore } from './stores/auth'

export default {
  name: 'App',
  components: {
    Navbar,
    // Sidebar
  },
  computed: {
    isAuthenticated() {
      const authStore = useAuthStore()
      return authStore.isAuthenticated
    }
  }
}
</script>

<style>
#app {
  min-height: 100vh;
  background-color: #f8f9fa;
}

.app-container {
  display: flex;
  min-height: calc(100vh - 64px);
}

.main-content {
  flex: 1;
  padding: 1rem;
}

@media (min-width: 768px) {
  .main-content {
    padding: 1.5rem 2rem;
  }
}
</style>