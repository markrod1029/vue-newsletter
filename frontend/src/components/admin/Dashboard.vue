<template>
  <div>
    <h1 class="text-2xl font-bold mb-8">Admin Dashboard</h1>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-10">
      <div class="card text-center py-6 px-4">
        <div class="text-3xl font-bold text-blue-600 mb-3">{{ stats.pendingStudents }}</div>
        <div class="text-gray-600 mb-4">Pending Students</div>
        <button class="text-blue-600 text-sm" @click="$router.push('/admin/students')">View All</button>
      </div>

      <div class="card text-center py-6 px-4">
        <div class="text-3xl font-bold text-yellow-600 mb-3">{{ stats.pendingPosts }}</div>
        <div class="text-gray-600 mb-4">Pending Posts</div>
        <button class="text-blue-600 text-sm" @click="filterPendingContent('posts')">View All</button>
      </div>

      <div class="card text-center py-6 px-4">
        <div class="text-3xl font-bold text-blue-600 mb-3">{{ stats.pendingEvents }}</div>
        <div class="text-gray-600 mb-4">Pending Events</div>
        <button class="text-blue-600 text-sm" @click="filterPendingContent('events')">View All</button>
      </div>

      <div class="card text-center py-6 px-4">
        <div class="text-3xl font-bold text-green-600 mb-3">{{ stats.totalUsers }}</div>
        <div class="text-gray-600">Total Users</div>
      </div>
    </div>

    <!-- Quick Actions Buttons -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <div class="card">
        <div class="card-header">
          <h2 class="text-xl font-semibold">Quick Actions</h2>
        </div>
        <div class="card-body">
          <div class="grid grid-cols-1 gap-4">
            <button class="btn btn-primary" @click="$router.push('/admin/students')">
              <svg class="w-5 h-5 mr-2 icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z">
                </path>
              </svg>
              Manage Pending Students
            </button>

            <button class="btn btn-warning" @click="$router.push('/admin/content')">
              <svg class="w-5 h-15 mr-2 icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
              </svg>
              Review Pending Content
            </button>

            <button class="btn btn-success" @click="$router.push('/admin/post/create')">
              <svg class="w-5 h-5 mr-2 icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              Create Announcement
            </button>

            <button class="btn btn-info" @click="$router.push('/admin/create-event')">
              <svg class="w-5 h-5 mr-2 icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
              Create Event
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import apiClient from '../../api/http'

export default {
  name: 'AdminDashboard',
  data() {
    return {
      stats: {
        pendingStudents: 0,
        pendingPosts: 0,
        pendingEvents: 0,
        totalUsers: 0
      }
    }
  },
  async mounted() {
    await this.fetchStats()
  },
  methods: {
    async fetchStats() {
      try {
        const response = await apiClient.get('/api/admin/dashboard')
        this.stats = response.data
      } catch (error) {
        console.error('Error fetching stats:', error)
      }
    },
    filterPendingContent(type) {
      this.$router.push(`/admin/content?type=${type}`)
    }
  }
}
</script>

<style scoped>
.card {
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.card-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.card-body {
  padding: 1.5rem;
}

.icon {
  width: 24px;
  height: 24px;
  stroke-width: 2;
}

.btn {
  padding: 0.75rem 1rem;
  border-radius: 0.375rem;
  font-weight: 500;
  transition: all 0.2s;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
}

.btn-primary {
  background-color: #2563eb;
  color: white;
}

.btn-primary:hover {
  background-color: #1d4ed8;
}

.btn-warning {
  background-color: #d97706;
  color: white;
}

.btn-warning:hover {
  background-color: #b45309;
}

.btn-success {
  background-color: #059669;
  color: white;
}

.btn-success:hover {
  background-color: #047857;
}

.btn-info {
  background-color: #0891b2;
  color: white;
}

.btn-info:hover {
  background-color: #0e7490;
}

.text-blue-600 {
  color: #2563eb;
}

.text-yellow-600 {
  color: #d97706;
}

.text-green-600 {
  color: #059669;
}
</style>
