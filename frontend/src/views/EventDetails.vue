<template>
  <div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="spinner mx-auto"></div>
      <p class="mt-4 text-gray-600">Loading event...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-12">
      <div class="text-red-600 mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
      </div>
      <h2 class="text-xl font-semibold text-gray-800 mb-2">Event Not Found</h2>
      <p class="text-gray-600 mb-4">{{ error }}</p>
      <button @click="$router.push('/events')" class="btn btn-primary">Back to Events</button>
    </div>

    <!-- Event Content -->
    <article v-else-if="event" class="bg-white">
      <!-- Event Image -->
      <div v-if="event.image_url" class="w-full mb-8">
        <img 
          :src="getMediaUrl(event.image_url)" 
          :alt="event.title" 
          class="w-full h-64 md:h-96 object-cover rounded-lg"
          @error="handleImageError"
        >
      </div>

      <!-- Event Header -->
      <header class="mb-8">
        <!-- Title -->
        <h1 class="text-2xl md:text-5xl font-bold text-gray-900 mb-4 leading-tight">
          {{ event.title }}
        </h1>

        <!-- Meta Information -->
        <div class="flex items-center text-gray-600 mb-6">
          <!-- Author Avatar -->
          <div v-if="event.user?.avatar" class="mr-3">
            <img 
              :src="getMediaUrl(event.user.avatar)" 
              :alt="event.user.name" 
              class="w-10 h-10 rounded-full object-cover"
            >
          </div>
          <div class="text-sm">
            <span class="font-semibold text-gray-800">{{ event.user?.name }}</span>
            <span class="mx-2">•</span>
            <time :datetime="event.created_at">{{ formatDate(event.created_at) }}</time>
          </div>
        </div>

        <!-- Event Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-6 rounded-lg">
          <div class="flex items-center">
            <svg class="w-6 h-6 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <div>
              <h3 class="font-semibold text-gray-800">Location</h3>
              <p class="text-gray-600">{{ event.location }}</p>
            </div>
          </div>

          <div class="flex items-center">
            <svg class="w-6 h-6 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <div>
              <h3 class="font-semibold text-gray-800">Start Date</h3>
              <p class="text-gray-600">{{ formatDateTime(event.start_at) }}</p>
            </div>
          </div>

          <div class="flex items-center">
            <svg class="w-6 h-6 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
              <h3 class="font-semibold text-gray-800">End Date</h3>
              <p class="text-gray-600">{{ formatDateTime(event.end_at) }}</p>
            </div>
          </div>

          <div class="flex items-center">
            <svg class="w-6 h-6 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
              <h3 class="font-semibold text-gray-800">Status</h3>
              <p class="text-gray-600 capitalize">{{ event.status }}</p>
            </div>
          </div>
        </div>
      </header>

      <!-- Event Content -->
      <div class="prose prose-lg max-w-none mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">About This Event</h2>
        <div class="text-gray-700 leading-8 whitespace-pre-line">
          {{ event.description }}
        </div>
      </div>

      <!-- Event Footer -->
      <footer class="mt-12 pt-8 border-t border-gray-100">
        <!-- Author Bio -->
        <div class="bg-gray-50 rounded-lg p-6">
          <div class="flex items-center">
            <div v-if="event.user?.avatar" class="mr-4">
              <img 
                :src="getMediaUrl(event.user.avatar)" 
                :alt="event.user.name" 
                class="w-16 h-16 rounded-full object-cover"
              >
            </div>
            <div>
              <h4 class="font-semibold text-gray-900">Organized by {{ event.user?.name }}</h4>
              <p class="text-gray-600 text-sm mt-1">
                Created on {{ formatDate(event.created_at, 'full') }}
              </p>
            </div>
          </div>
        </div>
      </footer>
    </article>

    <!-- Related Events Section -->
    <section v-if="event && relatedEvents.length > 0" class="mt-16">
      <div class="border-t border-gray-200 pt-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-8">You might also like</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <article 
            v-for="relatedEvent in relatedEvents" 
            :key="relatedEvent.id" 
            class="group cursor-pointer"
            @click="$router.push(`/events/${relatedEvent.id}`)"
          >
            <div class="bg-white rounded-lg overflow-hidden transition-transform group-hover:scale-105">
              <!-- Related Event Image -->
              <div v-if="relatedEvent.image_url" class="w-full h-48 overflow-hidden">
                <img 
                  :src="getMediaUrl(relatedEvent.image_url)" 
                  :alt="relatedEvent.title" 
                  class="w-full h-full object-cover transition-transform group-hover:scale-110"
                />
              </div>
              
              <!-- Related Event Content -->
              <div class="p-4">
                <h3 class="font-semibold text-lg mb-2 line-clamp-2 group-hover:text-blue-600">
                  {{ relatedEvent.title }}
                </h3>
                <p class="text-gray-600 text-sm mb-3 line-clamp-3">
                  {{ truncateText(relatedEvent.description, 100) }}
                </p>
                <div class="flex items-center text-xs text-gray-500">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                  <time :datetime="relatedEvent.start_at">
                    {{ formatDate(relatedEvent.start_at, 'short') }}
                  </time>
                </div>
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
    await this.fetchRelatedEvents()
  },
  methods: {
    async fetchEvent() {
      this.loading = true
      this.error = null
      
      try {
        const response = await apiClient.get(`/api/events/${this.eventId}`)
        this.event = response.data.data
      } catch (error) {
        console.error('Error fetching event:', error)
        if (error.response?.status === 404) {
          this.error = 'The event you are looking for does not exist or has been removed.'
        } else if (error.response?.status === 403) {
          this.error = 'You do not have permission to view this event.'
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
            search: this.event.title.split(' ').slice(0, 3).join(' '),
            approved: true,
            limit: 3
          }
        })
        this.relatedEvents = response.data.data
          .filter(e => e.id !== this.event.id)
          .slice(0, 3)
      } catch (error) {
        console.error('Error fetching related events:', error)
      }
    },

    getMediaUrl(mediaPath) {
      if (!mediaPath) return null;
      
      if (mediaPath.startsWith('http://') || mediaPath.startsWith('https://')) {
        return mediaPath;
      }
      
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

    truncateText(text, length) {
      if (!text) return ''
      const strippedText = text.replace(/<[^>]*>/g, '')
      return strippedText.length > length ? strippedText.substring(0, length) + '...' : strippedText
    },

    formatDate(dateString, format = 'full') {
      if (!dateString) return ''
      
      const date = new Date(dateString);
      
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
      if (!dateString) return ''
      return new Date(dateString).toLocaleString('en-US', {
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
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 500;
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

.btn-primary:hover {
  background-color: #1d4ed8;
  transform: translateY(-1px);
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

.prose {
  max-width: none;
}

.prose p {
  margin-bottom: 1.5rem;
  line-height: 1.8;
  font-size: 1.125rem;
}

.group:hover .group-hover\:scale-105 {
  transform: scale(1.05);
}

.group:hover .group-hover\:scale-110 {
  transform: scale(1.1);
}

.transition-transform {
  transition: transform 0.3s ease;
}
</style>