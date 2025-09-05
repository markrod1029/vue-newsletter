<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">News & Articles</h1>
      <button v-if="userHasPermission" class="btn btn-primary" @click="$router.push('/student/post/create')">
        Create Post
      </button>
    </div>

    <div class="mb-6">
      <div class="flex gap-2">
        <input class="form-input" placeholder="Search posts..." v-model="searchQuery" />
        <select class="form-select" v-model="filterStatus">
          <option value="">All Statuses</option>
          <option value="draft">Draft</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
        <button class="btn btn-primary" @click="fetchPosts">Search</button>
      </div>
    </div>

    <div v-if="loading" class="text-center py-8">
      <div class="spinner"></div>
    </div>

    <div v-else>
      <div v-if="posts.length === 0" class="text-center py-8">
        <p class="text-gray-500">No posts found.</p>
      </div>

      <div v-else class="grid grid-cols-1 gap-6">
        <div v-for="post in posts" :key="post.id" class="card hover:shadow-lg transition-shadow">
          <div class="card-body">
            <div v-if="post.cover_image_url" class="mb-4">
              <img :src="post.cover_image_url" :alt="post.title" class="w-full h-48 object-cover rounded">
            </div>
            
            <h3 class="text-xl font-semibold mb-2">{{ post.title }}</h3>
            
            <p class="text-gray-600 mb-4">{{ truncateText(post.body, 200) }}</p>
            
            <div class="flex justify-between items-center mb-3">
              <div class="text-sm text-gray-500">
                <span>By {{ post.user?.name }}</span> • 
                <span>{{ formatDate(post.created_at) }}</span> • 
                <span>{{ post.type }}</span>
              </div>
              <span :class="`badge ${getStatusColor(post.status)}`">{{ post.status }}</span>
            </div>
            
            <div class="flex gap-2">
              <button class="btn btn-primary btn-sm" @click="$router.push(`/posts/${post.id}`)">
                Read More
              </button>
              
              <button 
                v-if="canEditPost(post)" 
                class="btn btn-secondary btn-sm" 
                @click="$router.push(`/student/post/edit/${post.id}`)"
              >
                Edit
              </button>
              
              <button 
                v-if="canDeletePost(post)" 
                class="btn btn-danger btn-sm" 
                @click="handleDeletePost(post.id)"
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
  </div>
</template>

<script>
import { useAuthStore } from '../stores/auth'
import axios from 'axios'

export default {
  name: 'Feed',
  data() {
    return {
      posts: [],
      loading: false,
      searchQuery: '',
      filterStatus: '',
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0
      }
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
    await this.fetchPosts()
  },
  methods: {
    async fetchPosts(page = 1) {
      this.loading = true
      try {
        const params = new URLSearchParams({
          page: page,
          search: this.searchQuery,
          status: this.filterStatus
        })

        const response = await axios.get(`/api/feed?${params}`)
        this.posts = response.data.data
        this.pagination = {
          current_page: response.data.meta.current_page,
          last_page: response.data.meta.last_page,
          per_page: response.data.meta.per_page,
          total: response.data.meta.total
        }
      } catch (error) {
        console.error('Error fetching posts:', error)
      } finally {
        this.loading = false
      }
    },
    handlePageChange(page) {
      this.fetchPosts(page)
    },
    async handleDeletePost(postId) {
      if (!confirm('Are you sure you want to delete this post?')) return
      
      try {
        await axios.delete(`/api/posts/${postId}`)
        this.posts = this.posts.filter(post => post.id !== postId)
        alert('Post deleted successfully')
      } catch (error) {
        console.error('Error deleting post:', error)
        alert('Failed to delete post')
      }
    },
    canEditPost(post) {
      return this.currentUser && (
        this.currentUser.id === post.user_id || 
        this.currentUser.roles.some(role => role.name === 'admin')
      )
    },
    canDeletePost(post) {
      return this.currentUser && (
        this.currentUser.id === post.user_id || 
        this.currentUser.roles.some(role => role.name === 'admin')
      )
    },
    truncateText(text, length) {
      const strippedText = text.replace(/<[^>]*>/g, '')
      return strippedText.length > length ? strippedText.substring(0, length) + '...' : strippedText
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString()
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

.object-cover {
  object-fit: cover;
}
</style>