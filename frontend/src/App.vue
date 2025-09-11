<template>
  <div id="app">
    <Navbar v-if="isAuthenticated" />

    <div class="app-container">
      <main class="main-content">
        <router-view />
        <div v-if="loading" class="text-center py-12">
          <div class="spinner mx-auto"></div>
          <p class="mt-4 text-gray-600">Loading App...</p>
        </div>

      </main>
    </div>

    <MobileFooter v-if="isAuthenticated" />
  </div>
</template>
<script>
import Navbar from './components/common/Navbar.vue'
import MobileFooter from './components/common/MobileFooter.vue'
import { useAuthStore } from './stores/auth'
import { useLoadingStore } from './stores/loading'

export default {
  name: 'App',
  data() {
    return {
      loading: true,
    }
  },
  components: { Navbar, MobileFooter },
  computed: {
    isAuthenticated() {
      const authStore = useAuthStore()
      return authStore.isAuthenticated
    },
    loading() {
      this.loading = true
     
    }
  }
}
</script>

<style scoped>
.app-container {
  margin-bottom: 60px;
}
</style>
