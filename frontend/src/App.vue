<template>
  <div id="app">
    <Navbar v-if="isAuthenticated" />

    <div class="app-container">
      <main class="main-content">
        <router-view />
        <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div class="spinner-large"></div>
        </div>
      </main>
    </div>

    <MobileFooter style="margin-top: 100px;" v-if="isAuthenticated" />
  </div>
</template>
<script>
import Navbar from './components/common/Navbar.vue'
import MobileFooter from './components/common/MobileFooter.vue'
import { useAuthStore } from './stores/auth'
import { useLoadingStore } from './stores/loading'

export default {
  name: 'App',
  components: { Navbar, MobileFooter },
  computed: {
    isAuthenticated() {
      const authStore = useAuthStore()
      return authStore.isAuthenticated
    },
    loading() {
      const loadingStore = useLoadingStore()
      return loadingStore.isLoading
    }
  }
}
</script>
