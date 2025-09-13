<template>
  <div class="max-w-4xl mx-auto px-4 py-6">
    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="spinner mx-auto mb-3"></div>
      <p class="text-gray-600">Loading event details...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-8">
      <div class="text-red-600 mb-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
      </div>
      <h2 class="text-xl font-semibold text-gray-800 mb-2">Unable to Load Event</h2>
      <p class="text-gray-600 mb-4">{{ error }}</p>
      <div class="flex flex-col sm:flex-row justify-center gap-3">
        <button @click="fetchEvent" class="btn btn-primary flex items-center justify-center">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Try Again
        </button>
        <button @click="$router.push('/events')" class="btn btn-secondary">Back to Events</button>
      </div>
    </div>

    <!-- Event Content -->
    <article v-else-if="event" class="bg-white rounded-lg shadow-md overflow-hidden">
      <!-- Event Image Banner -->
      <div v-if="event.image_url && !brokenImages.has(getMediaUrl(event.image_url))" class="w-full">
        <img 
          :src="getMediaUrl(event.image_url)" 
          :alt="event.title" 
          class="w-full h-48 object-cover"
          @error="handleImageError"
        >
      </div>

      <!-- Event Content Container -->
      <div class="p-5 md:p-6">
        <!-- Event Header -->
        <header class="mb-6">
          <!-- Title -->
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3 leading-tight">
            {{ event.title }}
          </h1>

          <!-- Meta Information -->
          <div class="flex items-center text-gray-600 mb-4">
            <!-- Author Avatar -->
            <div v-if="event.user?.avatar && !brokenImages.has(getMediaUrl(event.user.avatar))" class="mr-2">
              <img 
                :src="getMediaUrl(event.user.avatar)" 
                :alt="event.user.name" 
                class="w-8 h-8 rounded-full object-cover"
                @error="handleImageError"
              >
            </div>
            <div v-else class="user-avatar-small mr-2">
              {{ getUserInitials(event.user?.name) }}
            </div>
            <div class="text-xs">
              <span class="font-semibold text-gray-800">{{ event.user?.name || 'Unknown Organizer' }}</span>
              <span class="mx-1">•</span>
              <time :datetime="event.created_at">{{ formatDate(event.created_at) }}</time>
            </div>
          </div>

          <!-- Event Details -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3 bg-blue-50 p-4 rounded-lg">
            <div class="flex items-center">
              <svg class="w-4 h-4 md:w-5 md:h-5 mr-2 text-blue-600 icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
              <div>
                <h3 class="font-semibold text-gray-800 text-xs md:text-sm">Location</h3>
                <p class="text-gray-600 text-xs">{{ event.location || 'Not specified' }}</p>
              </div>
            </div>

            <div class="flex items-center">
              <svg class="w-4 h-4 md:w-5 md:h-5 mr-2 text-blue-600 icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
              <div>
                <h3 class="font-semibold text-gray-800 text-xs md:text-sm">Start Date</h3>
                <p class="text-gray-600 text-xs">{{ formatDateTime(event.start_at) }}</p>
              </div>
            </div>

            <div class="flex items-center">
              <svg class="w-4 h-4 md:w-5 md:h-5 mr-2 text-blue-600 icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 01118 0z"></path>
              </svg>
              <div>
                <h3 class="font-semibold text-gray-800 text-xs md:text-sm">End Date</h3>
                <p class="text-gray-600 text-xs">{{ formatDateTime(event.end_at) }}</p>
              </div>
            </div>

            <div class="flex items-center">
              <svg class="w-4 h-4 md:w-5 md:h-5 mr-2 text-blue-600 icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <div>
                <h3 class="font-semibold text-gray-800 text-xs md:text-sm">Status</h3>
                <p class="text-gray-600 text-xs capitalize">{{ event.status || 'unknown' }}</p>
              </div>
            </div>
          </div>
        </header>

        <!-- Event Content -->
        <div class="mb-6">
          <h2 class="text-xl font-bold text-gray-900 mb-3">About This Event</h2>
          <div class="text-gray-700 leading-6 whitespace-pre-line text-sm">
            {{ event.description || 'No description available.' }}
          </div>
        </div>

        <!-- Event Footer -->
        <footer class="pt-4 border-t border-gray-200">
          <!-- Author Bio -->
          <div class="bg-gray-50 rounded-lg p-3">
            <div class="flex items-center">
              <div v-if="event.user?.avatar && !brokenImages.has(getMediaUrl(event.user.avatar))" class="mr-3">
                <img 
                  :src="getMediaUrl(event.user.avatar)" 
                  :alt="event.user.name" 
                  class="w-10 h-10 rounded-full object-cover"
                  @error="handleImageError"
                >
              </div>
              <div v-else class="user-avatar-medium mr-3">
                {{ getUserInitials(event.user?.name) }}
              </div>
              <div>
                <h4 class="font-semibold text-gray-900 text-sm">Organized by {{ event.user?.name || 'Unknown Organizer' }}</h4>
                <p class="text-gray-600 text-xs mt-1">
                  Created on {{ formatDate(event.created_at, 'full') }}
                </p>
              </div>
            </div>
          </div>
        </footer>
      </div>
    </article>

    <!-- Related Events Section -->
    <section v-if="event && relatedEvents.length > 0" class="mt-8">
      <div class="border-t border-gray-200 pt-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">You might also like</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
          <article 
            v-for="relatedEvent in relatedEvents" 
            :key="relatedEvent.id" 
            class="group cursor-pointer bg-white rounded-lg shadow-sm overflow-hidden transition-all duration-200 hover:shadow-md"
            @click="goToEvent(relatedEvent.id)"
          >
            <div class="h-32 overflow-hidden">
              <img 
                v-if="relatedEvent.image_url && !brokenImages.has(getMediaUrl(relatedEvent.image_url))"
                :src="getMediaUrl(relatedEvent.image_url)" 
                :alt="relatedEvent.title" 
                class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                @error="handleImageError"
              />
              <div v-else class="w-full h-full bg-gray-100 flex items-center justify-center">
                <span class="text-gray-400 text-xs">No image</span>
              </div>
            </div>
            
            <div class="p-3">
              <h3 class="font-semibold text-gray-800 text-sm mb-1 line-clamp-2 group-hover:text-blue-600 transition-colors">
                {{ relatedEvent.title }}
              </h3>
              <p class="text-gray-600 text-xs mb-2 line-clamp-2">
                {{ truncateText(relatedEvent.description, 80) }}
              </p>
              <div class="flex items-center text-xs text-gray-500">
                <svg class="w-3 h-3 mr-1 icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <time :datetime="relatedEvent.start_at">
                  {{ formatDate(relatedEvent.start_at, 'short') }}
                </time>
              </div>
            </div>
          </article>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import { useAuthStore } from '../stores/auth'
import apiClient from '../api/http'

export default {
  name: 'EventDetail',
  data() {
    return {
      event: null,
      relatedEvents: [],
      loading: true,
      error: null,
      brokenImages: new Set()
    }
  },
  computed: {
    currentUser() {
      const authStore = useAuthStore()
      return authStore.user
    },
    eventId() {
      return this.$route.params.id
    }
  },
  async mounted() {
    await this.fetchEvent()
    if (this.event) {
      await this.fetchRelatedEvents()
    }
  },
  methods: {
    async fetchEvent() {
      this.loading = true
      this.error = null
      
      try {
        const response = await apiClient.get(`/api/events/${this.eventId}`)
        this.event = response.data.data || response.data
      } catch (error) {
        console.error('Error fetching event:', error)
        
        // Handle CORS and network errors specifically
        if (error.code === 'ERR_NETWORK' || error.message === 'Network Error') {
          this.error = 'Cannot connect to the server. Please check your internet connection and try again.'
        } else if (error.response?.status === 404) {
          this.error = 'The event you are looking for does not exist or has been removed.'
        } else if (error.response?.status === 403) {
          this.error = 'You do not have permission to view this event.'
        } else if (error.response?.status === 500) {
          this.error = 'Server error. Please try again later.'
        } else {
          this.error = 'Failed to load event. Please try again later.'
        }
      } finally {
        this.loading = false
      }
    },

    async fetchRelatedEvents() {
      if (!this.event) return
      
      try {
        const response = await apiClient.get('/api/events', {
          params: {
            exclude: this.eventId,
            limit: 3,
            approved: true
          }
        })
        
        // Handle different response structures
        const eventsData = response.data.data || response.data || []
        this.relatedEvents = eventsData
          .filter(e => e.id !== this.event.id)
          .slice(0, 3)
      } catch (error) {
        console.error('Error fetching related events:', error)
        // Silently fail for related events as they're not critical
      }
    },

    goToEvent(eventId) {
      // Use router push to navigate to the event detail page
      this.$router.push(`/event/view/${eventId}`)
        .then(() => {
          // Refresh the page to load the new event data
          window.location.reload()
        })
        .catch(err => {
          console.error('Navigation error:', err)
        })
    },

    getMediaUrl(mediaPath) {
      if (!mediaPath) return '';
      
      if (mediaPath.startsWith('http://') || mediaPath.startsWith('https://') || mediaPath.startsWith('data:')) {
        return mediaPath;
      }
      
      if (mediaPath.startsWith('/storage/')) {
        const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000';
        return `${baseUrl}${mediaPath}`;
      }
      
      if (mediaPath.startsWith('storage/')) {
        const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000';
        return `${baseUrl}/${mediaPath}`;
      }
      
      return mediaPath;
    },

    handleImageError(event) {
      const imgSrc = event.target.src;
      this.brokenImages.add(imgSrc);
      event.target.style.display = 'none';
    },

    getUserInitials(name) {
      if (!name) return 'U';
      return name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
    },

    truncateText(text, length) {
      if (!text) return '';
      const strippedText = text.replace(/<[^>]*>/g, '');
      return strippedText.length > length ? strippedText.substring(0, length) + '...' : strippedText;
    },

    formatDate(dateString, format = 'full') {
      if (!dateString) return '';
      
      const date = new Date(dateString);
      
      if (isNaN(date.getTime())) {
        return 'Invalid date';
      }
      
      if (format === 'year') {
        return date.getFullYear().toString();
      }
      
      if (format === 'short') {
        return date.toLocaleDateString('en-US', {
          month: 'short',
          day: 'numeric'
        });
      }
      
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    },

    formatDateTime(dateString) {
      if (!dateString) return '';
      
      const date = new Date(dateString);
      if (isNaN(date.getTime())) {
        return 'Invalid date';
      }
      
      return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    }
  }
}
</script>

<style scoped>
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
  font-size: 0.875rem;
  transition: all 0.2s;
  border: none;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-primary {
  background-color: #2563eb;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background-color: #1d4ed8;
  transform: translateY(-1px);
}

.btn-secondary {
  background-color: #6b7280;
  color: white;
}

.btn-secondary:hover:not(:disabled) {
  background-color: #4b5563;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.user-avatar-small {
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  background-color: #3b82f6;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.75rem;
}

.user-avatar-medium {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  background-color: #3b82f6;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.875rem;
}

.group:hover .group-hover\:scale-105 {
  transform: scale(1.05);
}

.transition-transform {
  transition: transform 0.3s ease;
}

.icon {
  width: 20px;
  height: 20px;
  stroke-width: 1.5;
}
</style>