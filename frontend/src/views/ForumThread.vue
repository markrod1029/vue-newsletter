<template>
  <div>
    <div class="mb-6">
      <button class="btn btn-secondary mb-4" @click="$router.back()">
        Back to Forums
      </button>
      
      <div class="card">
        <div class="card-body">
          <h1 class="text-2xl font-bold mb-2">{{ forum.title }}</h1>
          <p class="text-gray-600 mb-4">{{ forum.description }}</p>
          <div class="flex items-center text-sm text-gray-500">
            <span>Created by {{ forum.creator?.name }}</span>
            <span class="mx-2">•</span>
            <span>{{ formatDate(forum.created_at) }}</span>
            <span v-if="forum.is_locked" class="badge badge-danger ml-4">Locked</span>
          </div>
        </div>
      </div>
    </div>

    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-semibold">Threads</h2>
      <button 
        v-if="!forum.is_locked && userHasPermission" 
        class="btn btn-primary" 
        @click="showCreateThreadModal = true"
      >
        Create Thread
      </button>
    </div>

    <div v-if="loading" class="text-center py-8">
      <div class="spinner"></div>
    </div>

    <div v-else>
      <div v-if="threads.length === 0" class="text-center py-8">
        <p class="text-gray-500">No threads found in this forum.</p>
      </div>

      <div v-else class="grid grid-cols-1 gap-4">
        <div v-for="thread in threads" :key="thread.id" class="card hover:shadow-md transition-shadow">
          <div class="card-body">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-lg font-semibold mb-2">{{ thread.title }}</h3>
                <p class="text-gray-600 mb-3">{{ truncateText(thread.body, 150) }}</p>
                <div class="text-sm text-gray-500">
                  <span>By {{ thread.author.name }}</span> • 
                  <span>{{ formatDate(thread.created_at) }}</span> • 
                  <span>{{ thread.comments_count }} comments</span>
                </div>
              </div>
              <div class="flex gap-2">
                <span :class="`badge ${getStatusColor(thread.status)}`">{{ thread.status }}</span>
                <span v-if="thread.is_locked" class="badge badge-danger">Locked</span>
              </div>
            </div>
          </div>
          <div class="card-footer">
            <button class="btn btn-primary" @click="$router.push(`/threads/${thread.id}`)">
              View Thread
            </button>
            <button 
              v-if="userHasPermission && (userIsAdmin || thread.user_id === authUser.id)" 
              class="btn btn-danger ml-2"
              @click="deleteThread(thread.id)"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Thread Modal -->
    <div v-if="showCreateThreadModal" class="modal-overlay">
      <div class="modal">
        <div class="modal-header">
          <h3 class="modal-title">Create New Thread</h3>
          <button class="modal-close" @click="showCreateThreadModal = false">×</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="createThread">
            <div class="form-group">
              <label class="form-label">Title</label>
              <input class="form-input" v-model="newThread.title" required />
            </div>
            <div class="form-group">
              <label class="form-label">Content</label>
              <textarea class="form-textarea" v-model="newThread.body" rows="5" required></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showCreateThreadModal = false">Cancel</button>
          <button class="btn btn-primary" @click="createThread">Create</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from '../stores/auth'
import axios from 'axios'

export default {
  name: 'ForumThread',
  data() {
    return {
      forum: {},
      threads: [],
      loading: false,
      showCreateThreadModal: false,
      newThread: {
        title: '',
        body: ''
      },
      threadErrors: {}
    }
  },
  computed: {
    userHasPermission() {
      const authStore = useAuthStore()
      return authStore.user && authStore.user.status === 'approved'
    },
    userIsAdmin() {
      const authStore = useAuthStore()
      return authStore.user && authStore.user.roles.some(role => role.name === 'admin')
    },
    authUser() {
      const authStore = useAuthStore()
      return authStore.user || {}
    }
  },
  async mounted() {
    await this.fetchForum()
    await this.fetchThreads()
  },
  methods: {
    async fetchForum() {
      try {
        const response = await axios.get(`/api/forums/${this.$route.params.id}`)
        this.forum = response.data.data
      } catch (error) {
        console.error('Error fetching forum:', error)
      }
    },
    async fetchThreads() {
      this.loading = true
      try {
        const response = await axios.get(`/api/threads?forum_id=${this.$route.params.id}`)
        this.threads = response.data.data
      } catch (error) {
        console.error('Error fetching threads:', error)
      } finally {
        this.loading = false
      }
    },
    async createThread() {
      try {
        const threadData = {
          ...this.newThread,
          forum_id: this.$route.params.id
        }
        
        const response = await axios.post('/api/threads', threadData)
        this.threads.unshift(response.data.data)
        this.showCreateThreadModal = false
        this.newThread = { title: '', body: '' }
        this.threadErrors = {}
        alert('Thread created successfully')
      } catch (error) {
        if (error.response?.data?.errors) {
          this.threadErrors = error.response.data.errors
        }
        alert('Failed to create thread')
      }
    },
    async deleteThread(threadId) {
      if (!confirm('Are you sure you want to delete this thread?')) return
      
      try {
        await axios.delete(`/api/threads/${threadId}`)
        this.threads = this.threads.filter(thread => thread.id !== threadId)
        alert('Thread deleted successfully')
      } catch (error) {
        console.error('Error deleting thread:', error)
        alert('Failed to delete thread')
      }
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString()
    },
    truncateText(text, length) {
      return text.length > length ? text.substring(0, length) + '...' : text
    },
    getStatusColor(status) {
      const colors = {
        visible: 'badge-success',
        hidden: 'badge-warning',
        locked: 'badge-danger'
      }
      return colors[status] || 'badge-secondary'
    }
  }
}
</script>