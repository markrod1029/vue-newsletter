<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Pending Students</h1>
    </div>

    <div v-if="loading" class="text-center py-8">
      <div class="spinner"></div>
    </div>

    <div v-else>
      <div v-if="students.length === 0" class="text-center py-8">
        <p class="text-gray-500">No pending students found.</p>
      </div>

      <div class="grid gap-4">
        <div v-for="student in paginatedStudents" :key="student.id" class="bg-white shadow rounded-lg p-4 flex flex-col md:flex-row justify-between items-start md:items-center">
          <div class="mb-2 md:mb-0">
            <h2 class="font-semibold text-lg">{{ student.name }}</h2>
            <p class="text-gray-600 text-sm">Email: {{ student.email }}</p>
            <p class="text-gray-600 text-sm">Grade Level: {{ student.grade_level || 'N/A' }}</p>
            <p class="text-gray-500 text-xs">Registered: {{ formatDate(student.created_at) }}</p>
          </div>
          <div class="flex gap-2 mt-2 md:mt-0">
            <button class="btn btn-success btn-sm" @click="approveStudent(student.id)">Approve</button>
            <button class="btn btn-danger btn-sm" @click="rejectStudent(student.id)">Reject</button>
          </div>
        </div>
      </div>

      <!-- Pagination Controls -->
      <div v-if="totalPages > 1" class="flex justify-center items-center gap-2 mt-6">
        <button 
          :disabled="currentPage === 1"
          class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50"
          @click="currentPage--">
          Prev
        </button>

        <button
          v-for="page in totalPages"
          :key="page"
          :class="['px-3 py-1 rounded', currentPage === page ? 'bg-blue-600 text-white' : 'bg-gray-200']"
          @click="currentPage = page">
          {{ page }}
        </button>

        <button 
          :disabled="currentPage === totalPages"
          class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50"
          @click="currentPage++">
          Next
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import apiClient from '../../api/http'

export default {
  name: 'PendingStudents',
  data() {
    return {
      students: [],
      loading: false,
      currentPage: 1,
      studentsPerPage: 5
    }
  },
  async mounted() {
    await this.fetchPendingStudents()
  },
  computed: {
    paginatedStudents() {
      const start = (this.currentPage - 1) * this.studentsPerPage
      const end = start + this.studentsPerPage
      return this.students.slice(start, end)
    },
    totalPages() {
      return Math.ceil(this.students.length / this.studentsPerPage)
    }
  },
  methods: {
    async fetchPendingStudents() {
      this.loading = true
      try {
        const response = await apiClient.get('/api/admin/pending-students')
        this.students = response.data.data
        this.currentPage = 1
      } catch (error) {
        console.error('Error fetching pending students:', error)
      } finally {
        this.loading = false
      }
    },
    async approveStudent(studentId) {
      try {
        await apiClient.post(`/api/admin/approve-student/${studentId}`)
        this.students = this.students.filter(s => s.id !== studentId)
        alert('Student approved successfully')
      } catch (error) {
        console.error('Error approving student:', error)
        alert('Failed to approve student')
      }
    },
    async rejectStudent(studentId) {
      if (!confirm('Are you sure you want to reject this student?')) return
      try {
        await apiClient.post(`/api/admin/reject-student/${studentId}`)
        this.students = this.students.filter(s => s.id !== studentId)
        alert('Student rejected successfully')
      } catch (error) {
        console.error('Error rejecting student:', error)
        alert('Failed to reject student')
      }
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString()
    }
  }
}
</script>

<style scoped>
.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
}

.btn-success {
  background-color: #10b981;
  color: white;
  border: none;
  border-radius: 4px;
}

.btn-danger {
  background-color: #ef4444;
  color: white;
  border: none;
  border-radius: 4px;
}

.btn-sm:hover {
  opacity: 0.9;
}

.spinner {
  border: 4px solid rgba(0,0,0,0.1);
  border-left-color: #4f46e5;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  animation: spin 1s linear infinite;
  margin: 0 auto;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
