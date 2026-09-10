<template>
  <div v-if="!isAuthenticated" class="min-h-screen bg-gray-50">
    <Login v-if="currentForm === 'login'" @authenticated="handleAuthenticated" @switch-form="currentForm = $event" />
    <Register v-else @authenticated="handleAuthenticated" @switch-form="currentForm = $event" />
  </div>

  <div v-else class="min-h-screen bg-gray-50">
    <nav class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <h1 class="text-2xl font-bold text-gray-900">Project Tracker</h1>
          </div>
          <div class="flex items-center gap-4">
            <span class="text-gray-600">Welcome, {{ currentUser?.name }}</span>
            <button
              @click="handleLogout"
              class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition"
            >
              Logout
            </button>
            <button
              @click="showCreateForm = true"
              class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition"
            >
              + New Project
            </button>
          </div>
        </div>
      </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <!-- Search and Filter -->
      <div class="mb-6 bg-white p-4 rounded-lg shadow">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search by client or project name..."
            class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <select
            v-model="filterStatus"
            class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Status</option>
            <option value="Planning">Planning</option>
            <option value="In Progress">In Progress</option>
            <option value="On Hold">On Hold</option>
            <option value="Completed">Completed</option>
          </select>
          <select
            v-model="filterPriority"
            class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Priority</option>
            <option value="Low">Low</option>
            <option value="Medium">Medium</option>
            <option value="High">High</option>
          </select>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <p class="text-gray-500">Loading projects...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredProjects.length === 0" class="text-center py-12 bg-white rounded-lg shadow">
        <p class="text-gray-500">No projects found. Create one to get started!</p>
      </div>

      <!-- Projects List -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-1 gap-4">
        <div
          v-for="project in filteredProjects"
          :key="project.id"
          class="bg-white rounded-lg shadow hover:shadow-lg transition p-6"
        >
          <div class="flex justify-between items-start mb-4">
            <div class="flex-1">
              <h3 class="text-lg font-bold text-gray-900">{{ project.project_name }}</h3>
              <p class="text-sm text-gray-600">Client: {{ project.client_name }}</p>
            </div>
            <div class="flex gap-2 ml-4">
              <button
                @click="editProject(project)"
                class="text-blue-600 hover:text-blue-800 transition"
              >
                Edit
              </button>
              <button
                @click="deleteProject(project.id)"
                class="text-red-600 hover:text-red-800 transition"
              >
                Delete
              </button>
            </div>
          </div>

          <p v-if="project.description" class="text-gray-700 mb-4">{{ project.description }}</p>

          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
            <div>
              <p class="text-xs text-gray-500 uppercase">Status</p>
              <p class="font-semibold">
                <span
                  :class="getStatusColor(project.status)"
                  class="px-2 py-1 rounded text-sm"
                >
                  {{ project.status }}
                </span>
              </p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase">Priority</p>
              <p class="font-semibold">
                <span
                  :class="getPriorityColor(project.priority)"
                  class="px-2 py-1 rounded text-sm"
                >
                  {{ project.priority }}
                </span>
              </p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase">Start Date</p>
              <p class="font-semibold">{{ formatDate(project.start_date) }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase">Due Date</p>
              <p class="font-semibold">{{ formatDate(project.due_date) }}</p>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Create/Edit Modal -->
    <ProjectForm
      v-if="showCreateForm || editingProject"
      :project="editingProject"
      @save="saveProject"
      @close="closeForm"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { projectAPI, authAPI } from './api';
import ProjectForm from './components/ProjectForm.vue';
import Login from './components/Login.vue';
import Register from './components/Register.vue';

const isAuthenticated = ref(false);
const currentUser = ref(null);
const currentForm = ref('login');

const projects = ref([]);
const loading = ref(false);
const showCreateForm = ref(false);
const editingProject = ref(null);
const searchQuery = ref('');
const filterStatus = ref('');
const filterPriority = ref('');

const filteredProjects = computed(() => {
  return projects.value.filter(project => {
    const matchesSearch = 
      project.client_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      project.project_name.toLowerCase().includes(searchQuery.value.toLowerCase());
    const matchesStatus = !filterStatus.value || project.status === filterStatus.value;
    const matchesPriority = !filterPriority.value || project.priority === filterPriority.value;
    
    return matchesSearch && matchesStatus && matchesPriority;
  });
});

onMounted(() => {
  checkAuthentication();
});

const checkAuthentication = () => {
  const token = localStorage.getItem('auth_token');
  const user = localStorage.getItem('user');
  
  if (token && user) {
    isAuthenticated.value = true;
    currentUser.value = JSON.parse(user);
    loadProjects();
  }
};

const handleAuthenticated = (user) => {
  currentUser.value = user;
  isAuthenticated.value = true;
  loadProjects();
};

const loadProjects = async () => {
  loading.value = true;
  try {
    const response = await projectAPI.getProjects();
    projects.value = response.data;
  } catch (error) {
    console.error('Error loading projects:', error);
  } finally {
    loading.value = false;
  }
};

const editProject = (project) => {
  editingProject.value = { ...project };
  showCreateForm.value = true;
};

const closeForm = () => {
  showCreateForm.value = false;
  editingProject.value = null;
};

const saveProject = async (projectData) => {
  try {
    if (editingProject.value) {
      await projectAPI.updateProject(editingProject.value.id, projectData);
    } else {
      await projectAPI.createProject(projectData);
    }
    closeForm();
    loadProjects();
  } catch (error) {
    console.error('Error saving project:', error);
    if (error.response?.data?.errors) {
      alert('Validation error: ' + JSON.stringify(error.response.data.errors));
    }
  }
};

const deleteProject = async (id) => {
  if (confirm('Are you sure you want to delete this project?')) {
    try {
      await projectAPI.deleteProject(id);
      loadProjects();
    } catch (error) {
      console.error('Error deleting project:', error);
    }
  }
};

const handleLogout = async () => {
  try {
    await authAPI.logout();
  } catch (error) {
    console.error('Error during logout:', error);
  } finally {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    isAuthenticated.value = false;
    currentUser.value = null;
    currentForm.value = 'login';
    projects.value = [];
  }
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const getStatusColor = (status) => {
  const colors = {
    'Planning': 'bg-blue-100 text-blue-800',
    'In Progress': 'bg-yellow-100 text-yellow-800',
    'On Hold': 'bg-orange-100 text-orange-800',
    'Completed': 'bg-green-100 text-green-800',
  };
  return colors[status] || 'bg-gray-100 text-gray-800';
};

const getPriorityColor = (priority) => {
  const colors = {
    'Low': 'bg-green-100 text-green-800',
    'Medium': 'bg-blue-100 text-blue-800',
    'High': 'bg-red-100 text-red-800',
  };
  return colors[priority] || 'bg-gray-100 text-gray-800';
};
</script>

