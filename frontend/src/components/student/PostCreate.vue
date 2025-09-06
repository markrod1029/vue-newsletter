<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">{{ isEditing ? 'Edit Post' : 'Create New Post' }}</h1>
      <button class="btn btn-secondary" @click="$router.push('/student')">
        Back to Dashboard
      </button>
    </div>

    <div class="card">
      <div class="card-body">
        <form @submit.prevent="submitForm">
          <div class="form-group">
            <label class="form-label">Title</label>
            <input
              type="text"
              class="form-input"
              v-model="form.title"
              required
              :class="{ 'form-input-error': errors.title }"
              placeholder="Post Title"
            />
            <div v-if="errors.title" class="form-error">
              {{ errors.title[0] }}
            </div>
          </div>
          
          <div class="form-group">
            <label class="form-label">Content</label>
            <textarea 
              class="form-textarea" 
              v-model="form.body" 
              rows="10" 
              required
              :class="{ 'form-input-error': errors.body }"
              placeholder="Write your post content here..."
            ></textarea>
            <div v-if="errors.body" class="form-error">
              {{ errors.body[0] }}
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Type</label>
            <select
              class="form-select"
              v-model="form.type"
              :class="{ 'form-input-error': errors.type }"
            >
              <option value="news">News</option>
              <option value="article">Article</option>
            </select>
            <div v-if="errors.type" class="form-error">
              {{ errors.type[0] }}
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Cover Image URL (Optional)</label>
            <input
              type="url"
              class="form-input"
              v-model="form.cover_image_url"
              :class="{ 'form-input-error': errors.cover_image_url }"
              placeholder="https://example.com/image.jpg"
            />
            <div v-if="errors.cover_image_url" class="form-error">
              {{ errors.cover_image_url[0] }}
            </div>
          </div>

          <div class="flex gap-3 mt-4">
            <button 
              type="submit" 
              class="btn btn-primary" 
              :disabled="loading"
              @click="saveAsDraft = false"
            >
              <span v-if="loading">Submitting...</span>
              <span v-else>Submit for Approval</span>
            </button>
            
            <button 
              type="button" 
              class="btn btn-secondary" 
              :disabled="loading"
              @click="saveAsDraft = true; submitForm()"
            >
              Save as Draft
            </button>
            
            <button 
              type="button" 
              class="btn btn-outline" 
              @click="$router.push('/student')"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import apiClient from "../../api/http";

export default {
  name: 'PostCreate',
  data() {
    return {
      form: {
        title: '',
        body: '',
        type: 'news',
        cover_image_url: ''
      },
      errors: {},
      loading: false,
      saveAsDraft: false,
      isEditing: false,
      postId: null
    }
  },
  async mounted() {
    if (this.$route.params.id) {
      this.isEditing = true
      this.postId = this.$route.params.id
      await this.fetchPost()
    }
  },
  methods: {
    async fetchPost() {
      try {
        const response = await apiClient.get(`/api/posts/${this.postId}`)
        this.form = { ...response.data.data }
      } catch (error) {
        console.error('Error fetching post:', error)
        alert('Failed to load post')
      }
    },
    async submitForm() {
      this.loading = true
      this.errors = {}
      
      try {
        const postData = {
          ...this.form,
          status: this.saveAsDraft ? 'draft' : 'pending'
        }
        
        let response
        if (this.isEditing) {
          response = await apiClient.put(`/api/posts/${this.postId}`, postData)
        } else {
          response = await apiClient.post('/api/posts', postData)
        }
        
        alert(
          this.saveAsDraft 
            ? 'Post saved as draft successfully' 
            : 'Post submitted for approval successfully'
        )
        this.$router.push('/student')
      } catch (error) {
        if (error.response?.data?.errors) {
          this.errors = error.response.data.errors
        } else {
          alert('Failed to save post')
        }
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
.btn-outline {
  background-color: transparent;
  border: 1px solid #d1d5db;
  color: #374151;
}

.btn-outline:hover {
  background-color: #f9fafb;
}
</style>