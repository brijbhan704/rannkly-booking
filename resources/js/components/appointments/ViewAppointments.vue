<template>
    <div class="container mx-auto p-4">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">My Appointments</h2>
            <div class="flex gap-4 items-center">
                <!-- Filter Options -->
                <select 
                    v-model="filterBy" 
                    class="border rounded px-3 py-2 text-gray-700"
                    @change="loadAppointments"
                >
                    <option value="all">All Appointments</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="past">Past</option>
                </select>

                <!-- Sort Options -->
                <select 
                    v-model="sortBy" 
                    class="border rounded px-3 py-2 text-gray-700"
                    @change="loadAppointments"
                >
                    <option value="start_time">Sort by Date</option>
                    <option value="created_at">Sort by Created Date</option>
                </select>

                <!-- View Toggle -->
                <div class="flex bg-gray-100 rounded-lg p-1">
                    <button 
                        @click="viewType = 'list'"
                        :class="[
                            'px-4 py-2 rounded-lg',
                            viewType === 'list' 
                                ? 'bg-white shadow-sm text-blue-600' 
                                : 'text-gray-600'
                        ]"
                    >
                        <i class="fas fa-list"></i> List
                    </button>
                    <button 
                        @click="viewType = 'grid'"
                        :class="[
                            'px-4 py-2 rounded-lg',
                            viewType === 'grid' 
                                ? 'bg-white shadow-sm text-blue-600' 
                                : 'text-gray-600'
                        ]"
                    >
                        <i class="fas fa-grid"></i> Grid
                    </button>
                </div>

                <router-link 
                    to="/appointments" 
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                >
                    Book New Appointment
                </router-link>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-8">
            Loading appointments...
        </div>

        <!-- Error State -->
        <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ error }}
        </div>

        <!-- No Appointments -->
        <div v-if="!loading && appointments.length === 0" class="text-center py-8">
            No appointments found. 
            <router-link to="/appointments" class="text-blue-500 hover:underline">
                Book your first appointment
            </router-link>
        </div>

        <!-- List View -->
        <div v-if="!loading && appointments.length > 0 && viewType === 'list'" class="space-y-4">
            <div v-for="appointment in appointments" 
                :key="appointment.id" 
                class="border rounded-lg p-4 hover:shadow-lg transition-shadow"
            >
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-xl font-semibold">{{ appointment.title }}</h3>
                        <p class="text-gray-600">{{ appointment.description }}</p>
                    </div>
                    <div class="text-right">
                        <div :class="getStatusClass(appointment)">
                            {{ getStatusText(appointment) }}
                        </div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Date & Time</p>
                        <p>{{ formatDateTime(appointment.start_time) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Created</p>
                        <p>{{ formatDate(appointment.created_at) }}</p>
                    </div>
                </div>

                <div v-if="appointment.guests.length > 0" class="mt-4">
                    <p class="text-sm text-gray-500">Guests:</p>
                    <div class="flex flex-wrap gap-2 mt-1">
                        <span 
                            v-for="guest in appointment.guests" 
                            :key="guest.id"
                            class="bg-gray-100 px-2 py-1 rounded text-sm"
                        >
                            {{ guest.email }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button 
                        v-if="canShowCancelButton(appointment)"
                        @click="cancelAppointment(appointment)"
                        class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition-colors"
                        :disabled="!canCancel(appointment)"
                    >
                        Cancel Appointment
                    </button>
                </div>
            </div>
        </div>

        <!-- Grid View -->
        <div v-if="!loading && appointments.length > 0 && viewType === 'grid'" 
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
        >
            <div v-for="appointment in appointments" 
                :key="appointment.id" 
                class="border rounded-lg p-4 hover:shadow-lg transition-shadow bg-white"
            >
                <div class="flex justify-between items-start mb-3">
                    <h3 class="text-lg font-semibold truncate">{{ appointment.title }}</h3>
                    <div :class="getStatusClass(appointment)">
                        {{ getStatusText(appointment) }}
                    </div>
                </div>

                <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ appointment.description }}</p>

                <div class="text-sm space-y-2">
                    <div>
                        <span class="text-gray-500">Date & Time:</span><br>
                        {{ formatDateTime(appointment.start_time) }}
                    </div>
                    
                    <div>
                        <span class="text-gray-500">Created:</span><br>
                        {{ formatDate(appointment.created_at) }}
                    </div>

                    <div v-if="appointment.guests?.length > 0">
                        <span class="text-gray-500">Guests:</span>
                        <div class="flex flex-wrap gap-1 mt-1">
                            <span 
                                v-for="guest in appointment.guests" 
                                :key="guest.id"
                                class="bg-gray-100 px-2 py-0.5 rounded text-xs"
                            >
                                {{ guest.email }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Cancel Button -->
                <div class="mt-4 flex justify-end">
                    <button 
                        v-if="canShowCancelButton(appointment)"
                        @click="cancelAppointment(appointment)"
                        class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition-colors text-sm"
                        :disabled="!canCancel(appointment)"
                    >
                        Cancel Appointment
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '../../axios';

const appointments = ref([]);
const loading = ref(false);
const error = ref('');
const filterBy = ref('all');
const sortBy = ref('start_time');
const viewType = ref('list');

const loadAppointments = async () => {
    try {
        loading.value = true;
        error.value = '';
        
        const response = await axios.get(`/appointments?sort=${sortBy.value}&filter=${filterBy.value}`);
        appointments.value = response.data;
    } catch (e) {
        error.value = 'Failed to load appointments';
        console.error('Error loading appointments:', e);
    } finally {
        loading.value = false;
    }
};

const formatDateTime = (datetime) => {
    return new Date(datetime).toLocaleString();
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

const isUpcoming = (appointment) => {
    return new Date(appointment.start_time) > new Date();
};

const getStatusText = (appointment) => {
    if (appointment.status === 'cancelled') {
        return 'Cancelled';
    }
    return isUpcoming(appointment) ? 'Upcoming' : 'Past';
};

const getStatusClass = (appointment) => {
    if (appointment.status === 'cancelled') {
        return 'text-red-600 bg-red-100 px-2 py-1 rounded text-sm';
    }
    return isUpcoming(appointment)
        ? 'text-green-600 bg-green-100 px-2 py-1 rounded text-sm'
        : 'text-gray-600 bg-gray-100 px-2 py-1 rounded text-sm';
};

const canShowCancelButton = (appointment) => {
    return isUpcoming(appointment) && appointment.status === 'active';
};

const canCancel = (appointment) => {
    if (appointment.status === 'cancelled') {
        return false;
    }
    const appointmentTime = new Date(appointment.start_time);
    const thirtyMinutesBefore = new Date(appointmentTime.getTime() - 30 * 60000);
    return new Date() < thirtyMinutesBefore;
};

const cancelAppointment = async (appointment) => {
    if (!confirm('Are you sure you want to cancel this appointment?')) {
        return;
    }

    try {
        loading.value = true;
        await axios.post(`/appointments/${appointment.id}/cancel`);
        await loadAppointments(); // Reload the appointments list
        
        // Show success message
        alert('Appointment cancelled successfully');
    } catch (e) {
        error.value = e.response?.data?.message || 'Failed to cancel appointment';
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadAppointments();
});

// Add these styles to help with text overflow
const gridCardStyles = {
    maxHeight: '400px',
    overflow: 'auto'
};
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Add any additional styles you need */
</style> 