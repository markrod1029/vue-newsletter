<template>
    <div class="max-w-3xl mx-auto p-6">
        <!-- Profile Card -->
        <div class="bg-white  p-8 space-y-8">
            <!-- Avatar + Name -->
            <div class="flex items-center space-x-6">
                <!-- Avatar Upload Section -->
                <div class="relative">
                    <!-- Avatar Display -->
                    <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-white shadow-lg">
                        <div v-if="form.avatar" class="w-full h-full">
                            <img :src="form.avatar" alt="Avatar" class="w-full h-full object-cover" />
                        </div>
                        <div v-else class="user-avatar w-full h-full flex items-center justify-center">
                            <span class="text-3xl font-bold text-white">{{ userInitials }}</span>
                        </div>
                    </div>

                    <!-- Upload Button -->
                    <!-- <label class="absolute -bottom-1 -right-1 bg-blue-600 rounded-full p-2 shadow-lg cursor-pointer hover:bg-blue-700 transition-colors">
            <input
              type="file"
              ref="avatarInput"
              accept="image/*"
              @change="handleAvatarUpload"
            />
           <svg  class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          </label> -->
                </div>

                <!-- Name + Email -->
                <div>
                    <h2 class="text-2xl ml-4 font-semibold showName">{{ form.name }}</h2>
                    <span class="text-gray-600 ml-4 ">{{ form.email }}</span>


                </div>
            </div>

            <!-- Profile Form -->
            <div class="grid grid-cols-1 mt-4 md:grid-cols-2 gap-6">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Avatar</label>
                    <input type="file" ref="avatarInput" accept="image/*" @change="handleAvatarUpload"
                        class="form-input" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                    <input v-model="form.name" type="text" class="form-input" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Student ID</label>
                    <input v-model="form.studentID" type="text" class="form-input" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact</label>
                    <input v-model="form.contact" type="text" class="form-input" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input v-model="form.email" type="email" class="form-input bg-gray-100 cursor-not-allowed"
                        disabled />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">House No.</label>
                    <input v-model="form.hno" type="text" class="form-input" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Street</label>
                    <input v-model="form.street" type="text" class="form-input" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                    <input v-model="form.city" type="text" class="form-input" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Province</label>
                    <input v-model="form.prov" type="text" class="form-input" />
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Grade Level</label>
                    <input v-model="form.grade_level" type="text" class="form-input" />
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">

                
                <button class="btn btn-primary mx-auto ml-5" @click="updateProfile" :disabled="loading">
                    <span v-if="loading" class="spinner"></span>
                    Update Profile
                </button>

                <!-- <button class="btn-secondary" @click="resetForm" :disabled="loading">
                    <svg v-if="!loading" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Cancel
                </button>
                <button class="btn-primary" @click="updateProfile" :disabled="loading">
                    <span v-if="loading" class="spinner mr-2"></span>
                    <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Update Profile
                </button> -->
            </div>
        </div>
    </div>
</template>

<script>
import apiClient from '../api/http'
import { ref, onMounted, computed } from 'vue'
import { getMediaUrl } from '../utils/media'

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
                const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']
                const maxSize = 2 * 1024 * 1024 // 2MB

                if (!validTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPEG, PNG, JPG, GIF, WEBP)')
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
            switch (status?.toLowerCase()) {
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

     
        const userInitials = computed(() => {
            if (!form.value.name) return 'US'
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
.showName {
    margin-bottom: 0px;
}

.user-avatar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.form-input {
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    width: 100%;
    transition: all 0.2s;
    font-size: 0.95rem;
}

.form-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    transform: translateY(-1px);
}

.form-input:disabled {
    background-color: #f9fafb;
    color: #6b7280;
    cursor: not-allowed;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 0.95rem;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(59, 130, 246, 0.4);
}

.btn-primary:active:not(:disabled) {
    transform: translateY(0);
}

.btn-secondary {
    background: white;
    color: #6b7280;
    border: 2px solid #e5e7eb;
}

.btn-secondary:hover:not(:disabled) {
    background: #f9fafb;
    transform: translateY(-1px);
    border-color: #d1d5db;
}

.spinner {
    display: inline-block;
    width: 1rem;
    height: 1rem;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}

/* Smooth transitions for all interactive elements */
* {
    transition-property: color, background-color, border-color, transform, box-shadow;
    transition-duration: 0.2s;
    transition-timing-function: ease-in-out;
}
</style>