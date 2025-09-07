<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold ">News & Articles</h1>
    </div>

    <!-- Search -->
    <div class="mb-6">
      <div class="flex gap-2">
        <input 
          class="form-input" 
          placeholder="Search approved posts..." 
          v-model="searchQuery" 
          @keyup.enter="fetchPosts" 
        />
        <button class="btn btn-primary" @click="fetchPosts">Search</button>
      </div>
    </div>

    <!-- Loader -->
    <div v-if="loading" class="text-center py-8">
      <div class="spinner"></div>
    </div>

    <!-- Results -->
    <div v-else>
      <div v-if="posts.length === 0" class="text-center py-8">
        <p class="text-gray-500">No approved posts found.</p>
      </div>

      <div v-else class="grid grid-cols-1 gap-6">
        <div v-for="post in posts" :key="post.id" class="card hover:shadow-lg transition-shadow">
          <div class="card-body">
            <div v-if="post.cover_image_url" class="mb-4">
              <img :src="post.cover_image_url" :alt="post.title" class="w-full h-48 object-cover rounded">
            </div>
            
            <h3 class="text-xl font-semibold mb-2">{{ post.title }}</h3>
            <p class="text-gray-600 mb-4">{{ truncateText(post.body, 200) }}</p>
            
            <div class="flex justify-between items-center mb-3">
              <div class="text-sm text-gray-500">
                <span>By {{ post.user?.name }}</span> • 
                <span>{{ formatDate(post.published_at) }}</span> • 
                <span>{{ post.type }}</span>
              </div>
            </div>
            
            <div class="flex gap-2">
              <button class="btn btn-primary btn-sm" @click="$router.push(`/posts/${post.id}`)">
                Read More
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="mt-6 flex justify-center" v-if="pagination.last_page > 1">
        <div class="pagination">
          <button 
            v-for="page in pagination.last_page" 
            :key="page"
            class="pagination-item"
            :class="{ 'pagination-item-active': page === pagination.current_page }"
            @click="handlePageChange(page)"
          >
            {{ page }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'PublicFeed',
  data() {
    return {
      posts: [],
      loading: false,
      searchQuery: '',
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0
      }
    }
  },
  async mounted() {
    await this.fetchPosts()
  },
  methods: {
    async fetchPosts(page = 1) {
      this.loading = true
      try {
        const params = new URLSearchParams({ page })

        // send search only if not empty
        if (this.searchQuery.trim() !== '') {
          params.append('search', this.searchQuery)
        }

        const response = await axios.get(`/api/public-feed?${params.toString()}`)
        this.posts = response.data.data
        this.pagination = {
          current_page: response.data.meta.current_page,
          last_page: response.data.meta.last_page,
          per_page: response.data.meta.per_page,
          total: response.data.meta.total
        }
      } catch (error) {
        console.error('Error fetching posts:', error)
      } finally {
        this.loading = false
      }
    },
    handlePageChange(page) {
      this.fetchPosts(page)
    },
    truncateText(text, length) {
      const strippedText = text.replace(/<[^>]*>/g, '')
      return strippedText.length > length ? strippedText.substring(0, length) + '...' : strippedText
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString()
    }
  }
}
</script>

<style scoped>
.pagination {
  display: flex;
  gap: 0.5rem;
}

.pagination-item {
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  background: white;
  cursor: pointer;
}

.pagination-item:hover {
  background-color: #f9fafb;
}

.pagination-item-active {
  background-color: #007bff;
  color: white;
  border-color: #007bff;
}

.object-cover {
  object-fit: cover;
}
</style>
