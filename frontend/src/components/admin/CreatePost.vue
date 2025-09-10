<template>
  <div class="p-4 max-w-3xl mx-auto">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-2">
      <h1 class="text-2xl font-bold">{{ isEditing ? 'Edit Post' : 'Create New Post' }}</h1>
    </div>

    <div class="" style="margin-right: 20px;">
      <div class="flex flex-col">
        <form @submit.prevent="submitForm" class="flex flex-col gap-4" enctype="multipart/form-data">
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

          <!-- Cover Image Upload -->
          <div class="form-group w-full">
            <label class="form-label">Cover Image</label>
            
            <!-- Image Preview -->
            <div v-if="imagePreview" class="mb-3">
              <img :src="imagePreview" class="w-full h-48 object-cover rounded-lg border" alt="Preview">
            </div>
            
            <!-- File Input -->
            <div class="flex items-center gap-2">
              <input
                type="file"
                ref="fileInput"
                class="hidden"
                accept="image/*"
                @change="handleImageUpload"
              />
              <button
                type="button"
                class="btn btn-outline"
                @click="$refs.fileInput.click()"
              >
                {{ form.cover_image ? 'Change Image' : 'Upload Image' }}
              </button>
              
              <button
                v-if="form.cover_image"
                type="button"
                class="btn btn-danger"
                @click="removeImage"
              >
                Remove
              </button>
            </div>
            
            <div class="text-sm text-gray-500 mt-1">
              Supported formats: JPEG, PNG, JPG, GIF. Max size: 2MB
            </div>
            
            <div v-if="errors.cover_image" class="form-error">
              {{ errors.cover_image[0] }}
            </div>

            <!-- OR separator -->
            <div class="my-4 flex items-center">
              <div class="flex-grow border-t border-gray-300"></div>
              <span class="mx-4 text-gray-500">OR</span>
              <div class="flex-grow border-t border-gray-300"></div>
            </div>

            <!-- Image URL (fallback) -->
            <label class="form-label">Image URL (if not uploading)</label>
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
              @click="$router.push('/admin')"
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
        cover_image: null,
        cover_image_url: '',
        status: 'draft'
      },
      imagePreview: null,
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
        
        // Set image preview if exists
        if (this.form.cover_image_url) {
          this.imagePreview = this.form.cover_image_url
        }
      } catch (error) {
        console.error('Error fetching post:', error)
        if (error.response?.status === 403) {
          alert('You are not authorized to edit this post')
          this.$router.push('/admin')
        } else {
          alert('Failed to load post')
        }
      }
    },
    
    handleImageUpload(event) {
      const file = event.target.files[0];
      
      if (file) {
        // Validate file type and size
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        const maxSize = 2 * 1024 * 1024; // 2MB
        
        if (!validTypes.includes(file.type)) {
          alert('Please select a valid image file (JPEG, PNG, JPG, GIF)');
          this.$refs.fileInput.value = '';
          return;
        }
        
        if (file.size > maxSize) {
          alert('Image size must be less than 2MB');
          this.$refs.fileInput.value = '';
          return;
        }
        
        this.form.cover_image = file;
        this.form.cover_image_url = ''; // Clear URL field when uploading file
        
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
          this.imagePreview = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    },
    
    removeImage() {
      this.form.cover_image = null;
      this.form.cover_image_url = '';
      this.imagePreview = null;
      this.$refs.fileInput.value = '';
    },
    
    async submitForm() {
      this.loading = true
      this.errors = {}
      
      try {
        const formData = new FormData();
        formData.append('title', this.form.title);
        formData.append('body', this.form.body);
        formData.append('type', this.form.type);
        formData.append('status', this.saveAsDraft ? 'draft' : 'pending');
        
        // Append image if selected
        if (this.form.cover_image) {
          formData.append('cover_image', this.form.cover_image);
        } else if (this.form.cover_image_url) {
          formData.append('cover_image_url', this.form.cover_image_url);
        }
        
        let response;
        if (this.isEditing) {
          response = await apiClient.put(`/api/posts/${this.postId}`, formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          });
        } else {
          response = await apiClient.post('/api/posts', formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          });
        }
        
        alert(
          this.saveAsDraft 
            ? 'Post saved as draft successfully' 
            : 'Post submitted for approval successfully'
        );
        this.$router.push('/admin');
      } catch (error) {
        if (error.response?.data?.errors) {
          this.errors = error.response.data.errors;
        } else {
          alert('Failed to save post');
          console.error('Error:', error);
        }
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>

<style scoped>
.btn {
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-weight: 500;
  transition: all 0.2s;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background-color: #3b82f6;
  color: white;
  border: none;
}

.btn-primary:hover:not(:disabled) {
  background-color: #2563eb;
}

.btn-secondary {
  background-color: #6b7280;
  color: white;
  border: none;
}

.btn-secondary:hover:not(:disabled) {
  background-color: #4b5563;
}

.btn-outline {
  background-color: transparent;
  border: 1px solid #d1d5db;
  color: #374151;
}

.btn-outline:hover {
  background-color: #f9fafb;
}

.btn-danger {
  background-color: #ef4444;
  color: white;
  border: none;
}

.btn-danger:hover {
  background-color: #dc2626;
}

.form-input,
.form-textarea,
.form-select {
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  width: 100%;
  transition: border-color 0.2s;
}

.form-input:focus,
.form-textarea:focus,
.form-select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-input-error {
  border-color: #ef4444;
}

.form-error {
  color: #ef4444;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.form-label {
  display: block;
  font-weight: 500;
  margin-bottom: 0.5rem;
  color: #374151;
}

.form-group {
  margin-bottom: 1rem;
}
</style>