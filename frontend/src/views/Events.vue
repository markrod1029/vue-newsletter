<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Events</h1>
      <button v-if="userHasPermission" class="btn btn-primary" @click="showCreateEventModal = true">
        Create Event
      </button>
    </div>

    <div class="mb-6">
      <div class="flex gap-2">
        <input class="form-input" placeholder="Search events..." v-model="searchQuery" />
        <select class="form-select" v-model="filterStatus">
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
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
          <div class="card-body">
            <h3 class="text-xl font-semibold mb-2">{{ event.title }}</h3>
            
            <p class="text-gray-600 mb-3">{{ truncateText(event.description, 150) }}</p>
            
            <div class="grid grid-cols-2 gap-2 text-sm mb-3">
              <div>
                <strong>Location:</strong> {{ event.location }}
              </div>
              <div>
                <strong>Starts:</strong> {{ formatDateTime(event.start_at) }}
              </div>
              <div>
                <strong>Ends:</strong> {{ formatDateTime(event.end_at) }}
              </div>
              <div>
                <strong>Status:</strong> 
                <span :class="`badge ${getStatusColor(event.status)}`">{{ event.status }}</span>
              </div>
            </div>
            
            <div class="text-sm text-gray-500 mb-3">
              <span>By {{ event.user?.name }}</span> • 
              <span>{{ formatDate(event.created_at) }}</span>
            </div>
            
            <div class="flex gap-2">
              <button class="btn btn-primary btn-sm" @click="$router.push(`/events/${event.id}`)">
                View Details
              </button>
              
              <button 
                v-if="canEditEvent(event)" 
                class="btn btn-secondary btn-sm" 
                @click="editEvent(event.id)"
              >
                Edit
              </button>
              
              <button 
                v-if="canDeleteEvent(event)" 
                class="btn btn-danger btn-sm" 
                @click="handleDeleteEvent(event.id)"
              >
                Delete
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

    <!-- Create Event Modal -->
    <div v-if="showCreateEventModal" class="modal-overlay">
      <div class="modal">
        <div class="modal-header">
          <h3 class="modal-title">Create New Event</h3>
          <button class="modal-close" @click="showCreateEventModal = false">×</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="createEvent">
            <div class="form-group">
              <label class="form-label">Title</label>
              <input class="form-input" v-model="newEvent.title" required />
            </div>
            <div class="form-group">
              <label class="form-label">Description</label>
              <textarea class="form-textarea" v-model="newEvent.description" rows="3" required></textarea>
            </div>
            <div class="form-group">
              <label class="form-label">Location</label>
              <input class="form-input" v-model="newEvent.location" required />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="form-group">
                <label class="form-label">Start Date & Time</label>
                <input type="datetime-local" class="form-input" v-model="newEvent.start_at" required />
              </div>
              <div class="form-group">
                <label class="form-label">End Date & Time</label>
                <input type="datetime-local" class="form-input" v-model="newEvent.end_at" required />
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showCreateEventModal = false">Cancel</button>
          <button class="btn btn-primary" @click="createEvent">Create</button>
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
      filterStatus: '',
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 9,
        total: 0
      },
      showCreateEventModal: false,
      newEvent: {
        title: '',
        description: '',
        location: '',
        start_at: '',
        end_at: ''
      },
      eventErrors: {}
    }
  },
  computed: {
    userHasPermission() {
      const authStore = useAuthStore()
      return authStore.user && (
        authStore.user.roles.some(role => role.name === 'admin') || 
        authStore.user.roles.some(role => role.name === 'student')
      )
    },
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
          status: this.filterStatus
        })

        const response = await apiClient.get(`/api/events?${params}`)
        this.events = response.data.data
        this.pagination = {
          current_page: response.data.meta.current_page,
          last_page: response.data.meta.last_page,
          per_page: response.data.meta.per_page,
          total: response.data.meta.total
        }
      } catch (error) {
        console.error('Error fetching events:', error)
      } finally {
        this.loading = false
      }
    },
    handlePageChange(page) {
      this.fetchEvents(page)
    },
    async createEvent() {
      try {
        const response = await apiClient.post('/api/events', this.newEvent)
        this.events.unshift(response.data.data)
        this.showCreateEventModal = false
        this.newEvent = { title: '', description: '', location: '', start_at: '', end_at: '' }
        this.eventErrors = {}
        alert('Event created successfully')
      } catch (error) {
        if (error.response?.data?.errors) {
          this.eventErrors = error.response.data.errors
        }
        alert('Failed to create event')
      }
    },
    async handleDeleteEvent(eventId) {
      if (!confirm('Are you sure you want to delete this event?')) return
      
      try {
        await apiClient.delete(`/api/events/${eventId}`)
        this.events = this.events.filter(event => event.id !== eventId)
        alert('Event deleted successfully')
      } catch (error) {
        console.error('Error deleting event:', error)
        alert('Failed to delete event')
      }
    },
    canEditEvent(event) {
      return this.currentUser && (
        this.currentUser.id === event.user_id || 
        this.currentUser.roles.some(role => role.name === 'admin')
      )
    },
    canDeleteEvent(event) {
      return this.currentUser && (
        this.currentUser.id === event.user_id || 
        this.currentUser.roles.some(role => role.name === 'admin')
      )
    },
    editEvent(eventId) {
      alert('Event editing feature coming soon')
    },
    truncateText(text, length) {
      return text.length > length ? text.substring(0, length) + '...' : text
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString()
    },
    formatDateTime(dateString) {
      return new Date(dateString).toLocaleString()
    },
    getStatusColor(status) {
      const colors = {
        pending: 'badge-warning',
        approved: 'badge-success',
        rejected: 'badge-danger',
        archived: 'badge-dark'
      }
      return colors[status] || 'badge-secondary'
    }
  }
}
</script>