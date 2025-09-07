<template>
  <div>
    <!-- Welcome Section -->
    <div class="card mb-6">
      <h1 class="text-2xl font-bold text-gray-900 mb-4">Welcome to Campus Newsletter</h1>
      <p class="text-gray-600">Stay updated with the latest campus news, events, and discussions.</p>
    </div>

    <!-- Feature Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <!-- News Card -->
      <div class="card cursor-pointer hover:shadow-lg transition-shadow" @click="$router.push('/feed')">
        <div class="card-body text-center">
          <div class="icon-primary mb-3">📰</div>
          <h3 class="text-lg font-semibold mb-2">News & Articles</h3>
          <p class="text-gray-600">Read the latest campus news and articles</p>
        </div>
      </div>

      <!-- Events Card -->
      <div class="card cursor-pointer hover:shadow-lg transition-shadow" @click="$router.push('/events')">
        <div class="card-body text-center">
          <div class="icon-success mb-3">📅</div>
          <h3 class="text-lg font-semibold mb-2">Events</h3>
          <p class="text-gray-600">Discover upcoming campus events</p>
        </div>
      </div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- <div class="card">
        <div class="card-header">
          <h2 class="text-xl font-semibold">Recent News</h2>
        </div>
        <div class="card-body">
          <ul class="list-group">
            <li v-for="post in recentPosts" :key="post.id" class="list-group-item">
              <div class="d-flex justify-between align-items-center">
                <div>
                  <h5 class="mb-1 text-sm font-medium">{{ post.title }}</h5>
                  <small class="text-muted">{{ formatDate(post.created_at) }}</small>
                </div>
                <span :class="`badge ${getStatusColor(post.status)}`">{{ post.status }}</span>
              </div>
            </li>
          </ul>
          <button class="btn btn-primary mt-3" @click="$router.push('/feed')">View All News</button>
        </div>
      </div> -->

      <!-- <div class="card">
        <div class="card-header">
          <h2 class="text-xl font-semibold">Upcoming Events</h2>
        </div>
        <div class="card-body">
          <ul class="list-group">
            <li v-for="event in upcomingEvents" :key="event.id" class="list-group-item">
              <div class="d-flex justify-between align-items-center">
                <div>
                  <h5 class="mb-1 text-sm font-medium">{{ event.title }}</h5>
                  <small class="text-muted">{{ formatDate(event.start_at) }}</small>
                </div>
                <span :class="`badge ${getStatusColor(event.status)}`">{{ event.status }}</span>
              </div>
            </li>
          </ul>
          <button class="btn btn-success mt-3" @click="$router.push('/events')">View All Events</button>
        </div>
      </div> -->
    </div>

   
  </div>
</template>

<script>
import apiClient from '../api/http'

export default {
  name: 'Home',
  data() {
    return {
      // recentPosts: [],
      // upcomingEvents: [],
      loading: false
    }
  },
  async mounted() {
    // await this.fetchRecentPosts()
    // await this.fetchUpcomingEvents()
  },
  methods: {
    // async fetchRecentPosts() {
    //   this.loading = true
    //   try {
    //     const response = await apiClient.get('/api/feed?limit=5')
    //     this.recentPosts = response.data.data
    //   } catch (error) {
    //     console.error('Error fetching recent posts:', error)
    //     this.recentPosts = [
    //       { id: 1, title: 'Welcome to Campus Newsletter', created_at: new Date().toISOString(), status: 'approved' },
    //       { id: 2, title: 'New Semester Guidelines', created_at: new Date().toISOString(), status: 'approved' }
    //     ]
    //   } finally {
    //     this.loading = false
    //   }
    // },

    // async fetchUpcomingEvents() {
    //   this.loading = true
    //   try {
    //     const response = await apiClient.get('/api/events/upcoming?limit=5')
    //     this.upcomingEvents = response.data.data
    //   } catch (error) {
    //     console.error('Error fetching upcoming events:', error)
    //     this.upcomingEvents = [
    //       { id: 1, title: 'Orientation Day', start_at: new Date().toISOString(), status: 'approved' },
    //       { id: 2, title: 'Sports Festival', start_at: new Date(Date.now() + 86400000).toISOString(), status: 'approved' }
    //     ]
    //   } finally {
    //     this.loading = false
    //   }
    // },

    formatDate(dateString) {
      if (!dateString) return 'No date'
      try {
        return new Date(dateString).toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        })
      } catch (error) {
        return 'Invalid date'
      }
    },

    getStatusColor(status) {
      const colors = {
        draft: 'badge-secondary',
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

<style scoped>
/* Card Styles */
.card {
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.card-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  background-color: #f8f9fa;
}

.card-body {
  padding: 1.5rem;
}

/* Icon Styles */
.icon-primary {
  font-size: 3rem;
  color: #007bff;
}

.icon-success {
  font-size: 3rem;
  color: #28a745;
}

.icon-warning {
  font-size: 3rem;
  color: #ffc107;
}

/* List Group Styles */
.list-group {
  list-style: none;
  padding: 0;
  margin: 0;
}

.list-group-item {
  padding: 0.75rem 0;
  border-bottom: 1px solid #e5e7eb;
}

.list-group-item:last-child {
  border-bottom: none;
}

/* Flex Utilities */
.d-flex {
  display: flex;
}

.justify-between {
  justify-content: space-between;
}

.align-items-center {
  align-items: center;
}

/* Text Utilities */
.text-muted {
  color: #6b7280;
}

/* Badge Styles */
.badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  border-radius: 0.25rem;
}

.badge-primary {
  background-color: #007bff;
  color: white;
}

.badge-secondary {
  background-color: #6c757d;
  color: white;
}

.badge-success {
  background-color: #28a745;
  color: white;
}

.badge-danger {
  background-color: #dc3545;
  color: white;
}

.badge-warning {
  background-color: #ffc107;
  color: #212529;
}

.badge-info {
  background-color: #17a2b8;
  color: white;
}

.badge-dark {
  background-color: #343a40;
  color: white;
}

/* Button Styles */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem 1rem;
  border: 1px solid transparent;
  border-radius: 0.375rem;
  font-weight: 500;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.875rem;
  line-height: 1.25rem;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background-color: #007bff;
  color: white;
  border-color: #007bff;
}

.btn-primary:hover:not(:disabled) {
  background-color: #0056b3;
  border-color: #0056b3;
}

.btn-success {
  background-color: #28a745;
  color: white;
  border-color: #28a745;
}

.btn-success:hover:not(:disabled) {
  background-color: #218838;
  border-color: #218838;
}

/* Grid Styles */
.grid {
  display: grid;
}

.grid-cols-1 {
  grid-template-columns: repeat(1, minmax(0, 1fr));
}

.gap-6 {
  gap: 1.5rem;
}

/* Spacing Utilities */
.mb-4 {
  margin-bottom: 1rem;
}

.mb-6 {
  margin-bottom: 1.5rem;
}

.mt-3 {
  margin-top: 0.75rem;
}

.mb-1 {
  margin-bottom: 0.25rem;
}

.mb-2 {
  margin-bottom: 0.5rem;
}

.mb-3 {
  margin-bottom: 0.75rem;
}

/* Text Styles */
.text-2xl {
  font-size: 1.5rem;
}

.text-xl {
  font-size: 1.25rem;
}

.text-lg {
  font-size: 1.125rem;
}

.font-bold {
  font-weight: 700;
}

.font-semibold {
  font-weight: 600;
}

.font-medium {
  font-weight: 500;
}

.text-gray-900 {
  color: #1f2937;
}

.text-gray-600 {
  color: #4b5563;
}

/* Cursor and Transition */
.cursor-pointer {
  cursor: pointer;
}

.transition-shadow {
  transition: box-shadow 0.2s ease-in-out;
}

.hover\:shadow-lg:hover {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Loading Spinner */
.spinner-large {
  width: 3rem;
  height: 3rem;
  border: 3px solid #f3f3f3;
  border-top: 3px solid #007bff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Responsive Design */
@media (min-width: 768px) {
  .md\:grid-cols-3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
  
  .md\:grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

/* Fixed positioning for loading overlay */
.fixed {
  position: fixed;
}

.inset-0 {
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
}

.bg-black {
  background-color: #000;
}

.bg-opacity-50 {
  --tw-bg-opacity: 0.5;
}

.flex {
  display: flex;
}

.items-center {
  align-items: center;
}

.justify-center {
  justify-content: center;
}

.z-50 {
  z-index: 50;
}
</style>