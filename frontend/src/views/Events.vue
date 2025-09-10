<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Events</h1>
    </div>

    <div class="mb-6">
      <div class="flex gap-2">
        <input class="form-input" placeholder="Search events..." v-model="searchQuery" />
        <button class="btn btn-primary" @click="fetchEvents">Search</button>
      </div>
    </div>

    <div v-if="loading" class="text-center py-8">
      <div class="spinner"></div>
    </div>

    <div v-else>
      <div v-if="events.length === 0" class="text-center py-8">
        <p class="text-gray-500">No events found.</p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="event in events" :key="event.id" class="card hover:shadow-lg transition-shadow">
          <!-- Event Image -->
          <div v-if="event.image_url" class="w-full h-48 overflow-hidden">
            <img 
              :src="getMediaUrl(event.image_url)" 
              :alt="event.title" 
              class="w-full h-full object-cover"
              @error="handleImageError"
            />
          </div>
          
          <div class="card-body">
            <h3 class="text-xl font-semibold mb-2">{{ event.title }}</h3>
            
            <p class="text-gray-600 mb-3">{{ truncateText(event.description, 100) }}</p>
            
            <div class="space-y-2 text-sm mb-3">
              <div class="flex items-center">
                <svg class="w-4 h-4 mr-2 text-gray-500 icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span>{{ event.location }}</span>
              </div>
              
              <div class="flex items-center">
                <svg class="w-4 h-4 mr-2 text-gray-500 icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span>{{ formatDateTime(event.start_at) }}</span>
              </div>
            </div>
            
            <div class="text-sm text-gray-500 mb-3">
              <span>By {{ event.user?.name }}</span>
            </div>
            
            <div class="flex gap-2">
              <button class="btn btn-primary btn-sm flex-1" @click="$router.push(`/event/view/${event.id}`)">
                Read More
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-6 flex justify-center" v-if="pagination.last_page > 1">
        <div class="pagination">
          <button 
            v-for="page in pagination.last_page" 
            :key="page"
            class="pagination-item"
            :class="{ 'pagination-item-active': page === pagination.current_page }"
            @click="handlePageChange(page)"
          >
            {{ page }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from '../stores/auth'
import apiClient from '../api/http'

export default {
  name: 'Events',
  data() {
    return {
      events: [],
      loading: false,
      searchQuery: '',
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 9,
        total: 0
      },
      brokenImages: new Set()
    }
  },
  computed: {
    currentUser() {
      const authStore = useAuthStore()
      return authStore.user
    }
  },
  async mounted() {
    await this.fetchEvents()
  },
  methods: {
    async fetchEvents(page = 1) {
      this.loading = true
      try {
        const params = new URLSearchParams({
          page: page,
          search: this.searchQuery,
          approved: true // Only show approved events
        })

        const response = await apiClient.get(`/api/events?${params}`)
        this.events = response.data.data
        this.pagination = {
          current_page: response.data.current_page,
          last_page: response.data.last_page,
          per_page: response.data.per_page,
          total: response.data.total
        }
      } catch (error) {
        console.error('Error fetching events:', error)
      } finally {
        this.loading = false
      }
    },
    
    getMediaUrl(mediaPath) {
      if (!mediaPath) return null;
      
      // If it's already a full URL, return as is
      if (mediaPath.startsWith('http://') || mediaPath.startsWith('https://')) {
        return mediaPath;
      }
      
      // If it's a storage path, prepend the base URL
      if (mediaPath.startsWith('/storage/')) {
        const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000';
        return `${baseUrl}${mediaPath}`;
      }
      
      return mediaPath;
    },
    
    handleImageError(event) {
      const imgSrc = event.target.src;
      this.brokenImages.add(imgSrc);
      event.target.style.display = 'none';
    },
    
    handlePageChange(page) {
      this.fetchEvents(page)
    },
    
    truncateText(text, length) {
      if (!text) return ''
      const strippedText = text.replace(/<[^>]*>/g, '')
      return strippedText.length > length ? strippedText.substring(0, length) + '...' : strippedText
    },
    
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString()
    },
    
    formatDateTime(dateString) {
      return new Date(dateString).toLocaleString()
    }
  }
}
</script>

<style scoped>
.pagination {
  display: flex;
  gap: 0.5rem;
}

.pagination-item {
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  background: white;
  cursor: pointer;
}

.pagination-item:hover {
  background-color: #f9fafb;
}

.pagination-item-active {
  background-color: #007bff;
  color: white;
  border-color: #007bff;
}

.spinner {
  display: inline-block;
  width: 2rem;
  height: 2rem;
  border: 2px solid #f3f3f3;
  border-top: 2px solid #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-weight: 500;
  transition: all 0.2s;
  border: none;
  cursor: pointer;
}

.btn-primary {
  background-color: #007bff;
  color: white;
}

.btn-primary:hover {
  background-color: #0056b3;
}

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}

.form-input {
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  width: auto;
}

.card {
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.card-body {
  padding: 1.5rem;
}

.icon {
  width: 24px;
  height: 24px;
  stroke-width: 2;
}

.object-cover {
  object-fit: cover;
}
</style>