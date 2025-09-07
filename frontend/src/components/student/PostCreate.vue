<template>
  <div class="p-4 max-w-3xl mx-auto">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-2">
      <h1 class="text-2xl font-bold">{{ isEditing ? 'Edit Post' : 'Create New Post' }}</h1>
   
    </div>

    <div class="" style="margin-right: 20px;">
      <div class=" flex flex-col ">
        <form @submit.prevent="submitForm" class="flex flex-col gap-4">
          <!-- Title -->
          <div class="form-group w-full">
            <label class="form-label">Title</label>
            <input
              type="text"
              class="form-input w-full"
              v-model="form.title"
              required
              :class="{ 'form-input-error': errors.title }"
              placeholder="Post Title"
            />
            <div v-if="errors.title" class="form-error">{{ errors.title[0] }}</div>
          </div>

          <!-- Content -->
          <div class="form-group w-full">
            <label class="form-label">Content</label>
            <textarea
              class="form-textarea w-full"
              v-model="form.body"
              rows="8"
              required
              :class="{ 'form-input-error': errors.body }"
              placeholder="Write your post content here..."
            ></textarea>
            <div v-if="errors.body" class="form-error">{{ errors.body[0] }}</div>
          </div>

          <!-- Type -->
          <div class="form-group w-full">
            <label class="form-label">Type</label>
            <select
              class="form-select w-full"
              v-model="form.type"
              :class="{ 'form-input-error': errors.type }"
            >
              <option value="news">News</option>
              <option value="article">Article</option>
            </select>
            <div v-if="errors.type" class="form-error">{{ errors.type[0] }}</div>
          </div>

          <!-- Cover Image -->
          <div class="form-group w-full">
            <label class="form-label">Cover Image URL (Optional)</label>
            <input
              type="url"
              class="form-input w-full"
              v-model="form.cover_image_url"
              :class="{ 'form-input-error': errors.cover_image_url }"
              placeholder="https://example.com/image.jpg"
            />
            <div v-if="errors.cover_image_url" class="form-error">
              {{ errors.cover_image_url[0] }}
            </div>
          </div>

          <!-- Buttons -->
          <div class="flex flex-col sm:flex-row gap-2 mt-4 w-full">
            <button
              type="submit"
              class="btn btn-primary flex-1"
              :disabled="loading"
              @click="saveAsDraft = false"
            >
              <span v-if="loading">Submitting...</span>
              <span v-else>Submit for Approval</span>
            </button>

            <button
              type="button"
              class="btn btn-secondary flex-1"
              :disabled="loading"
              @click="saveAsDraft = true; submitForm()"
            >
              Save as Draft
            </button>

            <button
              type="button"
              class="btn btn-outline flex-1"
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
import apiClient from "../../api/http";

export default {
  name: 'PostCreate',
  data() {
    return {
      form: {
        title: '',
        body: '',
        type: 'news',
        cover_image_url: '',
        status: 'draft'
      },
      errors: {},
      loading: false,
      saveAsDraft: false,
      isEditing: false,
      postId: null
    }
  },
  async mounted() {
    // Check if we're in edit mode by looking for the id parameter
    if (this.$route.name === 'PostEdit' && this.$route.params.id) {
      this.isEditing = true
      this.postId = this.$route.params.id
      await this.fetchPost()
    }
  },
  methods: {
    async fetchPost() {
      try {
        const response = await apiClient.get(`/api/posts/${this.postId}`)
        this.form = { 
          title: response.data.data.title,
          body: response.data.data.body,
          type: response.data.data.type,
          cover_image_url: response.data.data.cover_image_url,
          status: response.data.data.status
        }
      } catch (error) {
        console.error('Error fetching post:', error)
        if (error.response?.status === 403) {
          alert('You are not authorized to edit this post')
          this.$router.push('/student')
        } else {
          alert('Failed to load post')
        }
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

.form-input-error {
  border-color: #ef4444;
}

.form-error {
  color: #ef4444;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}
</style>
