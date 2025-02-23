<template>
    <nav class="bg-gray-800 p-4">
        <div class="container mx-auto flex justify-between">
            <router-link to="/" class="text-white">Home</router-link>
            <div>
                <!-- Show these links only when user is NOT authenticated -->
                <template v-if="!isAuthenticated">
                    <router-link to="/login" class="text-white mx-2">Login</router-link>
                    <router-link to="/register" class="text-white mx-2">Register</router-link>
                </template>
                
                <!-- Show these links only when user is authenticated -->
                <template v-if="isAuthenticated">
                    <router-link to="/dashboard" class="text-white mx-2">Dashboard</router-link>
                    <button @click="handleLogout" class="text-white mx-2">Logout</button>
                </template>
            </div>
        </div>
    </nav>
    <router-view></router-view>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const isAuthenticated = ref(false);

// Check authentication status
const checkAuth = () => {
    const token = localStorage.getItem('token');
    isAuthenticated.value = !!token;
};

// Handle logout
const handleLogout = () => {
    localStorage.removeItem('token');
    isAuthenticated.value = false;
    router.push('/login');
};

// Watch for authentication changes
onMounted(() => {
    checkAuth();
    // Add event listener for storage changes (in case of multiple tabs)
    window.addEventListener('storage', checkAuth);
});

// Create a custom event bus or use your state management solution
window.addEventListener('login', () => {
    checkAuth();
});
</script> 