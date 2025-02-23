<template>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6">Dashboard</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Appointments Card -->
            <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Appointments</h2>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>

                <div class="space-y-4">
                    <router-link 
                        to="/view-appointments"
                        class="block p-3 bg-green-50 rounded-lg hover:bg-green-100 transition-colors"
                    >
                        <div class="flex items-center justify-between">
                            <span>View Appointments</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>

                           
                        </div>
                    </router-link>
                    <router-link 
                        to="/appointments"
                        class="block p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"
                    >
                        <div class="flex items-center justify-between">
                            <span>Book Appointments</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </router-link>

            
                </div>

                <div class="mt-4 text-sm text-gray-600">
                    Manage your appointments and schedule new meetings
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Quick Stats</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <span>Upcoming Appointments</span>
                        <span class="font-semibold text-blue-600">{{ upcomingCount }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <span>Total Appointments</span>
                        <span class="font-semibold text-gray-600">{{ totalCount }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '../axios';

const upcomingCount = ref(0);
const totalCount = ref(0);

const loadStats = async () => {
    try {
        const response = await axios.get('/appointments', {
            params: { sort: 'upcoming' }
        });
        
        // Count upcoming appointments (future dates)
        upcomingCount.value = response.data.filter(apt => 
            new Date(apt.start_time) > new Date()
        ).length;
        
        // Total appointments
        totalCount.value = response.data.length;
        const token = localStorage.getItem('token');
        if(token){
            console.log(token);
            //window.location.href = '/dashboard';
        }
    } catch (error) {
        console.error('Error loading appointment stats:', error);
    }
};

onMounted(() => {
    loadStats();
});
</script>

<style scoped>
.router-link-active {
    @apply bg-opacity-75;
}
</style>