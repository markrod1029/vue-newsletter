<template>
  <div class="p-4 md:p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
      <h1 class="text-2xl font-bold mb-4 md:mb-0">Student Dashboard</h1>
    </div>

    <!-- Pending Account Notice -->
    <div v-if="user.status === 'pending'"
      class="bg-yellow-100 text-yellow-800 border border-yellow-300 rounded p-4 mb-6">
      <p>Your account is pending approval. You can create posts but they won't be published until an admin approves your
        account.</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div class="bg-white shadow rounded-lg p-4 text-center">
        <div class="text-3xl font-bold text-blue-600 mb-1">{{ stats.posts }}</div>
        <div class="text-gray-600 text-sm">Your Posts</div>
      </div>
      <div class="bg-white shadow rounded-lg p-4 text-center">
        <div class="text-3xl font-bold text-green-600 mb-1">{{ stats.approvedPosts }}</div>
        <div class="text-gray-600 text-sm">Approved Posts</div>
      </div>
      <div class="bg-white shadow rounded-lg p-4 text-center">
        <div class="text-3xl font-bold text-yellow-600 mb-1">{{ stats.pendingPosts }}</div>
        <div class="text-gray-600 text-sm">Pending Posts</div>
      </div>
      <div class="bg-white shadow rounded-lg p-4 text-center">
        <div class="text-3xl font-bold text-blue-600 mb-1">{{ stats.events }}</div>
        <div class="text-gray-600 text-sm">Your Events</div>
      </div>
    </div>

    <!-- Recent Posts -->
    <div class="mb-8">
      <h2 class="text-xl font-semibold mb-4">Your Recent Posts</h2>
      <div v-if="posts.length === 0" class="text-center py-6">
        <p class="text-gray-500 mb-3">You haven't created any posts yet.</p>
        <button class="bg-blue-600 text-white px-4 py-2 rounded" @click="$router.push('/student/post/create')">
          Create Your First Post
        </button>
      </div>
      <div v-else class="space-y-4">
        <div v-for="post in posts" :key="post.id"
          class="bg-white shadow rounded-lg p-4 flex flex-col sm:flex-row sm:justify-between sm:items-center">
          <div class="mb-2 sm:mb-0">
            <h3 class="font-semibold text-lg">{{ post.title }}</h3>
            <div class="text-gray-500 text-sm">{{ formatDate(post.created_at) }}</div>
          </div>
          <div class="flex items-center gap-2 mt-2 sm:mt-0">
            <span :class="`px-2 py-1 rounded text-sm ${getStatusColor(post.status)}`">{{ post.status }}</span>
            <button class="bg-blue-600 text-white px-3 py-1 rounded text-sm" @click="editPost(post.id)">Edit</button>
            <button class="bg-red-600 text-white px-3 py-1 rounded text-sm" @click="deletePost(post.id)">Delete</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Events -->
    <div>
      <h2 class="text-xl font-semibold mb-4">Your Recent Events</h2>
      <div v-if="events.length === 0" class="text-center py-6">
        <p class="text-gray-500 mb-3">You haven't created any events yet.</p>
      </div>
      <div v-else class="space-y-4">
        <div v-for="event in events" :key="event.id"
          class="bg-white shadow rounded-lg p-4 flex flex-col sm:flex-row sm:justify-between sm:items-center">
          <div class="mb-2 sm:mb-0">
            <h3 class="font-semibold text-lg">{{ event.title }}</h3>
            <div class="text-gray-500 text-sm">{{ formatDate(event.start_at) }}</div>
          </div>
          <div class="flex items-center gap-2 mt-2 sm:mt-0">
            <span :class="`px-2 py-1 rounded text-sm ${getStatusColor(event.status)}`">{{ event.status }}</span>
            <button class="btn btn-primary btn-sm" @click="editEvent(event.id)">
              Edit
            </button>
            <button class="btn btn-danger btn-sm ml-2" @click="deleteEvent(event.id)">
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from '../../stores/auth'
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
    this.calculateStats()
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
    calculateStats() {
      this.stats.posts = this.posts.length
      this.stats.approvedPosts = this.posts.filter(p => p.status === 'approved').length
      this.stats.pendingPosts = this.posts.filter(p => p.status === 'pending').length
      this.stats.events = this.events.length
    },
    getStatusColor(status) {
      const colors = {
        draft: 'bg-gray-300 text-gray-800',
        pending: 'bg-yellow-200 text-yellow-800',
        approved: 'bg-green-200 text-green-800',
        rejected: 'bg-red-200 text-red-800',
        archived: 'bg-gray-500 text-white'
      }
      return colors[status] || 'bg-gray-300 text-gray-800'
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
        this.calculateStats()
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
        this.calculateStats()
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