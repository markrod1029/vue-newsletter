<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Student Dashboard</h1>
      <button class="btn btn-primary" @click="$router.push('/student/post/create')">
        Create New Post
      </button>
    </div>
    
    <div v-if="user.status === 'pending'" class="alert alert-warning mb-6">
      <p>Your account is pending approval. You can create posts but they won't be published until an admin approves your account.</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
      <div class="card text-center">
        <div class="text-3xl font-bold text-blue-600 mb-2">{{ stats.posts }}</div>
        <div class="text-gray-600">Your Posts</div>
      </div>
      <div class="card text-center">
        <div class="text-3xl font-bold text-green-600 mb-2">{{ stats.approvedPosts }}</div>
        <div class="text-gray-600">Approved Posts</div>
      </div>
      <div class="card text-center">
        <div class="text-3xl font-bold text-yellow-600 mb-2">{{ stats.pendingPosts }}</div>
        <div class="text-gray-600">Pending Posts</div>
      </div>
      <div class="card text-center">
        <div class="text-3xl font-bold text-blue-600 mb-2">{{ stats.events }}</div>
        <div class="text-gray-600">Your Events</div>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6">
      <div class="card">
        <div class="card-header">
          <h2 class="text-xl font-semibold">Your Recent Posts</h2>
        </div>
        <div class="card-body">
          <div v-if="posts.length === 0" class="text-center py-4">
            <p class="text-gray-500">You haven't created any posts yet.</p>
            <button class="btn btn-primary mt-3" @click="$router.push('/student/post/create')">
              Create Your First Post
            </button>
          </div>
          <table v-else class="table">
            <thead>
              <tr>
                <th>Title</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="post in posts" :key="post.id">
                <td>{{ post.title }}</td>
                <td>
                  <span :class="`badge ${getStatusColor(post.status)}`">{{ post.status }}</span>
                </td>
                <td>{{ formatDate(post.created_at) }}</td>
                <td>
                  <button class="btn btn-primary btn-sm" @click="editPost(post.id)">
                    Edit
                  </button>
                  <button class="btn btn-danger btn-sm ml-2" @click="deletePost(post.id)">
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h2 class="text-xl font-semibold">Your Recent Events</h2>
        </div>
        <div class="card-body">
          <div v-if="events.length === 0" class="text-center py-4">
            <p class="text-gray-500">You haven't created any events yet.</p>
          </div>
          <table v-else class="table">
            <thead>
              <tr>
                <th>Title</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="event in events" :key="event.id">
                <td>{{ event.title }}</td>
                <td>
                  <span :class="`badge ${getStatusColor(event.status)}`">{{ event.status }}</span>
                </td>
                <td>{{ formatDate(event.start_at) }}</td>
                <td>
                  <button class="btn btn-primary btn-sm" @click="editEvent(event.id)">
                    Edit
                  </button>
                  <button class="btn btn-danger btn-sm ml-2" @click="deleteEvent(event.id)">
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from '../../stores/auth'
import axios from 'axios'
import apiClient from "../../api/http";

export default {
  name: 'StudentDashboard',
  data() {
    return {
      posts: [],
      events: [],
      stats: {
        posts: 0,
        approvedPosts: 0,
        pendingPosts: 0,
        events: 0
      }
    }
  },
  computed: {
    user() {
      const authStore = useAuthStore()
      return authStore.user || {}
    }
  },
  async mounted() {
    await this.fetchPosts()
    await this.fetchEvents()
    await this.calculateStats()
  },
  methods: {
    async fetchPosts() {
      try {
        const response = await apiClient.get('/api/posts?my_posts=true')
        this.posts = response.data.data.slice(0, 5)
      } catch (error) {
        console.error('Error fetching posts:', error)
      }
    },
    async fetchEvents() {
      try {
        const response = await apiClient.get('/api/events?my_events=true')
        this.events = response.data.data.slice(0, 5)
      } catch (error) {
        console.error('Error fetching events:', error)
      }
    },
    async calculateStats() {
      this.stats.posts = this.posts.length
      this.stats.approvedPosts = this.posts.filter(p => p.status === 'approved').length
      this.stats.pendingPosts = this.posts.filter(p => p.status === 'pending').length
      this.stats.events = this.events.length
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
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString()
    },
    editPost(postId) {
      this.$router.push(`/student/post/edit/${postId}`)
    },
    async deletePost(postId) {
      if (!confirm('Are you sure you want to delete this post?')) return
      
      try {
        await apiClient.delete(`/api/posts/${postId}`)
        this.posts = this.posts.filter(post => post.id !== postId)
        await this.calculateStats()
        alert('Post deleted successfully')
      } catch (error) {
        console.error('Error deleting post:', error)
        alert('Failed to delete post')
      }
    },
    editEvent(eventId) {
      alert('Event editing feature coming soon')
    },
    async deleteEvent(eventId) {
      if (!confirm('Are you sure you want to delete this event?')) return
      
      try {
        await apiClient.delete(`/api/events/${eventId}`)
        this.events = this.events.filter(event => event.id !== eventId)
        await this.calculateStats()
        alert('Event deleted successfully')
      } catch (error) {
        console.error('Error deleting event:', error)
        alert('Failed to delete event')
      }
    }
  }
}
</script>

<style scoped>
.alert-warning {
  background-color: #fff3cd;
  color: #856404;
  border: 1px solid #ffeaa7;
  padding: 1rem;
  border-radius: 0.375rem;
}
</style>