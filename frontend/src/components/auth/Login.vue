<template>
  <div class="login-container">
    <div class="login-card">
      <h2 class="login-title">Sign in to your account</h2>
      
      <form @submit.prevent="handleLogin" class="login-form">
        <div v-if="errorMessage" class="alert alert-error">
          {{ errorMessage }}
        </div>
        
        <div class="form-group">
          <label for="email" class="form-label">Email address</label>
          <input
            type="email"
            id="email"
            v-model="form.email"
            required
            class="form-input"
            :class="{ 'form-input-error': errors.email }"
            placeholder="Email"
          />
          <div v-if="errors.email" class="form-error">
            {{ errors.email[0] }}
          </div>
        </div>
        
        <div class="form-group">
          <label for="password" class="form-label">Password</label>
          <input
            type="password"
            id="password"
            v-model="form.password"
            required
            class="form-input"
            :class="{ 'form-input-error': errors.password }"
            placeholder="Password"
          />
          <div v-if="errors.password" class="form-error">
            {{ errors.password[0] }}
          </div>
        </div>
        
        <div class="form-options">
          <label class="checkbox-label">
            <input type="checkbox" id="remember-me" />
            Remember me
          </label>
          <a href="#" class="forgot-password">Forgot your password?</a>
        </div>
        
        <button type="submit" class="btn btn-primary" :disabled="loading">
          <span v-if="loading">Signing in...</span>
          <span v-else>Sign in</span>
        </button>
        
        <div class="login-footer">
          <span class="text-sm">
            Don't have an account? 
            <router-link to="/register" class="link">
              Sign up
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
  name: 'Login',
  setup() {
    const authStore = useAuthStore()
    const router = useRouter()
    
    const form = ref({
      email: '',
      password: ''
    })
    
    const loading = ref(false)
    const errorMessage = ref('')
    const errors = ref({})
    
    const handleLogin = async () => {
      loading.value = true
      errorMessage.value = ''
      errors.value = {}
      
      const result = await authStore.login(form.value)
      
      if (result.success) {
        router.push('/')
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
      handleLogin
    }
  }
}
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f8f9fa;
  padding: 1rem;
}

.login-card {
  background: white;
  padding: 2rem;
  border-radius: 0.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
}

.login-title {
  text-align: center;
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 1.5rem;
  color: #1f2937;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-label {
  font-weight: 500;
  margin-bottom: 0.5rem;
  color: #374151;
}

.form-input {
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-size: 1rem;
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

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.875rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.forgot-password {
  color: #007bff;
  text-decoration: none;
}

.forgot-password:hover {
  text-decoration: underline;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 0.375rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-primary {
  background-color: #007bff;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background-color: #0056b3;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.login-footer {
  text-align: center;
  margin-top: 1rem;
}

.link {
  color: #007bff;
  text-decoration: none;
}

.link:hover {
  text-decoration: underline;
}

.alert {
  padding: 0.75rem;
  border-radius: 0.375rem;
  margin-bottom: 1rem;
}

.alert-error {
  background-color: #fee2e2;
  color: #dc3545;
  border: 1px solid #fecaca;
}
</style>