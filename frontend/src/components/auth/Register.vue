<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Create your account
        </h2>
      </div>
      <form class="mt-8 space-y-6" @submit.prevent="handleRegister">
        <div v-if="errorMessage" class="alert alert-error">
          {{ errorMessage }}
        </div>

        <div class="form-group">
          <label for="name" class="form-label">Full Name</label>
          <input type="text" id="name" v-model="form.name" required class="form-input"
            :class="{ 'form-input-error': errors.name }" placeholder="Full Name" />
          <div v-if="errors.name" class="form-error">
            {{ errors.name[0] }}
          </div>
        </div>

        <div class="form-group">
          <label for="name" class="form-label">Student No.</label>
          <input type="text" id="name" v-model="form.studentNo" required class="form-input"
            :class="{ 'form-input-error': errors.studentNo }" placeholder="Student No." />
          <div v-if="errors.studentNo" class="form-error">
            {{ errors.studentNo[0] }}
          </div>
        </div>

        <div class="form-group">
          <label for="name" class="form-label">Contact No.</label>
          <input type="text" id="name" v-model="form.contact" required class="form-input"
            :class="{ 'form-input-error': errors.contact }" placeholder="Contact Number" />
          <div v-if="errors.name" class="form-error">
            {{ errors.contact[0] }}
          </div>
        </div>



        <div class="form-group">
          <label for="email" class="form-label">Email address</label>
          <input type="email" id="email" v-model="form.email" required class="form-input"
            :class="{ 'form-input-error': errors.email }" placeholder="Email Address" />
          <div v-if="errors.email" class="form-error">
            {{ errors.email[0] }}
          </div>
        </div>


        <div class="form-group">
          <label for="name" class="form-label">House No.</label>
          <input type="text" id="name" v-model="form.hno" required class="form-input"
            :class="{ 'form-input-error': errors.hno }" placeholder="Full Name" />
          <div v-if="errors.hno" class="form-error">
            {{ errors.hno[0] }}
          </div>
        </div>

        <div class="form-group">
          <label for="name" class="form-label">Sreet.</label>
          <input type="text" id="name" v-model="form.hno" required class="form-input"
            :class="{ 'form-input-error': errors.hno }" placeholder="Street" />
          <div v-if="errors.hno" class="form-error">
            {{ errors.hno[0] }}
          </div>
        </div>



          <div class="form-group">
          <label for="name" class="form-label">City.</label>
          <input type="text" id="name" v-model="form.city" required class="form-input"
            :class="{ 'form-input-error': errors.city }" placeholder="Ciry" />
          <div v-if="errors.city" class="form-error">
            {{ errors.city[0] }}
          </div>
        </div>

          <div class="form-group">
          <label for="name" class="form-label">Province.</label>
          <input type="text" id="name" v-model="form.province" required class="form-input"
            :class="{ 'form-input-error': errors.province }" placeholder="Province" />
          <div v-if="errors.province" class="form-error">
            {{ errors.province[0] }}
          </div>
        </div>

        <div class="form-group">
          <label for="password" class="form-label">Password</label>
          <input type="password" id="password" v-model="form.password" required class="form-input"
            :class="{ 'form-input-error': errors.password }" placeholder="Password" />
          <div v-if="errors.password" class="form-error">
            {{ errors.password[0] }}
          </div>
        </div>

        <div class="form-group">
          <label for="password_confirmation" class="form-label">Confirm Password</label>
          <input type="password" id="password_confirmation" v-model="form.password_confirmation" required
            class="form-input" :class="{ 'form-input-error': errors.password_confirmation }"
            placeholder="Confirm Password" />
          <div v-if="errors.password_confirmation" class="form-error">
            {{ errors.password_confirmation[0] }}
          </div>
        </div>

        <div class="form-group">
          <label for="grade_level" class="form-label">Grade Level (Optional)</label>
          <input type="text" id="grade_level" v-model="form.grade_level" class="form-input"
            :class="{ 'form-input-error': errors.grade_level }" placeholder="e.g., Grade 11, College Freshman" />
          <div v-if="errors.grade_level" class="form-error">
            {{ errors.grade_level[0] }}
          </div>
        </div>

        <div>
          <button type="submit" class="btn btn-primary w-full" :disabled="loading">
            <span v-if="loading">Creating account...</span>
            <span v-else>Sign up</span>
          </button>
        </div>

        <div class="text-center">
          <span class="text-sm text-gray-600">
            Already have an account?
            <router-link to="/login" class="link">
              Sign in
            </router-link>
          </span>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from '../../stores/auth'
import { ref } from 'vue'
import { useRouter } from 'vue-router'

export default {
  name: 'Register',
  setup() {
    const authStore = useAuthStore()
    const router = useRouter()

    const form = ref({
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      grade_level: ''
    })

    const loading = ref(false)
    const errorMessage = ref('')
    const errors = ref({})

    const handleRegister = async () => {
      loading.value = true
      errorMessage.value = ''
      errors.value = {}

      const result = await authStore.register(form.value)

      if (result.success) {
        router.push('/login?message=Registration successful. Please wait for admin approval.')
      } else {
        errorMessage.value = result.message
        if (result.errors) {
          errors.value = result.errors
        }
      }

      loading.value = false
    }

    return {
      form,
      loading,
      errorMessage,
      errors,
      handleRegister
    }
  }
}
</script>

<style scoped>
.min-h-screen {
  min-height: 100vh;
}

.flex {
  display: flex;
}

.items-center {
  align-items: center;
}

.justify-center {
  justify-content: center;
}

.bg-gray-50 {
  background-color: #f9fafb;
}

.py-12 {
  padding-top: 3rem;
  padding-bottom: 3rem;
}

.px-4 {
  padding-left: 1rem;
  padding-right: 1rem;
}

.sm\:px-6 {
  @media (min-width: 640px) {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
  }
}

.lg\:px-8 {
  @media (min-width: 1024px) {
    padding-left: 2rem;
    padding-right: 2rem;
  }
}

.max-w-md {
  max-width: 28rem;
}

.w-full {
  width: 100%;
}

.space-y-8>*+* {
  margin-top: 2rem;
}

.mt-6 {
  margin-top: 1.5rem;
}

.text-center {
  text-align: center;
}

.text-3xl {
  font-size: 1.875rem;
}

.font-extrabold {
  font-weight: 800;
}

.text-gray-900 {
  color: #1f2937;
}

.mt-8 {
  margin-top: 2rem;
}

.space-y-6>*+* {
  margin-top: 1.5rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #374151;
}

.form-input {
  display: block;
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-size: 1rem;
  line-height: 1.5;
}

.form-input:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.form-input-error {
  border-color: #dc3545;
}

.form-error {
  color: #dc3545;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem 1rem;
  border: 1px solid transparent;
  border-radius: 0.375rem;
  font-weight: 500;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.875rem;
  line-height: 1.25rem;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background-color: #007bff;
  color: white;
  border-color: #007bff;
}

.btn-primary:hover:not(:disabled) {
  background-color: #0056b3;
  border-color: #0056b3;
}

.w-full {
  width: 100%;
}

.text-center {
  text-align: center;
}

.text-sm {
  font-size: 0.875rem;
}

.text-gray-600 {
  color: #6b7280;
}

.link {
  color: #007bff;
  text-decoration: none;
}

.link:hover {
  text-decoration: underline;
}

.alert {
  padding: 1rem;
  border-radius: 0.375rem;
  margin-bottom: 1rem;
}

.alert-error {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}
</style>