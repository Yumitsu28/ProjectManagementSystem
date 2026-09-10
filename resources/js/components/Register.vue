<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8">
      <h1 class="text-3xl font-bold text-center mb-2 text-gray-900">Create Account</h1>
      <p class="text-center text-gray-600 mb-8">Join Project Tracker today</p>

      <!-- Error Messages -->
      <div v-if="errors" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <p class="text-red-800 font-semibold mb-2">Registration Error:</p>
        <ul class="text-red-700 text-sm space-y-1">
          <li v-for="(error, key) in errors" :key="key">
            <strong>{{ key }}:</strong> {{ Array.isArray(error) ? error.join(', ') : error }}
          </li>
        </ul>
      </div>

      <form @submit.prevent="handleRegister" class="space-y-4">
        <!-- Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
          <input
            v-model="formData.name"
            type="text"
            required
            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="John Doe"
          />
        </div>

        <!-- Email -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
          <input
            v-model="formData.email"
            type="email"
            required
            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="your@email.com"
          />
        </div>

        <!-- Password -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
          <input
            v-model="formData.password"
            type="password"
            required
            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="••••••••"
          />
          <p class="text-xs text-gray-500 mt-1">At least 8 characters</p>
        </div>

        <!-- Confirm Password -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
          <input
            v-model="formData.password_confirmation"
            type="password"
            required
            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="••••••••"
          />
        </div>

        <!-- Submit Button -->
        <button
          :disabled="loading"
          type="submit"
          class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed font-medium"
        >
          {{ loading ? 'Creating account...' : 'Create Account' }}
        </button>
      </form>

      <!-- Login Link -->
      <p class="text-center text-sm text-gray-600 mt-6">
        Already have an account?
        <button
          @click="$emit('switch-form', 'login')"
          class="text-blue-600 hover:text-blue-800 font-medium"
        >
          Sign in
        </button>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { authAPI } from '../api';

const emit = defineEmits(['authenticated', 'switch-form']);

const formData = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const loading = ref(false);
const errors = ref(null);

const handleRegister = async () => {
  loading.value = true;
  errors.value = null;

  try {
    const response = await authAPI.register(formData.value);
    localStorage.setItem('auth_token', response.data.token);
    localStorage.setItem('user', JSON.stringify(response.data.user));
    emit('authenticated', response.data.user);
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      errors.value = { general: 'Registration failed. Please try again.' };
    }
  } finally {
    loading.value = false;
  }
};
</script>
