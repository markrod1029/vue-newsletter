<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Pending Content</h1>
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
              <button class="btn btn-danger btn-sm ml-2" @click="rejectContent(item.type ? 'posts' : 'events', item.id)">
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
      <div class="modal" style="max-width: 600px;">
        <div class="modal-header">
          <h3 class="modal-title">{{ selectedContent.title }}</h3>
          <button class="modal-close" @click="showDetailModal = false">×</button>
        </div>
        <div class="modal-body">
          <div v-if="selectedContent.type">
            <h4 class="font-semibold mb-2">Content:</h4>
            <div class="prose mb-4" style="white-space: pre-wrap;">{{ selectedContent.body }}</div>
            <p><strong>Type:</strong> {{ selectedContent.type }}</p>
          </div>
          <div v-else>
            <h4 class="font-semibold mb-2">Description:</h4>
            <p class="mb-3" style="white-space: pre-wrap;">{{ selectedContent.description }}</p>
            <p><strong>Location:</strong> {{ selectedContent.location }}</p>
            <p><strong>Start:</strong> {{ formatDateTime(selectedContent.start_at) }}</p>
            <p><strong>End:</strong> {{ formatDateTime(selectedContent.end_at) }}</p>
          </div>
          <div class="mt-4 text-sm text-gray-500">
            <p>Created by: {{ selectedContent.user?.name }}</p>
            <p>Created at: {{ formatDate(selectedContent.created_at) }}</p>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-success" @click="approveContent(selectedContent.type ? 'posts' : 'events', selectedContent.id)">
            Approve
          </button>
          <button class="btn btn-danger" @click="rejectContent(selectedContent.type ? 'posts' : 'events', selectedContent.id)">
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
      selectedContent: {}
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
</style>