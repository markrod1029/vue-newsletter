import { defineStore } from "pinia";
import apiClient from "../api/http";
export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: null,
    isAuthenticated: false,
    loading: false,
    token: localStorage.getItem("auth_token") || null,
  }),

  actions: {
    async login(credentials) {
      try {
        this.loading = true;

        // 1. Get CSRF cookie first
        await apiClient.get("/sanctum/csrf-cookie");

        // 2. Attempt login
        const response = await apiClient.post("/api/login", credentials);

        if (response.data.user && response.data.token) {
          // Store user data and token
          this.user = response.data.user;
          this.isAuthenticated = true;
          this.token = response.data.token;

          // Save to localStorage
          localStorage.setItem("auth_token", response.data.token);
          localStorage.setItem("auth_user", JSON.stringify(response.data.user));
          localStorage.setItem("auth_authenticated", "true");

          return { success: true };
        } else {
          return {
            success: false,
            message: response.data.message || "Login failed",
          };
        }
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || "Login failed",
        };
      } finally {
        this.loading = false;
      }
    },
    async register(userData) {
      try {
        this.loading = true;
        // Get CSRF cookie first
        await apiClient.get("/sanctum/csrf-cookie");

        // Attempt registration
        const response = await apiClient.post("/api/register", userData);
        return { success: true, data: response.data };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || "Registration failed",
          errors: error.response?.data?.errors,
        };
      } finally {
        this.loading = false;
      }
    },

     async logout() {
      try {
        await apiClient.post('/api/logout')
        this.user = null
        this.isAuthenticated = false
        return { success: true }
      } catch (error) {
        return { success: false }
      }
    },

    async fetchUser() {
      try {
        this.loading = true;
        const response = await apiClient.get("/api/user");

        if (response.data) {
          this.user = response.data;
          this.isAuthenticated = true;
          return { success: true };
        } else {
          this.clearAuthData();
          return { success: false };
        }
      } catch (error) {
        this.clearAuthData();
        return { success: false };
      } finally {
        this.loading = false;
      }
    },
    // Clear authentication data
    clearAuthData() {
      this.user = null;
      this.isAuthenticated = false;
      localStorage.removeItem("auth_user");
      localStorage.removeItem("auth_authenticated");
    },

    // Initialize auth state from localStorage
    initializeAuth() {
      const storedUser = localStorage.getItem("auth_user");
      const storedAuth = localStorage.getItem("auth_authenticated");

      if (storedUser && storedAuth === "true") {
        this.user = JSON.parse(storedUser);
        this.isAuthenticated = true;

        // Verify the token is still valid by fetching user data
        this.fetchUser().catch(() => {
          // If fetch fails, clear the invalid auth data
          this.clearAuthData();
        });
      }
    },
  },
});
