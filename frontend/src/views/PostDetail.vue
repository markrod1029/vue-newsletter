<template>
  <div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="spinner mx-auto"></div>
      <p class="mt-4 text-gray-600">Loading post...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-12">
      <div class="text-red-600 mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
      </div>
      <h2 class="text-xl font-semibold text-gray-800 mb-2">Post Not Found</h2>
      <p class="text-gray-600 mb-4">{{ error }}</p>
      <button @click="$router.push('/')" class="btn btn-primary">Go to Home</button>
    </div>

    <!-- Blog Article Content -->
    <article v-else-if="post" class="bg-white">
      <!-- Media Display - Image or Video -->
      <div v-if="post.media_url" class="w-full mb-8">
        <img 
          v-if="isImage(post.media_url)" 
          :src="getMediaUrl(post.media_url)" 
          :alt="post.title" 
          class="w-full h-64 md:h-96 object-cover"
          @error="handleImageError"
        >
        <video 
          v-else-if="isVideo(post.media_url)"
          :src="getMediaUrl(post.media_url)" 
          class="w-full h-64 md:h-96 object-cover"
          controls
          preload="metadata"
        >
          Your browser does not support the video tag.
        </video>
      </div>

      <!-- Article Header -->
      <header class="mb-8">
        <!-- Category Badge -->
        <div class="mb-4">
          <span class="inline-block bg-blue-100 text-blue-800 px-4 py-2 rounded-full text-sm font-semibold uppercase tracking-wide">
            {{ post.type }}
          </span>
        </div>

        <!-- Title -->
        <h1 class="text-2xl md:text-5xl font-bold text-gray-900 mb-4 leading-tight">
          {{ post.title }}
        </h1>

        <!-- Meta Information -->
        <div class="flex items-center text-gray-600 mb-6">
          <!-- Author Avatar -->
          <div v-if="post.user?.avatar" class="mr-3">
            <img 
              :src="getMediaUrl(post.user.avatar)" 
              :alt="post.user.name" 
              class="w-10 h-10 rounded-full object-cover"
            >
          </div>
          <div class="text-sm">
            <span class="font-semibold text-gray-800">{{ post.user?.name }}</span>
            <span class="mx-2">•</span>
            <time :datetime="post.published_at">{{ formatDate(post.published_at) }}</time>
            <span class="mx-2">•</span>
            <span>{{ readingTime }} read</span>
          </div>
        </div>
      </header>

      <!-- Article Content -->
      <div class="prose prose-lg max-w-none">
        <!-- Introduction (first paragraph emphasized) -->
        <p class="text-xl text-gray-700 leading-relaxed mb-6 font-light">
          {{ getFirstParagraph(post.body) }}
        </p>

        <!-- Main Content -->
        <div class="text-gray-700 leading-8 whitespace-pre-line">
          {{ getRemainingContent(post.body) }}
        </div>
      </div>

      <!-- Article Footer -->
      <footer class="mt-12 pt-8 border-t border-gray-100">
        <!-- Tags/Categories -->
        <div class="flex flex-wrap gap-2 mb-6">
          <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm">
            {{ post.type }}
          </span>
          <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm">
            {{ formatDate(post.published_at, 'year') }}
          </span>
          <span v-if="post.media_type" class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm">
            {{ post.media_type }}
          </span>
        </div>

        <!-- Author Bio -->
        <div class="bg-gray-50 rounded-lg p-6">
          <div class="flex items-center">
            <div v-if="post.user?.avatar" class="mr-4">
              <img 
                :src="getMediaUrl(post.user.avatar)" 
                :alt="post.user.name" 
                class="w-16 h-16 rounded-full object-cover"
              >
            </div>
            <div>
              <h4 class="font-semibold text-gray-900">Written by {{ post.user?.name }}</h4>
              <p class="text-gray-600 text-sm mt-1">
                Published on {{ formatDate(post.published_at, 'full') }}
              </p>
            </div>
          </div>
        </div>
      </footer>
    </article>

    <!-- Related Posts Section -->
    <section v-if="post && relatedPosts.length > 0" class="mt-16">
      <div class="border-t border-gray-200 pt-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-8">You might also like</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <article 
            v-for="relatedPost in relatedPosts" 
            :key="relatedPost.id" 
            class="group cursor-pointer"
            @click="$router.push(`/posts/${relatedPost.id}`)"
          >
            <div class="bg-white rounded-lg overflow-hidden transition-transform group-hover:scale-105">
              <!-- Related Post Media -->
              <div v-if="relatedPost.media_url" class="w-full h-48 overflow-hidden">
                <img 
                  v-if="isImage(relatedPost.media_url)" 
                  :src="getMediaUrl(relatedPost.media_url)" 
                  :alt="relatedPost.title" 
                  class="w-full h-full object-cover transition-transform group-hover:scale-110"
                />
                <video 
                  v-else-if="isVideo(relatedPost.media_url)"
                  :src="getMediaUrl(relatedPost.media_url)" 
                  class="w-full h-full object-cover transition-transform group-hover:scale-110"
                  preload="metadata"
                />
              </div>
              
              <!-- Related Post Content -->
              <div class="p-4">
                <h3 class="font-semibold text-lg mb-2 line-clamp-2 group-hover:text-blue-600">
                  {{ relatedPost.title }}
                </h3>
                <p class="text-gray-600 text-sm mb-3 line-clamp-3">
                  {{ truncateText(relatedPost.body, 100) }}
                </p>
                <div class="flex items-center text-xs text-gray-500">
                  <time :datetime="relatedPost.published_at">
                    {{ formatDate(relatedPost.published_at, 'short') }}
                  </time>
                </div>
              </div>
            </div>
          </article>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import { useAuthStore } from '../stores/auth'
import apiClient from '../api/http'

export default {
  name: 'PostDetail',
  data() {
    return {
      post: null,
      relatedPosts: [],
      loading: true,
      error: null,
      brokenImages: new Set()
    }
  },
  computed: {
    currentUser() {
      const authStore = useAuthStore()
      return authStore.user
    },
    postId() {
      return this.$route.params.id
    },
    readingTime() {
      if (!this.post?.body) return '2 min'
      const words = this.post.body.split(' ').length
      const minutes = Math.ceil(words / 200)
      return `${minutes} min`
    }
  },
  async mounted() {
    await this.fetchPost()
    await this.fetchRelatedPosts()
  },
  methods: {
    async fetchPost() {
      this.loading = true
      this.error = null
      
      try {
        const response = await apiClient.get(`/api/posts/${this.postId}`)
        this.post = response.data.data
      } catch (error) {
        console.error('Error fetching post:', error)
        if (error.response?.status === 404) {
          this.error = 'The post you are looking for does not exist or has been removed.'
        } else if (error.response?.status === 403) {
          this.error = 'You do not have permission to view this post.'
        } else {
          this.error = 'Failed to load post. Please try again later.'
        }
      } finally {
        this.loading = false
      }
    },

    async fetchRelatedPosts() {
      if (!this.post) return
      
      try {
        const response = await apiClient.get('/api/public-feed', {
          params: {
            search: this.post.title.split(' ').slice(0, 3).join(' '),
            limit: 3
          }
        })
        this.relatedPosts = response.data.data
          .filter(p => p.id !== this.post.id)
          .slice(0, 3)
      } catch (error) {
        console.error('Error fetching related posts:', error)
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
      
      if (mediaPath.startsWith('http://') || mediaPath.startsWith('https://')) {
        return mediaPath;
      }
      
      if (mediaPath.startsWith('/storage/')) {
        const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000';
        return `${baseUrl}${mediaPath}`;
      }
      
      return mediaPath;
    },

    handleImageError(event) {
      const imgSrc = event.target.src;
      this.brokenImages.add(imgSrc);
      event.target.style.display = 'none';
    },

    getFirstParagraph(content) {
      if (!content) return '';
      const paragraphs = content.split('\n').filter(p => p.trim().length > 0);
      return paragraphs[0] || content.substring(0, 200) + '...';
    },

    getRemainingContent(content) {
      if (!content) return '';
      const paragraphs = content.split('\n').filter(p => p.trim().length > 0);
      return paragraphs.length > 1 ? paragraphs.slice(1).join('\n\n') : '';
    },

    truncateText(text, length) {
      if (!text) return ''
      const strippedText = text.replace(/<[^>]*>/g, '')
      return strippedText.length > length ? strippedText.substring(0, length) + '...' : strippedText
    },

    formatDate(dateString, format = 'full') {
      if (!dateString) return ''
      
      const date = new Date(dateString);
      
      if (format === 'year') {
        return date.getFullYear().toString();
      }
      
      if (format === 'short') {
        return date.toLocaleDateString('en-US', {
          month: 'short',
          day: 'numeric'
        });
      }
      
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    }
  }
}
</script>

<style scoped>
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
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 500;
  transition: all 0.2s;
  border: none;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-primary {
  background-color: #2563eb;
  color: white;
}

.btn-primary:hover {
  background-color: #1d4ed8;
  transform: translateY(-1px);
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.prose {
  max-width: none;
}

.prose p {
  margin-bottom: 1.5rem;
  line-height: 1.8;
  font-size: 1.125rem;
}

.prose-lg p {
  font-size: 1.125rem;
}

.group:hover .group-hover\:scale-105 {
  transform: scale(1.05);
}

.group:hover .group-hover\:scale-110 {
  transform: scale(1.1);
}

.transition-transform {
  transition: transform 0.3s ease;
}

/* Video styling */
video {
  background-color: #000;
}
</style>