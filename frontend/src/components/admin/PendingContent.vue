<template>
  <div>
    <h1 class="text-1xl font-bold">Pending Content</h1>

    <div class="flex justify-between items-center mb-6">
      <select class="form-select" v-model="contentType" style="width: 200px;" @change="fetchPendingContent">
        <option value="all">All Content</option>
        <option value="posts">Posts</option>
        <option value="events">Events</option>
      </select>
    </div>

    <div v-if="loading" class="text-center py-8">
      <div class="spinner"></div>
    </div>

    <div v-else>
      <div v-if="filteredContent.length === 0" class="text-center py-8">
        <p class="text-gray-500">No pending content found.</p>
      </div>

      <div v-else class="grid grid-cols-1 gap-6">
        <!-- Content Items -->
        <div v-for="item in filteredContent" :key="item.id + item.type" class="card">
          <div class="card-body">
            <!-- Media Display for Posts -->
            <div v-if="item.type && item.media_url" class="mb-4">
              <img 
                v-if="isImage(item.media_url)" 
                :src="getMediaUrl(item.media_url)" 
                :alt="item.title" 
                class="w-full h-48 object-cover rounded"
                @error="handleImageError"
              >
              <video 
                v-else-if="isVideo(item.media_url)"
                :src="getMediaUrl(item.media_url)" 
                class="w-full h-48 object-cover rounded"
                controls
                preload="metadata"
              >
                Your browser does not support the video tag.
              </video>
            </div>

            <!-- Media Display for Events -->
            <div v-if="!item.type && item.image_url" class="mb-4">
              <img 
                :src="getMediaUrl(item.image_url)" 
                :alt="item.title" 
                class="w-full h-48 object-cover rounded"
                @error="handleImageError"
              >
            </div>

            <h3 class="text-lg font-semibold mb-2">{{ item.title }}</h3>

            <!-- Post Content -->
            <template v-if="item.type">
              <p class="text-gray-600 mb-3">{{ truncateText(item.body, 200) }}</p>
              <div class="text-sm text-gray-500 mb-3">
                <span>By {{ item.user?.name }}</span> •
                <span>{{ formatDate(item.created_at) }}</span> •
                <span>Type: {{ item.type }}</span> •
                <span class="badge badge-warning">Post</span>
              </div>
            </template>

            <!-- Event Content -->
            <template v-else>
              <p class="text-gray-600 mb-3">{{ truncateText(item.description, 200) }}</p>
              <div class="text-sm text-gray-500 mb-3">
                <span>By {{ item.user?.name }}</span> •
                <span>{{ formatDate(item.created_at) }}</span> •
                <span>Location: {{ item.location }}</span> •
                <span>Starts: {{ formatDateTime(item.start_at) }}</span> •
                <span class="badge badge-info">Event</span>
              </div>
            </template>

            <div>
              <button class="btn btn-success btn-sm" @click="approveContent(item.type ? 'posts' : 'events', item.id)">
                Approve
              </button>

              <button class="btn btn-primary btn-sm ml-2" v-if="!item.type" 
                @click="$router.push({ name: 'EventEdit', params: { id: item.id } })"
                >
                Edit
              </button>

              <button class="btn btn-danger btn-sm ml-2"
                @click="rejectContent(item.type ? 'posts' : 'events', item.id)">
                Reject
              </button>
              <button class="btn btn-secondary btn-sm ml-2" @click="viewContent(item)">
                View Details
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Content Detail Modal -->
    <div v-if="showDetailModal" class="modal-overlay">
      <div class="modal" style="max-width: 800px;">
        <div class="modal-header">
          <h3 class="modal-title">{{ selectedContent.title }}</h3>
          <button class="modal-close" @click="showDetailModal = false">×</button>
        </div>
        <div class="modal-body">
          <!-- Media Display for Posts in Modal -->
          <div v-if="selectedContent.type && selectedContent.media_url" class="mb-4">
            <img 
              v-if="isImage(selectedContent.media_url)" 
              :src="getMediaUrl(selectedContent.media_url)" 
              :alt="selectedContent.title" 
              class="w-full h-64 object-contain rounded"
              @error="handleImageError"
            >
            <video 
              v-else-if="isVideo(selectedContent.media_url)"
              :src="getMediaUrl(selectedContent.media_url)" 
              class="w-full h-64 object-contain rounded"
              controls
              preload="metadata"
            >
              Your browser does not support the video tag.
            </video>
          </div>

          <!-- Media Display for Events in Modal -->
          <div v-if="!selectedContent.type && selectedContent.image_url" class="mb-4">
            <img 
              :src="getMediaUrl(selectedContent.image_url)" 
              :alt="selectedContent.title" 
              class="w-full h-64 object-contain rounded"
              @error="handleImageError"
            >
          </div>

          <div v-if="selectedContent.type">
            <h4 class="font-semibold mb-2">Content:</h4>
            <div class="prose mb-4" style="white-space: pre-wrap;">{{ selectedContent.body }}</div>
            <p><strong>Type:</strong> {{ selectedContent.type }}</p>
            <p><strong>Media Type:</strong> {{ selectedContent.media_type }}</p>
          </div>
          <div v-else>
            <h4 class="font-semibold mb-2">Description:</h4>
            <p class="mb-3" style="white-space: pre-wrap;">{{ selectedContent.description }}</p>
            <p><strong>Location:</strong> {{ selectedContent.location }}</p>
            <p><strong>Start:</strong> {{ formatDateTime(selectedContent.start_at) }}</p>
            <p><strong>End:</strong> {{ formatDateTime(selectedContent.end_at) }}</p>
            <p v-if="selectedContent.image_url"><strong>Has Image:</strong> Yes</p>
          </div>
          <div class="mt-4 text-sm text-gray-500">
            <p>Created by: {{ selectedContent.user?.name }}</p>
            <p>Created at: {{ formatDate(selectedContent.created_at) }}</p>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-success"
            @click="approveContent(selectedContent.type ? 'posts' : 'events', selectedContent.id)">
            Approve
          </button>
          <button class="btn btn-danger"
            @click="rejectContent(selectedContent.type ? 'posts' : 'events', selectedContent.id)">
            Reject
          </button>
          <button class="btn btn-secondary" @click="showDetailModal = false">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import apiClient from '../../api/http'

export default {
  name: 'PendingContent',
  data() {
    return {
      allContent: [], // Store all content
      loading: false,
      contentType: 'all',
      showDetailModal: false,
      selectedContent: {},
      brokenImages: new Set() // Track images that fail to load
    }
  },
  computed: {
    filteredContent() {
      if (this.contentType === 'all') {
        return this.allContent;
      } else if (this.contentType === 'posts') {
        return this.allContent.filter(item => item.type); // Posts have type field
      } else if (this.contentType === 'events') {
        return this.allContent.filter(item => !item.type); // Events don't have type field
      }
      return this.allContent;
    }
  },
  async mounted() {
    await this.fetchPendingContent()
  },
  methods: {
    async fetchPendingContent() {
      this.loading = true
      try {
        const response = await apiClient.get('/api/admin/pending-content')
        this.allContent = response.data.data
      } catch (error) {
        console.error('Error fetching pending content:', error)
        alert('Failed to fetch pending content')
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
        return `${baseUrl}/storage/${mediaPath}`;
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
    
    async approveContent(type, id) {
      try {
        await apiClient.post(`/api/admin/approve-content/${id}`, { type })
        // Remove from local array
        this.allContent = this.allContent.filter(item => item.id !== id)
        alert(`${type === 'posts' ? 'Post' : 'Event'} approved successfully`)
      } catch (error) {
        console.error('Error approving content:', error)
        alert('Failed to approve content')
      }
    },
    
    async rejectContent(type, id) {
      if (!confirm('Are you sure you want to reject this content?')) return

      try {
        await apiClient.post(`/api/admin/reject-content/${id}`, { type })
        // Remove from local array
        this.allContent = this.allContent.filter(item => item.id !== id)
        alert(`${type === 'posts' ? 'Post' : 'Event'} rejected successfully`)
      } catch (error) {
        console.error('Error rejecting content:', error)
        alert('Failed to reject content')
      }
    },
    
    viewContent(content) {
      this.selectedContent = { ...content }
      this.showDetailModal = true
    },
    
    truncateText(text, length) {
      if (!text) return ''
      return text.length > length ? text.substring(0, length) + '...' : text
    },
    
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString()
    },
    
    formatDateTime(dateString) {
      return new Date(dateString).toLocaleString()
    }
  }
}
</script>

<style scoped>
.prose {
  max-width: none;
  line-height: 1.6;
}

.badge {
  @apply px-2 py-1 text-xs rounded-full;
}

.badge-warning {
  @apply bg-yellow-100 text-yellow-800;
}

.badge-info {
  @apply bg-blue-100 text-blue-800;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal {
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
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
  margin: 0;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #e5e7eb;
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
}

/* Video styling */
video {
  background-color: #000;
}

.object-contain {
  object-fit: contain;
}
</style>