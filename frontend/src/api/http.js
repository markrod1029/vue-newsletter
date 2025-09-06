import axios from 'axios'

// Create axios instance with custom config
const apiClient = axios.create({
  baseURL: 'http://localhost:8000',
  withCredentials: true,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  }
})

// Request interceptor to add auth token
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    console.log('🌐 API Request:', config.url)
    console.log('🔑 Token exists:', !!token)
    
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
      console.log('✅ Token added to headers:', config.headers.Authorization)
    } else {
      console.log('❌ No token found in localStorage')
    }
    
    return config
  },
  (error) => {
    console.error('❌ Request error:', error)
    return Promise.reject(error)
  }
)

// Response interceptor to handle errors
apiClient.interceptors.response.use(
  (response) => {
    console.log('✅ API Response success:', response.config.url)
    return response
  },
  (error) => {
    console.error('❌ API Response error:', error.response?.status, error.response?.data)
    
    if (error.response?.status === 401) {
      console.log('🛑 401 Unauthorized - Clearing auth data')
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
      localStorage.removeItem('auth_authenticated')
      
      if (window.location.pathname !== '/login') {
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

export default apiClient