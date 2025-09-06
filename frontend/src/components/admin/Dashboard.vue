<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Admin Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="card text-center">
        <div class="text-3xl font-bold text-blue-600 mb-2">{{ stats.pendingStudents }}</div>
        <div class="text-gray-600">Pending Students</div>
        <button class="text-blue-600 text-sm mt-2" @click="$router.push('/admin/students')">View All</button>
      </div>

      <div class="card text-center">
        <div class="text-3xl font-bold text-yellow-600 mb-2">{{ stats.pendingPosts }}</div>
        <div class="text-gray-600">Pending Posts</div>
        <button class="text-blue-600 text-sm mt-2" @click="filterPendingContent('posts')">View All</button>
      </div>

      <div class="card text-center">
        <div class="text-3xl font-bold text-blue-600 mb-2">{{ stats.pendingEvents }}</div>
        <div class="text-gray-600">Pending Events</div>
        <button class="text-blue-600 text-sm mt-2" @click="filterPendingContent('events')">View All</button>
      </div>

      <div class="card text-center">
        <div class="text-3xl font-bold text-green-600 mb-2">{{ stats.totalUsers }}</div>
        <div class="text-gray-600">Total Users</div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Activity -->
      <div class="card">
        <div class="card-header">
          <h2 class="text-xl font-semibold">Recent Activity</h2>
        </div>
        <div class="card-body">
          <ul class="list-group">
            <li v-for="activity in recentActivity" :key="activity.id" class="list-group-item">
              <div class="d-flex justify-between align-items-center">
                <div>
                  <h5 class="mb-1 text-sm font-medium">{{ activity.title }}</h5>
                  <small class="text-muted">{{ activity.description }}</small>
                </div>
                <small class="text-muted">{{ formatTime(activity.created_at) }}</small>
              </div>
            </li>
          </ul>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="card">
        <div class="card-header">
          <h2 class="text-xl font-semibold">Quick Actions</h2>
        </div>
        <div class="card-body">
          <div class="grid grid-cols-1 gap-3">
            <button class="btn btn-primary" @click="$router.push('/admin/students')">
              Manage Pending Students
            </button>
            <button class="btn btn-warning" @click="$router.push('/admin/content')">
              Review Pending Content
            </button>
            <button class="btn btn-success" @click="showCreatePostModal = true">
              Create Announcement
            </button>
            <button class="btn btn-info" @click="showCreateEventModal = true">
              Create Event
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Post Modal -->
    <div v-if="showCreatePostModal" class="modal-overlay">
      <div class="modal">
        <div class="modal-header">
          <h3 class="modal-title">Create Announcement</h3>
          <button class="modal-close" @click="showCreatePostModal = false">×</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="createPost">
            <div class="form-group">
              <label class="form-label">Title</label>
              <input class="form-input" v-model="newPost.title" required />
            </div>
            <div class="form-group">
              <label class="form-label">Content</label>
              <textarea class="form-textarea" v-model="newPost.body" rows="5" required></textarea>
            </div>
            <div class="form-group">
              <label class="form-label">Type</label>
              <select class="form-select" v-model="newPost.type">
                <option value="news">News</option>
                <option value="article">Article</option>
              </select>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showCreatePostModal = false">Cancel</button>
          <button class="btn btn-primary" @click="createPost">Create</button>
        </div>
      </div>
    </div>

    <!-- Create Event Modal -->
    <div v-if="showCreateEventModal" class="modal-overlay">
      <div class="modal">
        <div class="modal-header">
          <h3 class="modal-title">Create Event</h3>
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
import axios from 'axios'
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
      },
      recentActivity: [],
      showCreatePostModal: false,
      showCreateEventModal: false,
      newPost: {
        title: '',
        body: '',
        type: 'news'
      },
      newEvent: {
        title: '',
        description: '',
        location: '',
        start_at: '',
        end_at: ''
      }
    }
  },
  async mounted() {
    await this.fetchStats()
    await this.fetchRecentActivity()
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
    async fetchRecentActivity() {
      try {
        const response = await apiClient.get('/api/admin/recent-activity')
        this.recentActivity = response.data
      } catch (error) {
        console.error('Error fetching recent activity:', error)
      }
    },
    filterPendingContent(type) {
      this.$router.push(`/admin/content?type=${type}`)
    },
    formatTime(dateString) {
      return new Date(dateString).toLocaleTimeString()
    },
    async createPost() {
      try {
        await apiClient.post('/api/posts', this.newPost)
        this.showCreatePostModal = false
        this.newPost = { title: '', body: '', type: 'news' }
        alert('Post created successfully')
        await this.fetchStats()
      } catch (error) {
        console.error('Error creating post:', error)
        alert('Failed to create post')
      }
    },
    async createEvent() {
      try {
        await apiClient.post('/api/events', this.newEvent)
        this.showCreateEventModal = false
        this.newEvent = { title: '', description: '', location: '', start_at: '', end_at: '' }
        alert('Event created successfully')
        await this.fetchStats()
      } catch (error) {
        console.error('Error creating event:', error)
        alert('Failed to create event')
      }
    }
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: white;
  border-radius: 0.5rem;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: between;
  align-items: center;
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 600;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
}

.text-blue-600 { color: #2563eb; }
.text-yellow-600 { color: #d97706; }
.text-green-600 { color: #059669; }
</style>