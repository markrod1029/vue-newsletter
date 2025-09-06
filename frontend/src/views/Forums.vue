<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Forums</h1>
      <button v-if="userHasPermission" class="btn btn-primary" @click="showCreateForumModal = true">
        Create Forum
      </button>
    </div>

    <div class="mb-6">
      <input class="form-input" placeholder="Search forums..." v-model="searchQuery" @input="fetchForums" />
    </div>

    <div v-if="loading" class="text-center py-8">
      <div class="spinner"></div>
    </div>

    <div v-else>
      <div v-if="forums.length === 0" class="text-center py-8">
        <p class="text-gray-500">No forums found.</p>
      </div>

      <div v-else class="grid grid-cols-1 gap-4">
        <div v-for="forum in forums" :key="forum.id" class="card cursor-pointer hover:shadow-md transition-shadow" @click="$router.push(`/forums/${forum.id}`)">
          <div class="card-body">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-lg font-semibold">{{ forum.title }}</h3>
                <p class="text-gray-600">{{ forum.description }}</p>
                <div class="mt-2 text-sm text-gray-500">
                  <span>{{ forum.threads_count }} threads</span> • 
                  <span>{{ forum.comments_count }} comments</span> • 
                  <span>Created by {{ forum.creator?.name }}</span>
                </div>
              </div>
              <span v-if="forum.is_locked" class="badge badge-danger">Locked</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Forum Modal -->
    <div v-if="showCreateForumModal" class="modal-overlay">
      <div class="modal">
        <div class="modal-header">
          <h3 class="modal-title">Create New Forum</h3>
          <button class="modal-close" @click="showCreateForumModal = false">×</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="createForum">
            <div class="form-group">
              <label class="form-label">Title</label>
              <input class="form-input" v-model="newForum.title" required />
            </div>
            <div class="form-group">
              <label class="form-label">Description</label>
              <textarea class="form-textarea" v-model="newForum.description" rows="3"></textarea>
            </div>
            <div class="form-group">
              <label class="checkbox-label">
                <input type="checkbox" v-model="newForum.is_locked" />
                Lock Forum
              </label>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showCreateForumModal = false">Cancel</button>
          <button class="btn btn-primary" @click="createForum">Create</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from '../stores/auth'
import axios from 'axios'
import apiClient from '../api/http'

export default {
  name: 'Forums',
  data() {
    return {
      forums: [],
      loading: false,
      searchQuery: '',
      showCreateForumModal: false,
      newForum: {
        title: '',
        description: '',
        is_locked: false
      },
      forumErrors: {}
    }
  },
  computed: {
    userHasPermission() {
      const authStore = useAuthStore()
      return authStore.user && authStore.user.roles.some(role => role.name === 'admin')
    }
  },
  async mounted() {
    await this.fetchForums()
  },
  methods: {
    async fetchForums() {
      this.loading = true
      try {
        const params = new URLSearchParams({
          search: this.searchQuery
        })

        const response = await apiClient.get(`/api/forums-public?${params}`)
        this.forums = response.data.data
      } catch (error) {
        console.error('Error fetching forums:', error)
      } finally {
        this.loading = false
      }
    },
    async createForum() {
      try {
        const response = await apiClient.post('/api/forums', this.newForum)
        this.forums.unshift(response.data.data)
        this.showCreateForumModal = false
        this.newForum = { title: '', description: '', is_locked: false }
        this.forumErrors = {}
        alert('Forum created successfully')
      } catch (error) {
        if (error.response?.data?.errors) {
          this.forumErrors = error.response.data.errors
        }
        alert('Failed to create forum')
      }
    }
  }
}
</script>

<style scoped>
.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
</style>