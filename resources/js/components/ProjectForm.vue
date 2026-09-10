<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <div class="sticky top-0 bg-white border-b p-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-900">
          {{ project ? 'Edit Project' : 'Create New Project' }}
        </h2>
        <button
          @click="$emit('close')"
          class="text-gray-500 hover:text-gray-700 transition"
        >
          ✕
        </button>
      </div>

      <form @submit.prevent="submitForm" class="p-6 space-y-6">
        <!-- Error Messages -->
        <div v-if="errors" class="bg-red-50 border border-red-200 rounded-lg p-4">
          <p class="text-red-800 font-semibold mb-2">Validation Errors:</p>
          <ul class="text-red-700 space-y-1">
            <li v-for="(messages, field) in errors" :key="field">
              <strong>{{ field }}:</strong> {{ Array.isArray(messages) ? messages.join(', ') : messages }}
            </li>
          </ul>
        </div>

        <!-- Client Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Client Name *
          </label>
          <input
            v-model="formData.client_name"
            type="text"
            required
            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Enter client name"
          />
        </div>

        <!-- Project Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Project Name *
          </label>
          <input
            v-model="formData.project_name"
            type="text"
            required
            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Enter project name"
          />
        </div>

        <!-- Description -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Description
          </label>
          <textarea
            v-model="formData.description"
            rows="4"
            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Enter project description"
          ></textarea>
        </div>

        <!-- Status and Priority -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Status *
            </label>
            <select
              v-model="formData.status"
              required
              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="Planning">Planning</option>
              <option value="In Progress">In Progress</option>
              <option value="On Hold">On Hold</option>
              <option value="Completed">Completed</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Priority *
            </label>
            <select
              v-model="formData.priority"
              required
              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="Low">Low</option>
              <option value="Medium">Medium</option>
              <option value="High">High</option>
            </select>
          </div>
        </div>

        <!-- Start Date and Due Date -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Start Date *
            </label>
            <input
              v-model="formData.start_date"
              type="date"
              required
              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Due Date *
            </label>
            <input
              v-model="formData.due_date"
              type="date"
              required
              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>

        <!-- Buttons -->
        <div class="flex justify-end gap-4 pt-6 border-t">
          <button
            @click="$emit('close')"
            type="button"
            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
          >
            Cancel
          </button>
          <button
            :disabled="submitting"
            type="submit"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ submitting ? 'Saving...' : 'Save Project' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  project: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['save', 'close']);

const submitting = ref(false);
const errors = ref(null);

const formData = ref({
  client_name: '',
  project_name: '',
  description: '',
  status: 'Planning',
  priority: 'Medium',
  start_date: '',
  due_date: '',
});

// Initialize form data when component mounts or project changes
watch(
  () => props.project,
  (newProject) => {
    if (newProject) {
      formData.value = { ...newProject };
    } else {
      resetForm();
    }
    errors.value = null;
  },
  { immediate: true }
);

const resetForm = () => {
  formData.value = {
    client_name: '',
    project_name: '',
    description: '',
    status: 'Planning',
    priority: 'Medium',
    start_date: '',
    due_date: '',
  };
};

const submitForm = async () => {
  submitting.value = true;
  errors.value = null;

  try {
    emit('save', formData.value);
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      console.error('Error:', error);
    }
  } finally {
    submitting.value = false;
  }
};
</script>
