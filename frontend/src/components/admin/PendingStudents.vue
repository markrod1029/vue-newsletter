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

      <table class="table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Grade Level</th>
            <th>Registered</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="student in students" :key="student.id">
            <td>{{ student.name }}</td>
            <td>{{ student.email }}</td>
            <td>{{ student.grade_level || 'N/A' }}</td>
            <td>{{ formatDate(student.created_at) }}</td>
            <td>
              <button class="btn btn-success btn-sm" @click="approveStudent(student.id)">
                Approve
              </button>
              <button class="btn btn-danger btn-sm ml-2" @click="rejectStudent(student.id)">
                Reject
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import apiClient from '../../api/http'
export default {
  name: 'PendingStudents',
  data() {
    return {
      students: [],
      loading: false
    }
  },
  async mounted() {
    await this.fetchPendingStudents()
  },
  methods: {
    async fetchPendingStudents() {
      this.loading = true
      try {
        const response = await apiClient.get('/api/admin/pending-students')
        this.students = response.data.data
      } catch (error) {
        console.error('Error fetching pending students:', error)
      } finally {
        this.loading = false
      }
    },
    async approveStudent(studentId) {
      try {
        await apiClient.post(`/api/admin/approve-student/${studentId}`)
        this.students = this.students.filter(student => student.id !== studentId)
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
        this.students = this.students.filter(student => student.id !== studentId)
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
.table {
  width: 100%;
  border-collapse: collapse;
}

.table th,
.table td {
  padding: 0.75rem;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
}

.table th {
  background-color: #f8f9fa;
  font-weight: 600;
}

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
}

.ml-2 {
  margin-left: 0.5rem;
}
</style>