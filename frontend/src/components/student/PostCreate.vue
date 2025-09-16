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

          <!-- Media Upload Section -->
          <div v-if="!isEditing" class="form-group w-full">
            <label class="form-label">Media Attachment</label>
            
            <!-- Media Preview -->
            <div v-if="mediaPreview" class="mb-4 relative">
              <div v-if="mediaType === 'image'" class="relative">
                <img :src="mediaPreview" class="w-full h-64 object-contain rounded-lg border border-gray-300" alt="Preview">
                <button 
                  type="button" 
                  class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                  @click="removeMedia"
                >
                  <i class="fas fa-times"></i>
                </button>
              </div>
              <div v-else-if="mediaType === 'video'" class="relative">
                <video :src="mediaPreview" class="w-full h-64 object-contain rounded-lg border border-gray-300" controls></video>
                <button 
                  type="button" 
                  class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                  @click="removeMedia"
                >
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
            
            <!-- File Input -->
            <div class="flex flex-col gap-3">
              <div class="flex items-center gap-2">
                <input
                  type="file"
                  ref="fileInput"
                  class="hidden"
                  :accept="acceptTypes"
                  @change="handleMediaUpload"
                />
                <button
                  type="button"
                  class="btn btn-outline flex items-center gap-2"
                  @click="openFileInput"
                >
                  <i class="fas fa-upload"></i>
                  {{ form.media ? 'Change Media' : 'Upload Media' }}
                </button>
                
                <button
                  v-if="form.media"
                  type="button"
                  class="btn btn-danger flex items-center gap-2"
                  @click="removeMedia"
                >
                  <i class="fas fa-trash"></i>
                  Remove
                </button>
              </div>
              
              <div class="flex gap-2">
                <button 
                  type="button" 
                  class="btn-media-type" 
                  :class="{'btn-media-type-active': mediaType === 'image'}"
                  @click="setMediaType('image')"
                >
                  <i class="fas fa-image"></i> Image
                </button>
                <button 
                  type="button" 
                  class="btn-media-type" 
                  :class="{'btn-media-type-active': mediaType === 'video'}"
                  @click="setMediaType('video')"
                >
                  <i class="fas fa-video"></i> Video
                </button>
              </div>
            </div>
            
            <div class="text-sm text-gray-500 mt-1">
              <span v-if="mediaType === 'image'">
                Supported formats: JPEG, PNG, JPG, GIF. Max size: 5MB
              </span>
              <span v-else>
                Supported formats: MP4, MOV, AVI. Max size: 20MB
              </span>
            </div>
            
            <div v-if="errors.media" class="form-error">
              {{ errors.media[0] }}
            </div>
          </div>

          <!-- Buttons -->
          <div class="flex flex-col sm:flex-row gap-2 mt-4 w-full">
            <button
              type="submit"
              class="btn btn-primary flex-1 flex items-center justify-center gap-2"
              :disabled="loading"
              @click="saveAsDraft = false"
            >
              <i class="fas fa-paper-plane" v-if="!loading"></i>
              <i class="fas fa-spinner fa-spin" v-if="loading"></i>
              <span v-if="!isEditing">{{ loading ? 'Submitting...' : 'Submit for Approval' }}</span>
              <span v-else>{{ loading ? 'Updating...' : 'Update' }}</span>
            </button>

            <button v-if="!isEditing"
              type="button"
              class="btn btn-secondary flex-1 flex items-center justify-center gap-2"
              :disabled="loading"
              @click="saveAsDraft = true; submitForm()"
            >
              <i class="fas fa-save"></i>
              Save as Draft
            </button>

            <button
              type="button"
              class="btn btn-outline flex-1 flex items-center justify-center gap-2"
              @click="cancel"
            >
              <i class="fas fa-times"></i>
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
        media: null,
        status: 'draft'
      },
      mediaPreview: null,
      mediaType: 'image', // 'image' or 'video'
      errors: {},
      loading: false,
      saveAsDraft: false,
      isEditing: false,
      postId: null
    }
  },
  computed: {
    acceptTypes() {
      return this.mediaType === 'image' 
        ? 'image/*' 
        : 'video/*';
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
          status: response.data.data.status
        }
        
        // Set media preview if exists
        if (response.data.data.media_url) {
          this.mediaPreview = response.data.data.media_url;
          // Determine media type from URL extension
          const url = response.data.data.media_url.toLowerCase();
          if (url.endsWith('.mp4') || url.endsWith('.mov') || url.endsWith('.avi')) {
            this.mediaType = 'video';
          } else {
            this.mediaType = 'image';
          }
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
    
    setMediaType(type) {
      this.mediaType = type;
      this.removeMedia();
    },
    
    openFileInput() {
      this.$refs.fileInput.click();
    },
    
    handleMediaUpload(event) {
      const file = event.target.files[0];
      
      if (file) {
        // Validate file type and size based on media type
        let validTypes, maxSize, errorMessage;
        
        if (this.mediaType === 'image') {
          validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
          maxSize = 5 * 1024 * 1024; // 5MB
          errorMessage = 'Please select a valid image file (JPEG, PNG, JPG, GIF)';
        } else {
          validTypes = ['video/mp4', 'video/quicktime', 'video/x-msvideo'];
          maxSize = 20 * 1024 * 1024; // 20MB
          errorMessage = 'Please select a valid video file (MP4, MOV, AVI)';
        }
        
        if (!validTypes.includes(file.type)) {
          alert(errorMessage);
          this.$refs.fileInput.value = '';
          return;
        }
        
        if (file.size > maxSize) {
          alert(`File size must be less than ${maxSize/(1024*1024)}MB`);
          this.$refs.fileInput.value = '';
          return;
        }
        
        this.form.media = file;
        
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
          this.mediaPreview = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    },
    
    removeMedia() {
      this.form.media = null;
      this.mediaPreview = null;
      this.$refs.fileInput.value = '';
    },
    
    cancel() {
      this.$router.push('/student');
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
        
        // Append media if selected
        if (this.form.media) {
          formData.append('media', this.form.media);
          formData.append('media_type', this.mediaType);
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
        this.$router.push('/student');
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

.btn-media-type {
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  border: 1px solid #d1d5db;
  background-color: white;
  color: #374151;
  transition: all 0.2s;
}

.btn-media-type:hover {
  background-color: #f9fafb;
}

.btn-media-type-active {
  background-color: #3b82f6;
  color: white;
  border-color: #3b82f6;
}
</style>