import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    isAuthenticated: false
  }),
  
  actions: {
    async login(credentials) {
      try {
        // Get CSRF cookie first
        await axios.get('/sanctum/csrf-cookie')
        
        // Attempt login
        const response = await axios.post('/api/login', credentials)
        
        // Get user data
        const userResponse = await axios.get('/user')
        this.user = userResponse.data
        this.isAuthenticated = true
        
        return { success: true }
      } catch (error) {
        return { 
          success: false, 
          message: error.response?.data?.message || 'Login failed' 
        }
      }
    },
    
    async register(userData) {
      try {
        // Get CSRF cookie first
        await axios.get('/sanctum/csrf-cookie')
        
        // Attempt registration
        const response = await axios.post('/register', userData)
        return { success: true, data: response.data }
      } catch (error) {
        return { 
          success: false, 
          message: error.response?.data?.message || 'Registration failed',
          errors: error.response?.data?.errors 
        }
      }
    },
    
    async logout() {
      try {
        await axios.post('/logout')
        this.user = null
        this.isAuthenticated = false
        return { success: true }
      } catch (error) {
        return { success: false }
      }
    },
    
    async fetchUser() {
      try {
        const response = await axios.get('/user')
        this.user = response.data
        this.isAuthenticated = true
        return { success: true }
      } catch (error) {
        this.user = null
        this.isAuthenticated = false
        return { success: false }
      }
    }
  }
})