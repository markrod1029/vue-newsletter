<template>
  <div class="max-w-3xl mx-auto p-6">
    <!-- Profile Card -->
    <div class="bg-white shadow rounded-2xl p-6 space-y-6">
      <!-- Avatar + Name -->
      <div class="flex items-center space-x-4">
        <!-- Avatar Upload Section -->
        <div class="relative">
          <!-- Avatar Display -->
          <div v-if="form.avatar" class="w-20 h-20">
            <img
              :src="form.avatar"
              alt="Avatar"
              class="w-20 h-20 rounded-full object-cover border-2 border-gray-300"
            />
          </div>
          <div v-else class="user-avatar w-20 h-20 text-2xl">
            {{ userInitials }}
          </div>
          
          <!-- Upload Button -->
          <label class="absolute bottom-0 right-0 bg-white rounded-full p-1 shadow-md cursor-pointer">
            <input
              type="file"
              ref="avatarInput"
              class="hidden"
              accept="image/*"
              @change="handleAvatarUpload"
            />
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
          </label>
        </div>

        <!-- Name + Email + Status -->
        <div>
          <h2 class="text-2xl font-semibold">{{ form.name }}</h2>
          <p class="text-gray-600">{{ form.email }}</p>
          <span
            class="px-2 py-1 text-sm rounded-xl"
            :class="statusClass(form.status)"
          >
            {{ form.status }}
          </span>
        </div>
      </div>

      <!-- Profile Form -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium">Name</label>
          <input v-model="form.name" type="text" class="form-input w-full" />
        </div>

        <div>
          <label class="block text-sm font-medium">Student ID</label>
          <input v-model="form.studentID" type="text" class="form-input w-full" />
        </div>

        <div>
          <label class="block text-sm font-medium">Contact</label>
          <input v-model="form.contact" type="text" class="form-input w-full" />
        </div>

        <div>
          <label class="block text-sm font-medium">Email</label>
          <input v-model="form.email" type="email" class="form-input w-full" disabled />
        </div>

        <div>
          <label class="block text-sm font-medium">House No.</label>
          <input v-model="form.hno" type="text" class="form-input w-full" />
        </div>

        <div>
          <label class="block text-sm font-medium">Street</label>
          <input v-model="form.street" type="text" class="form-input w-full" />
        </div>

        <div>
          <label class="block text-sm font-medium">City</label>
          <input v-model="form.city" type="text" class="form-input w-full" />
        </div>

        <div>
          <label class="block text-sm font-medium">Province</label>
          <input v-model="form.prov" type="text" class="form-input w-full" />
        </div>

        <div class="sm:col-span-2">
          <label class="block text-sm font-medium">Grade Level</label>
          <input v-model="form.grade_level" type="text" class="form-input w-full" />
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-end space-x-4">
        <button
          class="btn btn-secondary px-4 py-2"
          @click="resetForm"
          :disabled="loading"
        >
          Cancel
        </button>
        <button
          class="btn btn-primary px-4 py-2"
          @click="updateProfile"
          :disabled="loading"
        >
          <span v-if="loading" class="spinner"></span>
          Update Profile
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import apiClient from '../api/http'
import { ref, onMounted, computed } from 'vue'

export default {
  name: 'Profile',
  setup() {
    const form = ref({
      id: null,
      name: '',
      studentID: '',
      contact: '',
      email: '',
      hno: '',
      street: '',
      city: '',
      prov: '',
      status: '',
      grade_level: '',
      avatar: '',
      avatar_file: null
    })

    const originalForm = ref({})
    const loading = ref(false)
    const avatarInput = ref(null)

    const fetchUser = async () => {
      loading.value = true
      try {
        const { data } = await apiClient.get('/api/user')
        form.value = { ...data }
        originalForm.value = { ...data }
        
        // Ensure avatar URL is properly formatted
        if (form.value.avatar) {
          form.value.avatar = getMediaUrl(form.value.avatar)
          originalForm.value.avatar = data.avatar // Store the path, not the full URL
        }
      } finally {
        loading.value = false
      }
    }

    const handleAvatarUpload = (event) => {
      const file = event.target.files[0]
      if (file) {
        // Validate file type and size
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif']
        const maxSize = 2 * 1024 * 1024 // 2MB
        
        if (!validTypes.includes(file.type)) {
          alert('Please select a valid image file (JPEG, PNG, JPG, GIF)')
          resetAvatarInput()
          return
        }
        
        if (file.size > maxSize) {
          alert('Image size must be less than 2MB')
          resetAvatarInput()
          return
        }
        
        form.value.avatar_file = file
        
        // Create preview
        const reader = new FileReader()
        reader.onload = (e) => {
          form.value.avatar = e.target.result
        }
        reader.readAsDataURL(file)
      }
    }

    const resetAvatarInput = () => {
      if (avatarInput.value) {
        avatarInput.value.value = ''
      }
      form.value.avatar_file = null
    }

    const updateProfile = async () => {
      loading.value = true
      try {
        const formData = new FormData()
        
        // Append all form fields except avatar (we'll handle it separately)
        Object.keys(form.value).forEach(key => {
          if (key !== 'avatar' && key !== 'avatar_file' && form.value[key] !== null && form.value[key] !== undefined) {
            formData.append(key, form.value[key])
          }
        })
        
        // Append avatar file if selected
        if (form.value.avatar_file) {
          formData.append('avatar', form.value.avatar_file)
        }

        const response = await apiClient.post(`/api/user/profile`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        
        // Update the original form with the response data
        originalForm.value = { ...response.data.user || response.data }
        
        // Update the form values while preserving the avatar preview
        const updatedData = { ...response.data.user || response.data }
        Object.keys(updatedData).forEach(key => {
          if (key !== 'avatar') {
            form.value[key] = updatedData[key]
          }
        })
        
        // If avatar was updated, get the new URL
        if (response.data.user?.avatar || response.data.avatar) {
          const newAvatarPath = response.data.user?.avatar || response.data.avatar
          form.value.avatar = getMediaUrl(newAvatarPath)
          originalForm.value.avatar = newAvatarPath // Store the path, not the full URL
        }
        
        // Reset the avatar file
        form.value.avatar_file = null
        resetAvatarInput()
        
        alert('Profile updated successfully!')
      } catch (err) {
        console.error('Update error:', err)
        alert('Failed to update profile. Please try again.')
      } finally {
        loading.value = false
      }
    }

    const resetForm = () => {
      // Reset all fields except avatar preview
      const currentAvatar = form.value.avatar
      form.value = { ...originalForm.value }
      
      // Preserve the current avatar preview if it's a data URL (upload preview)
      if (currentAvatar && currentAvatar.startsWith('data:')) {
        form.value.avatar = currentAvatar
      } else {
        // Otherwise use the original avatar URL
        form.value.avatar = getMediaUrl(originalForm.value.avatar)
      }
      
      resetAvatarInput()
    }

    const statusClass = (status) => {
      switch (status) {
        case 'approved':
          return 'bg-green-100 text-green-800'
        case 'pending':
          return 'bg-yellow-100 text-yellow-800'
        case 'rejected':
          return 'bg-red-100 text-red-800'
        default:
          return 'bg-gray-100 text-gray-800'
      }
    }

    const getMediaUrl = (mediaPath) => {
      if (!mediaPath) return ''
      
      // If it's already a full URL or data URL, return as is
      if (mediaPath.startsWith('http://') || 
          mediaPath.startsWith('https://') || 
          mediaPath.startsWith('data:')) {
        return mediaPath
      }
      
      // If it's a storage path, prepend the base URL
      if (mediaPath.startsWith('storage/') || mediaPath.startsWith('/storage/')) {
        const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'
        // Remove any leading slash to avoid double slashes
        const cleanPath = mediaPath.replace(/^\//, '')
        return `${baseUrl}/${cleanPath}`
      }
      
      // If it's a relative path, assume it's from storage
      const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'
      return `${baseUrl}/storage/${mediaPath}`
    }

    const userInitials = computed(() => {
      if (!form.value.name) return ''
      return form.value.name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2)
    })

    onMounted(fetchUser)

    return {
      form,
      loading,
      avatarInput,
      fetchUser,
      updateProfile,
      resetForm,
      handleAvatarUpload,
      statusClass,
      userInitials
    }
  }
}
</script>

<style scoped>
.user-avatar {
  width: 5rem;
  height: 5rem;
  border-radius: 50%;
  background-color: #3b82f6;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 1.5rem;
}

.form-input {
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  width: 100%;
  transition: border-color 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

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

.spinner {
  display: inline-block;
  width: 1rem;
  height: 1rem;
  border: 2px solid #f3f3f3;
  border-top: 2px solid #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-right: 0.5rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>