<template>
  <div class="w-full max-w-xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-2">
      <h1 class="text-2xl font-bold">{{ isEditing ? 'Edit Event' : 'Create New Event' }}</h1>
    </div>

    <!-- Form -->
    <div>
      <form @submit.prevent="createEvent" class="flex flex-col gap-4" enctype="multipart/form-data">
        <!-- Title -->
        <div class="form-group">
          <label class="form-label">Event title</label>
          <input type="text" class="form-input w-full" v-model="event.title" placeholder="Enter event title" required />
          <div v-if="errors.title" class="form-error">{{ errors.title[0] }}</div>
        </div>

        <!-- Description -->
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea class="form-textarea w-full" v-model="event.description" rows="4" placeholder="Describe the event..." required></textarea>
          <div v-if="errors.description" class="form-error">{{ errors.description[0] }}</div>
        </div>

        <!-- Location -->
        <div class="form-group">
          <label class="form-label">Location</label>
          <input type="text" class="form-input w-full" v-model="event.location" placeholder="Enter event location" required />
          <div v-if="errors.location" class="form-error">{{ errors.location[0] }}</div>
        </div>

        <!-- Start & End Date -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="form-group">
            <label class="form-label">Start Date & Time</label>
            <input type="datetime-local" class="form-input w-full" v-model="event.start_at" required />
            <div v-if="errors.start_at" class="form-error">{{ errors.start_at[0] }}</div>
          </div>
          <div class="form-group">
            <label class="form-label">End Date & Time</label>
            <input type="datetime-local" class="form-input w-full" v-model="event.end_at" required />
            <div v-if="errors.end_at" class="form-error">{{ errors.end_at[0] }}</div>
          </div>
        </div>

        <!-- Event Image -->
        <div class="form-group">
          <label class="form-label">Event Image (Optional)</label>
          <input 
            type="file" 
            class="form-input w-full" 
            @change="handleImageUpload" 
            accept="image/*" 
            ref="fileInput"
          />
          <div class="text-sm text-gray-500 mt-1">
            Supported formats: JPEG, PNG, JPG, GIF. Max size: 2MB
          </div>
          <div v-if="imagePreview" class="flex justify-center mt-3">
            <img :src="imagePreview" class="w-full max-w-xs h-48 object-cover rounded-lg border" alt="Preview" />
          </div>
          <div v-if="errors.image" class="form-error">{{ errors.image[0] }}</div>
        </div>

        <!-- Buttons -->
        <div class="flex flex-col sm:flex-row gap-3">
          <button type="button" class="btn btn-secondary w-full sm:w-auto" @click="$router.push('/admin')">
            Cancel
          </button>
          <button type="submit" class="btn btn-primary w-full sm:w-auto" :disabled="loading">
            <span v-if="loading">Creating...</span>
            <span v-else>Create Event</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import apiClient from '../../api/http'

export default {
  name: 'CreateEvent',
  data() {
    return {
      event: {
        title: '',
        description: '',
        location: '',
        start_at: '',
        end_at: '',
        image: null
      },
      imagePreview: null,
      loading: false,
      errors: {}
    }
  },
  methods: {
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
        
        this.event.image = file;

        const reader = new FileReader();
        reader.onload = (e) => {
          this.imagePreview = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    },

    async createEvent() {
      this.loading = true;
      this.errors = {};

      try {
        const formData = new FormData();
        formData.append('title', this.event.title);
        formData.append('description', this.event.description);
        formData.append('location', this.event.location);
        formData.append('start_at', this.event.start_at);
        formData.append('end_at', this.event.end_at);
        formData.append('status', 'approved'); // Auto-approve admin events

        if (this.event.image) {
          formData.append('image', this.event.image);
        }

        const response = await apiClient.post('/api/events', formData, {
          headers: { 
            'Content-Type': 'multipart/form-data',
            'Accept': 'application/json'
          }
        });

        alert('Event created successfully!');
        this.$router.push('/admin');
      } catch (error) {
        if (error.response && error.response.data.errors) {
          this.errors = error.response.data.errors;
        } else {
          console.error('Error creating event:', error);
          alert('Failed to create event');
        }
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>

<style scoped>
/* Buttons */
.btn {
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-weight: 500;
  transition: all 0.2s;
  border: none;
  cursor: pointer;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background-color: #3b82f6;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background-color: #2563eb;
}

.btn-secondary {
  background-color: #6b7280;
  color: white;
}

.btn-secondary:hover:not(:disabled) {
  background-color: #4b5563;
}

/* Form Elements */
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

/* Error */
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