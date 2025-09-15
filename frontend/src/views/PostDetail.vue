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
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
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
        <img v-if="isImage(post.media_url)" :src="getMediaUrl(post.media_url)" :alt="post.title"
          class="w-full h-64 md:h-96 object-cover rounded-lg" @error="handleImageError">
        <video v-else-if="isVideo(post.media_url)" :src="getMediaUrl(post.media_url)"
          class="w-full h-64 md:h-96 object-cover rounded-lg" controls preload="metadata">
          Your browser does not support the video tag.
        </video>
      </div>

      <!-- Article Header -->
      <header class="mb-8">
        <!-- Category Badge -->
        <div class="mb-4">
          <span
            class="inline-block bg-blue-100 text-blue-800 px-4 py-2 rounded-full text-sm font-semibold uppercase tracking-wide">
            {{ post.type }}
          </span>
        </div>

        <!-- Title -->
        <h1 class="text-2xl md:text-5xl font-bold text-gray-900 mb-4 leading-tight">
          {{ post.title }}
        </h1>

        <!-- Meta Information -->
        <div class="flex items-center text-gray-600 mb-6">
          <!-- Author Avatar - FIXED: Now consistent with navbar -->
          <div class="mr-3">
            <div class="user-avatar">
              <img 
                v-if="post.user?.avatar" 
                :src="getMediaUrl(post.user.avatar)" 
                :alt="post.user.name"
                class="avatar-img"
                @error="(e) => handleAvatarError(e, post.user)"
              />
              <span v-else>
                {{ getUserInitials(post.user) }}
              </span>
            </div>
          </div>
          <div class="text-sm">
            <span class="font-semibold text-gray-800">{{ post.user?.name }}</span>
            <span class="mx-2">•</span>
            <time :datetime="post.published_at">{{ formatDate(post.published_at) }}</time>
            <span class="mx-2">•</span>
            <span>{{ readingTime }}</span>
          </div>
        </div>

        <!-- Like and Comment Stats -->
        <div class="flex items-center gap-6 mt-4">
          <button @click="toggleLike" class="flex items-center gap-2 text-gray-600 hover:text-red-600 transition-colors"
            :class="{ 'text-red-600': post.is_liked }" :disabled="likeLoading">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" :class="{ 'fill-current': post.is_liked }">
              <path
                d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
            </svg>
            <span>{{ post.likes_count }} {{ post.likes_count === 1 ? 'like' : 'likes' }}</span>
            <span v-if="likeLoading" class="ml-1">
              <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
              </svg>
            </span>
          </button>

          <div class="flex items-center gap-2 text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <span>{{ post.comments_count }} {{ post.comments_count === 1 ? 'comment' : 'comments' }}</span>
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

      <!-- Comments Section -->
      <section class="mt-12 pt-8 border-t border-gray-100">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Comments ({{ post.comments_count }})</h2>

        <!-- Add Comment Form -->
        <div v-if="currentUser" class="mb-8">
          <form @submit.prevent="addComment" class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-start gap-3">
              <div class="flex-shrink-0">
                <!-- Current User Avatar - FIXED: Now consistent with navbar -->
                <div class="user-avatar-sm">
                  <img 
                    v-if="currentUser.avatar" 
                    :src="getMediaUrl(currentUser.avatar)" 
                    :alt="currentUser.name"
                    class="avatar-img"
                    @error="(e) => handleAvatarError(e, currentUser)"
                  />
                  <span v-else>
                    {{ getUserInitials(currentUser) }}
                  </span>
                </div>
              </div>
              <div class="flex-1">
                <textarea v-model="newComment" placeholder="Write a comment..."
                  class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  rows="3" required :disabled="commentLoading"></textarea>
                <div class="flex justify-end mt-3">
                  <button type="submit" class="btn btn-primary" :disabled="commentLoading || !newComment.trim()">
                    <span v-if="commentLoading" class="flex items-center">
                      <svg class="w-4 h-4 mr-1 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                      </svg>
                      Posting...
                    </span>
                    <span v-else>Post Comment</span>
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div v-else class="mb-8 text-center py-4 bg-gray-50 rounded-lg">
          <p class="text-gray-600">Please <a href="/login" class="text-blue-600 hover:underline">login</a> to leave a
            comment.</p>
        </div>

        <!-- Comments List -->
        <div v-if="comments.length > 0" class="space-y-4">
          <div v-for="comment in comments" :key="comment.id" class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex items-start gap-3">
              <!-- Commenter Avatar - FIXED: Now consistent with navbar -->
              <div class="flex-shrink-0">
                <div class="user-avatar-sm">
                  <img 
                    v-if="comment.user?.avatar" 
                    :src="getMediaUrl(comment.user.avatar)" 
                    :alt="comment.user.name"
                    class="avatar-img"
                    @error="(e) => handleAvatarError(e, comment.user)"
                  />
                  <span v-else>
                    {{ getUserInitials(comment.user) }}
                  </span>
                </div>
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                  <h4 class="font-semibold text-gray-900 truncate">{{ comment.user?.name }}</h4>
                  <span class="text-sm text-gray-500 whitespace-nowrap">{{ formatTimeAgo(comment.created_at) }}</span>
                </div>
                <p class="text-gray-700  break-words">{{ comment.content }}</p>

                <!-- Comment Actions -->
                <div class="flex items-center gap-4 text-sm text-gray-500">
                  <button @click="toggleCommentLike(comment)"
                    class="flex items-center gap-1 hover:text-red-600 transition-colors"
                    :class="{ 'text-red-600': comment.is_liked }" :disabled="likeLoading">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"
                      :class="{ 'fill-current': comment.is_liked }">
                      <path
                        d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                    </svg>
                    <span>{{ comment.likes_count }}</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
          <p>No comments yet. Be the first to comment!</p>
        </div>
      </section>
    </article>

    <!-- Related Posts Section -->
    <section v-if="post && relatedPosts.length > 0" class="mt-16">
      <div class="border-t border-gray-200 pt-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-8">You might also like</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <article v-for="relatedPost in relatedPosts" :key="relatedPost.id" class="group cursor-pointer"
            @click="$router.push(`/posts/${relatedPost.id}`)">
            <div class="bg-white rounded-lg overflow-hidden transition-transform group-hover:scale-105">
              <!-- Related Post Media -->
              <div v-if="relatedPost.media_url" class="w-full h-48 overflow-hidden">
                <img v-if="isImage(relatedPost.media_url)" :src="getMediaUrl(relatedPost.media_url)"
                  :alt="relatedPost.title"
                  class="w-full h-full object-cover transition-transform group-hover:scale-110" />
                <video v-else-if="isVideo(relatedPost.media_url)" :src="getMediaUrl(relatedPost.media_url)"
                  class="w-full h-full object-cover transition-transform group-hover:scale-110" preload="metadata" />
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
            <div class="flex items-center mt-2">
              <div class="user-avatar-sm mr-2">
                <img 
                  v-if="relatedPost.user?.avatar" 
                  :src="getMediaUrl(relatedPost.user.avatar)" 
                  :alt="relatedPost.user.name"
                  class="avatar-img"
                  @error="(e) => handleAvatarError(e, relatedPost.user)"
                />
                <span v-else>
                  {{ getUserInitials(relatedPost.user) }}
                </span>
              </div>
              <span class="text-xs text-gray-600">{{ relatedPost.user?.name }}</span>
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
      comments: [],
      loading: true,
      commentLoading: false,
      likeLoading: false,
      error: null,
      newComment: '',
      brokenImages: new Set(),
      brokenAvatars: new Set()
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
      if (!this.post?.body) return '2 min read'
      const words = this.post.body.split(' ').length
      const minutes = Math.ceil(words / 200)
      return `${minutes} min read`
    }
  },
  async mounted() {
    await this.fetchPost()
    await this.fetchRelatedPosts()
    await this.fetchComments()
  },
  methods: {
    async fetchPost() {
      this.loading = true
      this.error = null

      try {
        const response = await apiClient.get(`/api/posts/${this.postId}`)
        this.post = response.data.data
        // Ensure like status is properly set
        this.post.is_liked = this.post.is_liked || false
        this.post.likes_count = this.post.likes_count || 0
        this.post.comments_count = this.post.comments_count || 0
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

    async fetchComments() {
      try {
        const response = await apiClient.get(`/api/posts/${this.postId}/comments`)
        this.comments = response.data.data.map(comment => ({
          ...comment,
          is_liked: comment.is_liked || false,
          likes_count: comment.likes_count || 0
        }))
      } catch (error) {
        console.error('Error fetching comments:', error)
        // Silently fail for comments as they're not critical to the main post
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

    async addComment() {
      if (!this.newComment.trim()) return

      this.commentLoading = true
      try {
        const response = await apiClient.post(`/api/posts/${this.postId}/comments`, {
          content: this.newComment
        })

        // Add the new comment to the beginning of the list
        this.comments.unshift({
          ...response.data.data,
          is_liked: false,
          likes_count: 0,
          user: this.currentUser
        })

        // Update the comment count - FIXED: Ensure this updates properly
        this.post.comments_count = (this.post.comments_count || 0) + 1

        // Clear the comment input
        this.newComment = ''
      } catch (error) {
        console.error('Error adding comment:', error)
        alert('Failed to add comment. Please try again.')
      } finally {
        this.commentLoading = false
      }
    },

    async toggleLike() {
      if (!this.currentUser) {
        alert('Please login to like posts')
        return
      }

      this.likeLoading = true
      try {
        // Store the current state before making the API call
        const wasLiked = this.post.is_liked
        const oldLikesCount = this.post.likes_count

        // Optimistically update the UI
        this.post.is_liked = !wasLiked
        this.post.likes_count = wasLiked ? oldLikesCount - 1 : oldLikesCount + 1

        const response = await apiClient.post(`/api/posts/${this.postId}/like`)

        // Ensure the UI reflects the actual server state
        this.post.is_liked = response.data.data.liked
        this.post.likes_count = response.data.data.likes_count
      } catch (error) {
        console.error('Error toggling like:', error)
        alert('Failed to toggle like. Please try again.')

        // Revert optimistic update on error
        this.post.is_liked = !this.post.is_liked
        this.post.likes_count = this.post.is_liked ?
          this.post.likes_count + 1 :
          Math.max(0, this.post.likes_count - 1)
      } finally {
        this.likeLoading = false
      }
    },

    async toggleCommentLike(comment) {
      if (!this.currentUser) {
        alert('Please login to like comments')
        return
      }

      this.likeLoading = true
      try {
        // Store the current state before making the API call
        const wasLiked = comment.is_liked
        const oldLikesCount = comment.likes_count

        // Optimistically update the UI
        comment.is_liked = !wasLiked
        comment.likes_count = wasLiked ? oldLikesCount - 1 : oldLikesCount + 1

        const response = await apiClient.post(`/api/comments/${comment.id}/like`)

        // Ensure the UI reflects the actual server state
        comment.is_liked = response.data.data.liked
        comment.likes_count = response.data.data.likes_count

        // Update the comment in the array to trigger reactivity
        const index = this.comments.findIndex(c => c.id === comment.id)
        if (index !== -1) {
          this.comments.splice(index, 1, { ...comment })
        }
      } catch (error) {
        console.error('Error toggling comment like:', error)
        alert('Failed to toggle comment like. Please try again.')

        // Revert optimistic update on error
        comment.is_liked = !comment.is_liked
        comment.likes_count = comment.is_liked ?
          comment.likes_count + 1 :
          Math.max(0, comment.likes_count - 1)
      } finally {
        this.likeLoading = false
      }
    },

    getUserInitials(user) {
      if (!user?.name) return 'U'
      return user.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)
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

    // FIXED: Enhanced getMediaUrl function to handle avatar paths correctly
    getMediaUrl(mediaPath) {
      if (!mediaPath) return ''
      
      console.log('Processing media path:', mediaPath); // Debug log

      // If it's already a full URL, return it
      if (
        mediaPath.startsWith('http://') ||
        mediaPath.startsWith('https://') ||
        mediaPath.startsWith('data:')
      ) {
        return mediaPath
      }

      const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'
      
      // Handle avatar paths (avatars/filename.jpg)
      if (mediaPath.includes('avatars')) {
        // Remove any backslashes that might be in the path
        const cleanPath = mediaPath.replace(/\\/g, '/')
        // If it starts with 'avatars/', prepend with /storage/
        if (cleanPath.startsWith('avatars/')) {
          return `${baseUrl}/storage/${cleanPath}`
        }
        // If it's just the filename, assume it's in the avatars directory
        if (!cleanPath.includes('/')) {
          return `${baseUrl}/storage/avatars/${cleanPath}`
        }
      }
      
      // Handle storage paths
      if (mediaPath.startsWith('storage/') || mediaPath.startsWith('/storage/')) {
        const cleanPath = mediaPath.replace(/^\/?storage\//, '')
        return `${baseUrl}/storage/${cleanPath}`
      }

      // For any other paths, assume they're in the storage directory
      return `${baseUrl}/storage/${mediaPath}`
    },

    handleImageError(event) {
      const imgSrc = event.target.src;
      this.brokenImages.add(imgSrc);
      event.target.style.display = 'none';
    },

    // Enhanced error handler to help debug
    handleAvatarError(event, user) {
      console.error('Avatar failed to load for user:', user?.name, 'Path:', event.target.src);
      const imgSrc = event.target.src;
      this.brokenAvatars.add(imgSrc);
      event.target.style.display = 'none';
      // Force re-render to show initials
      this.$forceUpdate();
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
    },

    formatTimeAgo(dateString) {
      if (!dateString) return ''

      const date = new Date(dateString);
      const now = new Date();
      const diffInSeconds = Math.floor((now - date) / 1000);

      if (diffInSeconds < 60) return 'just now';
      if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
      if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
      if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)}d ago`;

      return this.formatDate(dateString, 'short');
    }
  }
}
</script>

<style scoped>
.user-avatar {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  background-color: #007bff;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.875rem;
  overflow: hidden;
}

.user-avatar-sm {
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  background-color: #007bff;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.75rem;
  overflow: hidden;
}

.avatar-img {
  width: 100%;
  height: 100%;
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
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
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

.btn-primary:hover:not(:disabled) {
  background-color: #1d4ed8;
  transform: translateY(-1px);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-sm {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
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
  border-radius: 0.5rem;
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Fix for comment spacing */
.space-y-4>*+* {
  margin-top: 1rem;
}
  
/* Ensure text doesn't overflow */
.min-w-0 {
  min-width: 0;
}

.break-words {
  word-break: break-word;
}
</style>