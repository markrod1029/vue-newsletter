<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">News & Articles</h1>
    </div>

    <!-- Search -->
    <div class="mb-6">
      <div class="flex gap-2">
        <input 
          class="form-input" 
          placeholder="Search approved posts..." 
          v-model="searchQuery" 
          @keyup.enter="fetchPosts" 
        />
        <button class="btn btn-primary" @click="fetchPosts">Search</button>
      </div>
    </div>

    <!-- Loader -->
    <div v-if="loading" class="text-center py-8">
      <div class="spinner"></div>
    </div>

    <!-- Results -->
    <div v-else>
      <div v-if="posts.length === 0" class="text-center py-8">
        <p class="text-gray-500">No approved posts found.</p>
      </div>

      <div v-else class="grid grid-cols-1 gap-6">
        <div v-for="post in posts" :key="post.id" class="card hover:shadow-lg transition-shadow">
          <div class="card-body">
            <!-- Media Display - Image or Video -->
            <div v-if="post.media_url" class="mb-4">
              <img 
                v-if="isImage(post.media_url)" 
                :src="getMediaUrl(post.media_url)" 
                :alt="post.title" 
                class="w-full h-48 object-cover rounded"
                @error="handleImageError"
              >
              <video 
                v-else-if="isVideo(post.media_url)"
                :src="getMediaUrl(post.media_url)" 
                class="w-full h-48 object-cover rounded"
                controls
                preload="metadata"
              >
                Your browser does not support the video tag.
              </video>
            </div>
            
            <h3 class="text-xl font-semibold mb-2">{{ post.title }}</h3>
            <p class="text-gray-600 mb-4">{{ truncateText(post.body, 200) }}</p>
            
            <div class="flex justify-between items-center mb-3">
              <div class="text-sm text-gray-500">
                <span>By {{ post.user?.name }}</span> • 
                <span>{{ formatDate(post.published_at) }}</span> • 
                <span>{{ post.type }}</span>
              </div>
            </div>
            
            <div class="flex gap-2">
              <button class="btn btn-primary btn-sm" @click="$router.push(`/feed/view/${post.id}`)">
                Read More
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
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
import axios from 'axios'

export default {
  name: 'PublicFeed',
  data() {
    return {
      posts: [],
      loading: false,
      searchQuery: '',
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0
      },
      brokenImages: new Set() // Track images that fail to load
    }
  },
  async mounted() {
    await this.fetchPosts()
  },
  methods: {
    async fetchPosts(page = 1) {
      this.loading = true
      try {
        const params = new URLSearchParams({ page })

        // send search only if not empty
        if (this.searchQuery.trim() !== '') {
          params.append('search', this.searchQuery)
        }

        const response = await axios.get(`/api/public-feed?${params.toString()}`)
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
      
      // If it's already a full URL, return as is
      if (mediaPath.startsWith('http://') || mediaPath.startsWith('https://')) {
        return mediaPath;
      }
      
      // If it's a storage path, prepend the base URL
      if (mediaPath.startsWith('/storage/')) {
        const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000';
        return `${baseUrl}${mediaPath}`;
      }
      
      // If it's just a filename, assume it's in storage
      if (!mediaPath.includes('/')) {
        const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000';
        return `${baseUrl}/storage/posts/${mediaPath}`;
      }
      
      return mediaPath;
    },
    
    handleImageError(event) {
      // Mark this image as broken to prevent repeated attempts
      const imgSrc = event.target.src;
      this.brokenImages.add(imgSrc);
      
      // Hide the broken image
      event.target.style.display = 'none';
    },
    
    handlePageChange(page) {
      this.fetchPosts(page)
    },
    
    truncateText(text, length) {
      const strippedText = text.replace(/<[^>]*>/g, '')
      return strippedText.length > length ? strippedText.substring(0, length) + '...' : strippedText
    },
    
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString()
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
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-weight: 500;
  transition: all 0.2s;
  border: none;
  cursor: pointer;
}

.btn-primary {
  background-color: #007bff;
  color: white;
}

.btn-primary:hover {
  background-color: #0056b3;
}

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}

.form-input {
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

.card-body {
  padding: 1.5rem;
}

/* Video styling */
video {
  background-color: #000;
}
</style>