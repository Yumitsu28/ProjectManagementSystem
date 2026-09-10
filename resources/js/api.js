import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Add token to requests if it exists
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

export const authAPI = {
    // Register
    register(data) {
        return api.post('/auth/register', data);
    },

    // Login
    login(data) {
        return api.post('/auth/login', data);
    },

    // Get current user
    getUser() {
        return api.get('/auth/user');
    },

    // Logout
    logout() {
        return api.post('/auth/logout');
    },
};

export const projectAPI = {
    // Get all projects
    getProjects() {
        return api.get('/projects');
    },

    // Get single project
    getProject(id) {
        return api.get(`/projects/${id}`);
    },

    // Create project
    createProject(data) {
        return api.post('/projects', data);
    },

    // Update project
    updateProject(id, data) {
        return api.put(`/projects/${id}`, data);
    },

    // Delete project
    deleteProject(id) {
        return api.delete(`/projects/${id}`);
    },
};

export default api;
