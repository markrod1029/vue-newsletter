<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Pending Content</h1>
      <select class="form-select" v-model="contentType" style="width: 200px;">
        <option value="all">All Content</option>
        <option value="posts">Posts</option>
        <option value="events">Events</option>
      </select>
    </div>

    <div v-if="loading" class="text-center py-8">
      <div class="spinner"></div>
    </div>

    <div v-else>
      <div v-if="content.length === 0" class="text-center py-8">
        <p class="text-gray-500">No pending content found.</p>
      </div>

      <div v-else class="grid grid-cols-1 gap-6">
        <!-- Posts -->
        <div v-for="item in content" :key="item.id" class="card" v-if="contentType === 'posts' || contentType === 'all'">
          <div class="card-body">
            <h3 class="text-lg font-semibold mb-2">{{ item.title }}</h3>
            <p class="text-gray-600 mb-3">{{ truncateText(item.body, 200) }}</p>
            <div class="text-sm text-gray-500 mb-3">
              <span>By {{ item.user?.name }}</span> • 
              <span>{{ formatDate(item.created_at) }}</span> • 
              <span>Type: {{ item.type }}</span>
            </div>
            <div>
              <button class="btn btn-success btn-sm" @click="approveContent('posts', item.id)">
                Approve
              </button>
              <button class="btn btn-danger btn-sm ml-2" @click="rejectContent('posts', item.id)">
                Reject
              </button>
              <button class="btn btn-secondary btn-sm ml-2" @click="viewContent(item)">
                View Details
              </button>
            </div>
          </div>
        </div>

        <!-- Events -->
        <div v-for="item in content" :key="item.id" class="card" v-if="contentType === 'events' || contentType === 'all'">
          <div class="card-body">
            <h3 class="text-lg font-semibold mb-2">{{ item.title }}</h3>
            <p class="text-gray-600 mb-3">{{ truncateText(item.description, 200) }}</p>
            <div class="text-sm text-gray-500 mb-3">
              <span>By {{ item.user?.name }}</span> • 
              <span>{{ formatDate(item.created_at) }}</span> • 
              <span>Location: {{ item.location }}</span> • 
              <span>Starts: {{ formatDateTime(item.start_at) }}</span>
            </div>
            <div>
              <button class="btn btn-success btn-sm" @click="approveContent('events', item.id)">
                Approve
              </button>
              <button class="btn btn-danger btn-sm ml-2" @click="rejectContent('events', item.id)">
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
      <div class="modal">
        <div class="modal-header">
          <h3 class="modal-title">{{ selectedContent.title }}</h3>
          <button class="modal-close" @click="showDetailModal = false">×</button>
        </div>
        <div class="modal-body">
          <div v-if="selectedContent.type">
            <h4 class="font-semibold mb-2">Content:</h4>
            <div class="prose mb-4">{{ selectedContent.body }}</div>
          </div>
          <div v-else>
            <h4 class="font-semibold mb-2">Description:</h4>
            <p class="mb-3">{{ selectedContent.description }}</p>
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
import axios from 'axios'

export default {
  name: 'PendingContent',
  data() {
    return {
      content: [],
      loading: false,
      contentType: 'all',
      showDetailModal: false,
      selectedContent: {}
    }
  },
  watch: {
    contentType() {
      this.fetchPendingContent()
    }
  },
  async mounted() {
    await this.fetchPendingContent()
  },
  methods: {
    async fetchPendingContent() {
      this.loading = true
      try {
        const response = await axios.get(`/api/admin/pending-content?type=${this.contentType}`)
        this.content = response.data.data
      } catch (error) {
        console.error('Error fetching pending content:', error)
      } finally {
        this.loading = false
      }
    },
    async approveContent(type, id) {
      try {
        await axios.post(`/api/admin/approve-content/${id}`, { type })
        this.content = this.content.filter(item => item.id !== id)
        alert(`${type === 'posts' ? 'Post' : 'Event'} approved successfully`)
      } catch (error) {
        console.error('Error approving content:', error)
        alert('Failed to approve content')
      }
    },
    async rejectContent(type, id) {
      if (!confirm('Are you sure you want to reject this content?')) return
      
      try {
        await axios.post(`/api/admin/reject-content/${id}`, { type })
        this.content = this.content.filter(item => item.id !== id)
        alert(`${type === 'posts' ? 'Post' : 'Event'} rejected successfully`)
      } catch (error) {
        console.error('Error rejecting content:', error)
        alert('Failed to reject content')
      }
    },
    viewContent(content) {
      this.selectedContent = content
      this.showDetailModal = true
    },
    truncateText(text, length) {
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
</style>