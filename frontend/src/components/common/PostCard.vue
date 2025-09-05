<template>
  <CCard class="hover:shadow-lg transition-shadow">
    <CCardBody>
      <div v-if="post.cover_image_url" class="mb-4">
        <img :src="post.cover_image_url" :alt="post.title" class="w-full h-48 object-cover rounded">
      </div>
      
      <h3 class="text-xl font-semibold mb-2">{{ post.title }}</h3>
      
      <div class="prose max-w-none mb-4" v-html="truncateText(post.body, 200)"></div>
      
      <div class="flex justify-between items-center mb-3">
        <div class="text-sm text-gray-500">
          <span>By {{ post.user?.name }}</span> • 
          <span>{{ formatDate(post.created_at) }}</span> • 
          <span>{{ post.type }}</span>
        </div>
        <CBadge :color="getStatusColor(post.status)">{{ post.status }}</CBadge>
      </div>
      
      <div class="flex gap-2">
        <CButton color="primary" size="sm" @click="$router.push(`/posts/${post.id}`)">
          Read More
        </CButton>
        
        <CButton 
          v-if="canEditPost" 
          color="secondary" 
          size="sm" 
          @click="$router.push(`/student/post/edit/${post.id}`)"
        >
          Edit
        </CButton>
        
        <CButton 
          v-if="canDeletePost" 
          color="danger" 
          size="sm" 
          @click="$emit('delete', post.id)"
        >
          Delete
        </CButton>
      </div>
    </CCardBody>
  </CCard>
</template>

<script>
import { CCard, CCardBody, CButton, CBadge } from '@coreui/vue'
import { useAuthStore } from '../../stores/auth'

export default {
  name: 'PostCard',
  components: {
    CCard, CCardBody, CButton, CBadge
  },
  props: {
    post: {
      type: Object,
      required: true
    }
  },
  computed: {
    canEditPost() {
      const authStore = useAuthStore()
      return authStore.user && (
        authStore.user.id === this.post.user_id || 
        authStore.user.roles.some(role => role.name === 'admin')
      )
    },
    canDeletePost() {
      const authStore = useAuthStore()
      return authStore.user && (
        authStore.user.id === this.post.user_id || 
        authStore.user.roles.some(role => role.name === 'admin')
      )
    }
  },
  methods: {
    truncateText(text, length) {
      // Simple truncation for HTML content
      const strippedText = text.replace(/<[^>]*>/g, '')
      return strippedText.length > length ? strippedText.substring(0, length) + '...' : strippedText
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString()
    },
    getStatusColor(status) {
      const colors = {
        draft: 'secondary',
        pending: 'warning',
        approved: 'success',
        rejected: 'danger',
        archived: 'dark'
      }
      return colors[status] || 'secondary'
    }
  }
}
</script>