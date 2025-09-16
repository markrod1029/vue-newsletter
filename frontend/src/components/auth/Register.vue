<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-8 px-4">
    <div class="max-w-md w-full space-y-6">
      <div class="text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-2">
          Create your account
        </h2>
        <p class="text-gray-600">Join our campus community</p>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-md">
        <form class="space-y-4" @submit.prevent="handleRegister">
          <div v-if="errorMessage" class="alert alert-error">
            <svg xmlns="http://www.w3.org/2000/svg" class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                clip-rule="evenodd" />
            </svg>
            {{ errorMessage }}
          </div>

          <!-- Personal Information Section -->
          <div class="section-header">
            <h3 class="section-title">Personal Information</h3>
          </div>

          <div class="form-group">
            <label for="name" class="form-label">Full Name</label>
            <div class="input-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
              </svg>
              <input type="text" id="name" v-model="form.name" required class="form-input"
                :class="{ 'form-input-error': errors.name }" placeholder="Enter your full name" />
            </div>
            <div v-if="errors.name" class="form-error">
              {{ errors.name[0] }}
            </div>
          </div>

          <div class="form-group">
            <label for="studentNo" class="form-label">Student No.</label>
            <div class="input-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                <path
                  d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
              </svg>
              <input type="text" id="studentNo" v-model="form.studentNo" required class="form-input"
                :class="{ 'form-input-error': errors.studentNo }" placeholder="Enter student number" />
            </div>
            <div v-if="errors.studentNo" class="form-error">
              {{ errors.studentNo[0] }}
            </div>
          </div>

          <div class="form-group">
            <label for="contact" class="form-label">Contact No.</label>
            <div class="input-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                <path
                  d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
              </svg>
              <input type="tel" id="contact" v-model="form.contact" required class="form-input"
                :class="{ 'form-input-error': errors.contact }" placeholder="Enter contact number" />
            </div>
            <div v-if="errors.contact" class="form-error">
              {{ errors.contact[0] }}
            </div>
          </div>

          <div class="form-group">
            <label for="email" class="form-label">Email address</label>
            <div class="input-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
              </svg>
              <input type="email" id="email" v-model="form.email" required class="form-input"
                :class="{ 'form-input-error': errors.email }" placeholder="Enter email address" />
            </div>
            <div v-if="errors.email" class="form-error">
              {{ errors.email[0] }}
            </div>
          </div>

          <!-- Address Information Section -->
          <div class="section-header">
            <h3 class="section-title">Address Information</h3>
          </div>

          <div class="form-group">
            <label for="hno" class="form-label">House No.</label>
            <div class="input-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                  d="M5 3a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2H5zm0 2h10v7h-2l-1 2H8l-1-2H5V5z"
                  clip-rule="evenodd" />
              </svg>
              <input type="text" id="hno" v-model="form.hno" required class="form-input"
                :class="{ 'form-input-error': errors.hno }" placeholder="Enter house number" />
            </div>
            <div v-if="errors.hno" class="form-error">
              {{ errors.hno[0] }}
            </div>
          </div>

          <div class="form-group">
            <label for="street" class="form-label">Street</label>
            <div class="input-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                  d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                  clip-rule="evenodd" />
              </svg>
              <input type="text" id="street" v-model="form.street" class="form-input"
                :class="{ 'form-input-error': errors.street }" placeholder="Enter street name" />
            </div>
            <div v-if="errors.street" class="form-error">
              {{ errors.street[0] }}
            </div>
          </div>

          <div class="form-group">
            <label for="city" class="form-label">City</label>
            <div class="input-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                <path
                  d="M10 2.5a7.5 7.5 0 017.5 7.5c0 2.982-1.5 5-3 6.5s-2 3.5-4.5 3.5-3.5-1.5-4.5-3.5-3-3.518-3-6.5A7.5 7.5 0 0110 2.5z" />
              </svg>
              <input type="text" id="city" v-model="form.city" class="form-input"
                :class="{ 'form-input-error': errors.city }" placeholder="Enter city" />
            </div>
            <div v-if="errors.city" class="form-error">
              {{ errors.city[0] }}
            </div>
          </div>

          <div class="form-group">
            <label for="province" class="form-label">Province</label>
            <div class="input-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                  d="M10 2.5a7.5 7.5 0 017.5 7.5c0 2.982-1.5 5-3 6.5s-2 3.5-4.5 3.5-3.5-1.5-4.5-3.5-3-3.518-3-6.5A7.5 7.5 0 0110 2.5z"
                  clip-rule="evenodd" />
              </svg>
              <input type="text" id="province" v-model="form.province" class="form-input"
                :class="{ 'form-input-error': errors.province }" placeholder="Enter province" />
            </div>
            <div v-if="errors.province" class="form-error">
              {{ errors.province[0] }}
            </div>
          </div>

          <!-- Account Information Section -->
          <div class="section-header">
            <h3 class="section-title">Account Information</h3>
          </div>

          <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <div class="input-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                  d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                  clip-rule="evenodd" />
              </svg>
              <input :type="showPassword ? 'text' : 'password'" id="password" v-model="form.password" required
                class="form-input" :class="{ 'form-input-error': errors.password }" placeholder="Create password"
                @input="checkPasswordStrength" />
              <button type="button" class="password-toggle" @click="showPassword = !showPassword">
                <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" class="eye-icon" viewBox="0 0 20 20"
                  fill="currentColor">
                  <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                  <path fill-rule="evenodd"
                    d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                    clip-rule="evenodd" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="eye-icon" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd"
                    d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z"
                    clip-rule="evenodd" />
                  <path
                    d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
                </svg>
              </button>
            </div>

            <!-- Password Strength Indicator -->
            <div v-if="form.password" class="password-strength mt-2">
              <div class="strength-bars">
                <div class="strength-bar" :class="getStrengthClass(0)"></div>
                <div class="strength-bar" :class="getStrengthClass(1)"></div>
                <div class="strength-bar" :class="getStrengthClass(2)"></div>
                <div class="strength-bar" :class="getStrengthClass(3)"></div>
              </div>
              <div class="strength-text" :class="passwordStrength.class">
                {{ passwordStrength.text }}
              </div>
            </div>

            <div v-if="errors.password" class="form-error">
              {{ errors.password[0] }}
            </div>
          </div>

          <div class="form-group">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <div class="input-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                  d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                  clip-rule="evenodd" />
              </svg>
              <input :type="showConfirmPassword ? 'text' : 'password'" id="password_confirmation"
                v-model="form.password_confirmation" required class="form-input"
                :class="{ 'form-input-error': errors.password_confirmation }" placeholder="Confirm your password" />
              <button type="button" class="password-toggle" @click="showConfirmPassword = !showConfirmPassword">
                <svg v-if="showConfirmPassword" xmlns="http://www.w3.org/2000/svg" class="eye-icon" viewBox="0 0 20 20"
                  fill="currentColor">
                  <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                  <path fill-rule="evenodd"
                    d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                    clip-rule="evenodd" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="eye-icon" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd"
                    d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z"
                    clip-rule="evenodd" />
                  <path
                    d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
                </svg>
              </button>
            </div>
            <div v-if="errors.password_confirmation" class="form-error">
              {{ errors.password_confirmation[0] }}
            </div>
          </div>

          <div class="form-group">
            <label for="grade_level" class="form-label">Grade Level (Optional)</label>
            <select id="grade_level" v-model="form.grade_level" class="form-input"
              :class="{ 'form-input-error': errors.grade_level }">
              <option value="">Select grade level</option>
              <option value="Grade 7">Grade 7</option>
              <option value="Grade 8">Grade 8</option>
              <option value="Grade 9">Grade 9</option>
              <option value="Grade 10">Grade 10</option>
              <option value="Grade 11">Grade 11</option>
              <option value="Grade 12">Grade 12</option>
            </select>
            <div v-if="errors.grade_level" class="form-error">
              {{ errors.grade_level[0] }}
            </div>
          </div>

          <button type="submit" class="btn btn-primary w-full" :disabled="loading || passwordStrength.score < 2">
            <span v-if="loading" class="btn-loading">
              <svg class="spinner" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
              </svg>
              Creating account...
            </span>
            <span v-else>Sign up</span>
          </button>

          <div class="text-center pt-4">
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
  </div>
</template>

<script>
import { useAuthStore } from '../../stores/auth'
import { ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'

export default {
  name: 'Register',
  setup() {
    const authStore = useAuthStore()
    const router = useRouter()

    const form = ref({
      name: '',
      studentNo: '',
      contact: '',
      email: '',
      hno: '',
      street: '',
      city: '',
      province: '',
      password: '',
      password_confirmation: '',
      grade_level: ''
    })

    const loading = ref(false)
    const errorMessage = ref('')
    const errors = ref({})
    const showPassword = ref(false)
    const showConfirmPassword = ref(false)

    // Password strength state
    const passwordStrength = reactive({
      score: 0,
      text: '',
      class: ''
    })

    // Check password strength
    const checkPasswordStrength = () => {
      const password = form.value.password
      if (!password) {
        passwordStrength.score = 0
        passwordStrength.text = ''
        passwordStrength.class = ''
        return
      }

      let score = 0

      // Length check
      if (password.length >= 8) score++

      // Contains lowercase
      if (/[a-z]/.test(password)) score++

      // Contains uppercase
      if (/[A-Z]/.test(password)) score++

      // Contains numbers
      if (/[0-9]/.test(password)) score++

      // Contains special characters
      if (/[^A-Za-z0-9]/.test(password)) score++

      // Determine strength text and class
      let text, strengthClass
      switch (score) {
        case 0:
        case 1:
          text = 'Very Weak'
          strengthClass = 'very-weak'
          break
        case 2:
          text = 'Weak'
          strengthClass = 'weak'
          break
        case 3:
          text = 'Medium'
          strengthClass = 'medium'
          break
        case 4:
          text = 'Strong'
          strengthClass = 'strong'
          break
        case 5:
          text = 'Very Strong'
          strengthClass = 'very-strong'
          break
        default:
          text = ''
          strengthClass = ''
      }

      passwordStrength.score = score
      passwordStrength.text = text
      passwordStrength.class = strengthClass
    }

    // Get strength class for individual bars
    const getStrengthClass = (index) => {
      if (index < passwordStrength.score) {
        return passwordStrength.class
      }
      return ''
    }

    const handleRegister = async () => {
      loading.value = true
      errorMessage.value = ''
      errors.value = {}

      try {
        const result = await authStore.register(form.value)

        if (result.success) {
          alert('Registration successful. Please wait for admin approval.')
          router.push('/login?message=Registration successful. Please wait for admin approval.')
        } else {
          errorMessage.value = result.message
          if (result.errors) {
            errors.value = result.errors
          }
        }
      } catch (error) {
        errorMessage.value = 'An unexpected error occurred. Please try again.'
        console.error('Registration error:', error)
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      loading,
      errorMessage,
      errors,
      showPassword,
      showConfirmPassword,
      passwordStrength,
      checkPasswordStrength,
      getStrengthClass,
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

.py-8 {
  padding-top: 2rem;
  padding-bottom: 2rem;
}

.px-4 {
  padding-left: 1rem;
  padding-right: 1rem;
}

.max-w-md {
  max-width: 28rem;
}

.w-full {
  width: 100%;
}

.space-y-6>*+* {
  margin-top: 1.5rem;
}

.text-center {
  text-align: center;
}

.text-3xl {
  font-size: 1.875rem;
  line-height: 2.25rem;
}

.font-bold {
  font-weight: 700;
}

.text-gray-900 {
  color: #1f2937;
}

.mb-2 {
  margin-bottom: 0.5rem;
}

.text-gray-600 {
  color: #6b7280;
}

.bg-white {
  background-color: white;
}

.p-6 {
  padding: 1.5rem;
}

.rounded-lg {
  border-radius: 0.5rem;
}

.shadow-md {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.space-y-4>*+* {
  margin-top: 1rem;
}

.section-header {
  border-bottom: 2px solid #e5e7eb;
  padding-bottom: 0.5rem;
  margin-bottom: 1rem;
}

.section-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #374151;
}

.form-group {
  margin-bottom: 1rem;
}

.form-label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #374151;
  font-size: 0.875rem;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 0.75rem;
  width: 1.25rem;
  height: 1.25rem;
  color: #9ca3af;
  z-index: 10;
}

.form-input {
  display: block;
  width: 100%;
  padding: 0.75rem 0.75rem 0.75rem 2.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 1rem;
  line-height: 1.5;
  transition: all 0.2s;
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
  font-size: 0.75rem;
  margin-top: 0.25rem;
}

.password-toggle {
  position: absolute;
  right: 0.75rem;
  background: none;
  border: none;
  cursor: pointer;
  color: #9ca3af;
  padding: 0.25rem;
}

.eye-icon {
  width: 1.25rem;
  height: 1.25rem;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 0.5rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 1rem;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background-color: #007bff;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background-color: #0056b3;
  transform: translateY(-1px);
}

.btn-loading {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.spinner {
  width: 1.25rem;
  height: 1.25rem;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(360deg);
  }
}

.pt-4 {
  padding-top: 1rem;
}

.text-sm {
  font-size: 0.875rem;
}

.link {
  color: #007bff;
  text-decoration: none;
  font-weight: 500;
}

.link:hover {
  text-decoration: underline;
}

.alert {
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
}

.alert-error {
  background-color: #fef2f2;
  color: #dc3545;
  border: 1px solid #fecaca;
}

.alert-icon {
  width: 1.25rem;
  height: 1.25rem;
  flex-shrink: 0;
}

/* Mobile-specific styles */
@media (max-width: 480px) {
  .py-8 {
    padding-top: 1rem;
    padding-bottom: 1rem;
  }

  .p-6 {
    padding: 1rem;
  }

  .text-3xl {
    font-size: 1.5rem;
  }

  .btn {
    padding: 1rem;
  }
}

/* Reduced motion for accessibility */
@media (prefers-reduced-motion: reduce) {

  .btn,
  .form-input {
    transition: none;
  }

  .spinner {
    animation: none;
  }
}

/* ... (keep all your existing styles) ... */

/* Password Strength Indicator Styles */
.password-strength {
  margin-top: 0.5rem;
}

.strength-bars {
  display: flex;
  gap: 2px;
  margin-bottom: 0.25rem;
}

.strength-bar {
  height: 4px;
  flex: 1;
  background-color: #e5e7eb;
  border-radius: 2px;
  transition: all 0.3s ease;
}

.strength-bar.very-weak {
  background-color: #dc3545;
}

.strength-bar.weak {
  background-color: #fd7e14;
}

.strength-bar.medium {
  background-color: #ffc107;
}

.strength-bar.strong {
  background-color: #28a745;
}

.strength-bar.very-strong {
  background-color: #20c997;
}

.strength-text {
  font-size: 0.75rem;
  font-weight: 500;
  text-align: right;
}

.strength-text.very-weak {
  color: #dc3545;
}

.strength-text.weak {
  color: #fd7e14;
}

.strength-text.medium {
  color: #ffc107;
}

.strength-text.strong {
  color: #28a745;
}

.strength-text.very-strong {
  color: #20c997;
}

/* Disable button when password is weak */
.btn:disabled {
  background-color: #6c757d;
  cursor: not-allowed;
}

.btn:disabled:hover {
  background-color: #6c757d;
  transform: none;
}
</style>