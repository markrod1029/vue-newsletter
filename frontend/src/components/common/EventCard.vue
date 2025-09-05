<template>
  <CCard class="hover:shadow-lg transition-shadow">
    <CCardBody>
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
          <CBadge :color="getStatusColor(event.status)" class="ml-1">{{ event.status }}</CBadge>
        </div>
      </div>
      
      <div class="text-sm text-gray-500 mb-3">
        <span>By {{ event.user?.name }}</span> • 
        <span>{{ formatDate(event.created_at) }}</span>
      </div>
      
      <div class="flex gap-2">
        <CButton color="primary" size="sm" @click="$router.push(`/events/${event.id}`)">
          View Details
        </CButton>
        
        <CButton 
          v-if="canEditEvent" 
          color="secondary" 
          size="sm" 
          @click="editEvent"
        >
          Edit
        </CButton>
        
        <CButton 
          v-if="canDeleteEvent" 
          color="danger" 
          size="sm" 
          @click="$emit('delete', event.id)"
        >
          Delete
        </CButton>
      </div>
    </CCardBody>
  </CCard>
</template>

<script>
import { CCard, CCardBody, CButton, CBadge } from '@coreui/vue'
import { useAuthStore } from '../../stores/auth'

export default {
  name: 'EventCard',
  components: {
    CCard, CCardBody, CButton, CBadge
  },
  props: {
    event: {
      type: Object,
      required: true
    }
  },
  computed: {
    canEditEvent() {
      const authStore = useAuthStore()
      return authStore.user && (
        authStore.user.id === this.event.user_id || 
        authStore.user.roles.some(role => role.name === 'admin')
      )
    },
    canDeleteEvent() {
      const authStore = useAuthStore()
      return authStore.user && (
        authStore.user.id === this.event.user_id || 
        authStore.user.roles.some(role => role.name === 'admin')
      )
    }
  },
  methods: {
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
        pending: 'warning',
        approved: 'success',
        rejected: 'danger',
        archived: 'dark'
      }
      return colors[status] || 'secondary'
    },
    editEvent() {
      // Implement event editing
      this.$toast.info('Event editing feature coming soon')
    }
  }
}
</script>