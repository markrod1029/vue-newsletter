<template>
  <div class="px-6 py-8 max-w-3xl mx-auto">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-2">
      <h1 class="text-2xl font-bold">
        {{ isEditing ? 'Edit Event' : 'Create New Event' }}
      </h1>
    </div>

    <!-- Form -->
    <form
      @submit.prevent="handleSubmit"
      class="flex flex-col gap-6 bg-white"
      enctype="multipart/form-data"
    >
      <!-- Title -->
      <div>
        <label class="block font-medium mb-2">Event Title</label>
        <input
          type="text"
          class="form-input w-full max-w-xl"
          v-model="event.title"
          placeholder="Enter event title"
        />
        <p v-if="errors.title" class="text-red-500 text-sm mt-1">
          {{ errors.title[0] }}
        </p>
      </div>

      <!-- Description -->
      <div>
        <label class="block font-medium mb-2">Description</label>
        <textarea
          class="form-textarea w-full max-w-xl"
          v-model="event.description"
          placeholder="Enter event description"
        ></textarea>
        <p v-if="errors.description" class="text-red-500 text-sm mt-1">
          {{ errors.description[0] }}
        </p>
      </div>

      <!-- Location -->
      <div>
        <label class="block font-medium mb-2">Location</label>
        <input
          type="text"
          class="form-input w-full max-w-xl"
          v-model="event.location"
          placeholder="Enter event location"
        />
        <p v-if="errors.location" class="text-red-500 text-sm mt-1">
          {{ errors.location[0] }}
        </p>
      </div>

      <!-- Start Date -->
      <div>
        <label class="block font-medium mb-2">Start Date</label>
        <input type="datetime-local" class="form-input w-full max-w-xl" v-model="event.start_at" />
        <p v-if="errors.start_at" class="text-red-500 text-sm mt-1">
          {{ errors.start_at[0] }}
        </p>
      </div>

      <!-- End Date -->
      <div>
        <label class="block font-medium mb-2">End Date</label>
        <input type="datetime-local" class="form-input w-full max-w-xl" v-model="event.end_at" />
        <p v-if="errors.end_at" class="text-red-500 text-sm mt-1">
          {{ errors.end_at[0] }}
        </p>
      </div>

      <!-- Image Upload -->
      <div v-if="!isEditing">
        <label class="block font-medium mb-2">Event Image</label>
        <div class="flex flex-col gap-3">
          <input type="file" class="hidden" ref="fileInput" @change="handleImageUpload" />

          <div class="flex items-center gap-3">
            <button
              type="button"
              class="btn btn-outline flex items-center gap-2"
              @click="openFileInput"
            >
              <i class="fas fa-upload"></i>
              {{ event.image ? 'Change Image' : 'Upload Image' }}
            </button>
            <button
              v-if="imagePreview"
              type="button"
              class="btn btn-danger text-sm"
              @click="removeImage"
            >
              Remove
            </button>
          </div>

          <div v-if="imagePreview && !isEditing" class="mt-2">
            <img
              :src="imagePreview"
              alt="Preview"
              class="w-full h-64 object-contain rounded-lg border border-gray-300"
            />
          </div>
        </div>
        <p v-if="errors.image" class="text-red-500 text-sm mt-1">
          {{ errors.image[0] }}
        </p>
      </div>

      <!-- Submit -->
      <div class="flex justify-end gap-3">
        <div class="flex flex-col sm:flex-row gap-2 mt-4 w-full">
          <button
            type="submit"
            class="btn btn-primary flex-1 flex items-center justify-center gap-2"
            :disabled="loading"
          >
            <i class="fas fa-spinner fa-spin" v-if="loading"></i>
            <span>
              {{ loading ? (isEditing ? 'Updating...' : 'Creating...') : (isEditing ? 'Update Event' : 'Create Event') }}
            </span>
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
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import apiClient from '../../api/http'

// Router
const router = useRouter()
const route = useRoute()

// Form state
const event = ref({
  title: '',
  description: '',
  location: '',
  start_at: '',
  end_at: '',
  image: null
})

const fileInput = ref(null)
const imagePreview = ref(null)
const loading = ref(false)
const errors = ref({})
const isEditing = ref(false)
const eventId = ref(null)

// Cancel action
const cancel = () => {
  router.push('/admin')
}

// Open file input
const openFileInput = () => {
  if (fileInput.value) {
    fileInput.value.click()
  }
}

// Handle file upload
const handleImageUpload = (e) => {
  const file = e.target.files[0]
  if (file) {
    const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif']
    const maxSize = 2 * 1024 * 1024

    if (!validTypes.includes(file.type)) {
      alert('Please select a valid image file (JPEG, PNG, JPG, GIF)')
      e.target.value = ''
      return
    }

    if (file.size > maxSize) {
      alert('Image size must be less than 2MB')
      e.target.value = ''
      return
    }

    event.value.image = file

    const reader = new FileReader()
    reader.onload = (ev) => {
      imagePreview.value = ev.target.result
    }
    reader.readAsDataURL(file)
  }
}

// Remove image
const removeImage = () => {
  event.value.image = null
  imagePreview.value = null
  if (fileInput.value) {
    fileInput.value.value = '' // reset input
  }
}

// Format datetime for input[type=datetime-local]
const formatDateTime = (dateString) => {
  if (!dateString) return ''
  const d = new Date(dateString)
  const pad = (n) => (n < 10 ? '0' + n : n)
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
}

// Fetch existing event if editing
onMounted(async () => {
  if (route.params.id) {
    isEditing.value = true
    eventId.value = route.params.id

    try {
      const response = await apiClient.get(`/api/events/${eventId.value}`)
      const eventData = response.data.data ?? response.data // handle both cases

      event.value.title = eventData.title
      event.value.description = eventData.description
      event.value.location = eventData.location
      event.value.start_at = formatDateTime(eventData.start_at)
      event.value.end_at = formatDateTime(eventData.end_at)

      if (eventData.image_url) {
        imagePreview.value = eventData.image_url
        event.value.image = null
      }
    } catch (error) {
      console.error('Failed to fetch event:', error)
    }
  }
})

// Submit form
const handleSubmit = async () => {
  loading.value = true
  errors.value = {}

  try {
    const formData = new FormData()
    formData.append('title', event.value.title)
    formData.append('description', event.value.description)
    formData.append('location', event.value.location)
    formData.append('start_at', event.value.start_at)
    formData.append('end_at', event.value.end_at)
    formData.append('status', 'pending')

    if (event.value.image) {
      formData.append('image', event.value.image)
    }

    if (isEditing.value) {
      await apiClient.post(`/api/events/${eventId.value}?_method=PUT`, formData, {
        headers: { 'Content-Type': 'multipart/form-data', 'Accept': 'application/json' }
      })
      alert('Event updated successfully!')
    } else {
      await apiClient.post('/api/events', formData, {
        headers: { 'Content-Type': 'multipart/form-data', 'Accept': 'application/json' }
      })
      alert('Event created successfully!')
    }

    router.push('/admin')
  } catch (error) {
    if (error.response && error.response.data.errors) {
      errors.value = error.response.data.errors
    } else {
      console.error('Error saving event:', error)
      alert('Failed to save event')
    }
  } finally {
    loading.value = false
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
.form-textarea {
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  width: 100%;
  transition: border-color 0.2s;
}

.form-input:focus,
.form-textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
</style>
