<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">News & Articles</h1>
      <button 
        v-if="userHasPermission" 
        class="btn btn-primary" 
        @click="$router.push(getPostRoute('create'))"
      >
        Create Post
      </button>
    </div>

    <div class="mb-6">
      <div class="flex flex-col sm:flex-row gap-2">
        <!-- Search -->
        <input 
          class="form-input flex-1" 
          placeholder="Search posts..." 
          v-model="searchQuery" 
        />

        <!-- Filter -->
        <select 
          class="form-select w-full sm:w-48" 
          v-model="filterStatus"
        >
          <option value="">All Statuses</option>
          <option value="draft">Draft</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>

        <!-- Button -->
        <button 
          class="btn btn-primary w-full sm:w-auto" 
          @click="fetchPosts"
        >
          Search
        </button>
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
            <!-- Media Display - Image or Video -->
            <div v-if="post.media_url" class="mb-4 media-wrapper">
              <img 
                v-if="isImage(post.media_url)" 
                :src="getMediaUrl(post.media_url)" 
                :alt="post.title" 
                class="media-item"
                @error="handleImageError"
              >
              <video 
                v-else-if="isVideo(post.media_url)"
                :src="getMediaUrl(post.media_url)" 
                class="media-item"
                controls
              ></video>
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
             

              <button 
                v-if="canEditPost(post)" 
                class="btn btn-secondary btn-sm" 
                @click="$router.push(getPostRoute('edit', post.id))"
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
import apiClient from '../api/http'

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
      },
      brokenImages: new Set()
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

        const response = await apiClient.get(`/api/posts?${params}&my_posts=true`)
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

    // 🔑 Route builder based on role
    getPostRoute(action, postId = null) {
      if (!this.currentUser) return '/';

      let base = '/';
      if (this.currentUser.roles.some(role => role.name === 'admin')) {
        base = '/admin/post';
      } else if (this.currentUser.roles.some(role => role.name === 'student')) {
        base = '/student/post';
      }

      if (action === 'create') return `${base}/create`;
      if (action === 'edit' && postId) return `${base}/edit/${postId}`;
      if (action === 'view' && postId) return `${base}/view/${postId}`;

      return base;
    },
    
    isImage(url) {
      if (!url) return false;
      const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.bmp'];
      const lowerUrl = url.toLowerCase();
      return imageExtensions.some(ext => lowerUrl.includes(ext));
    },
    
    isVideo(url) {
      if (!url) return false;
      const videoExtensions = ['.mp4', '.mov', '.avi', '.webm', '.ogg'];
      const lowerUrl = url.toLowerCase();
      return videoExtensions.some(ext => lowerUrl.includes(ext));
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
      if (!mediaPath.includes('/')) {
        const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000';
        return `${baseUrl}/storage/posts/${mediaPath}`;
      }
      return mediaPath;
    },
    
    handleImageError(event) {
      const imgSrc = event.target.src;
      this.brokenImages.add(imgSrc);
      event.target.style.display = 'none';
    },
    
    handlePageChange(page) {
      this.fetchPosts(page)
    },
    
    async handleDeletePost(postId) {
      if (!confirm('Are you sure you want to delete this post?')) return
      try {
        await apiClient.delete(`/api/posts/${postId}`)
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
.media-wrapper {
  width: 100%;
  height: 16rem;
  overflow: hidden;
  border-radius: 0.5rem;
}

.media-item {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  border-radius: 0.5rem;
}

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

.badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge-secondary { background-color: #6c757d; color: white; }
.badge-warning { background-color: #ffc107; color: #212529; }
.badge-success { background-color: #28a745; color: white; }
.badge-danger { background-color: #dc3545; color: white; }
.badge-dark { background-color: #343a40; color: white; }

.btn {
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-weight: 500;
  transition: all 0.2s;
  border: none;
  cursor: pointer;
}

.btn-primary { background-color: #007bff; color: white; }
.btn-primary:hover { background-color: #0056b3; }

.btn-secondary { background-color: #6c757d; color: white; }
.btn-secondary:hover { background-color: #545b62; }

.btn-danger { background-color: #dc3545; color: white; }
.btn-danger:hover { background-color: #bd2130; }

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}

.form-input, .form-select {
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  width: auto;
}

.card {
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.card-body { padding: 1.5rem; }
</style>
