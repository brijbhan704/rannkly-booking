<template>
    <div class="container mx-auto p-4">
        <h2 class="text-2xl font-bold mb-4">Book Appointment</h2>
        
        <!-- Success Message -->
        <div v-if="successMessage" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ successMessage }}
        </div>

        <!-- Error Message -->
        <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ error }}
        </div>
        
        <form @submit.prevent="handleSubmit" class="space-y-4">
            <div>
                <label class="block mb-1">Title</label>
                <input 
                    v-model="form.title" 
                    type="text" 
                    class="w-full p-2 border rounded"
                    required
                >
            </div>

            <div>
                <label class="block mb-1">Description</label>
                <textarea 
                    v-model="form.description" 
                    class="w-full p-2 border rounded"
                    required
                ></textarea>
            </div>

            <div>
                <label class="block mb-1">Date & Time</label>
                <input 
                    v-model="form.start_time" 
                    type="datetime-local" 
                    class="w-full p-2 border rounded"
                    :min="minDateTime"
                    required
                >
            </div>

            <div class="mb-4">
                <label class="block mb-1">Reminder Preference</label>
                <select 
                    v-model="form.reminder_minutes"
                    class="w-full p-2 border rounded"
                >
                    <option value="30">30 minutes before</option>
                    <option value="60">1 hour before</option>
                    <option value="120">2 hours before</option>
                    <option value="1440">1 day before</option>
                </select>
            </div>

            <div>
                <label class="block mb-1">Guests (Email addresses)</label>
                <div v-for="(guest, index) in form.guests" :key="index" class="flex gap-2 mb-2">
                    <input 
                        v-model="form.guests[index]" 
                        type="email" 
                        class="flex-1 p-2 border rounded"
                        placeholder="guest@example.com"
                    >
                    <button 
                        type="button" 
                        @click="removeGuest(index)"
                        class="px-3 py-2 bg-red-500 text-white rounded"
                    >
                        Remove
                    </button>
                </div>
                <button 
                    type="button" 
                    @click="addGuest"
                    class="px-3 py-2 bg-blue-500 text-white rounded"
                >
                    Add Guest
                </button>
            </div>

            <div class="mt-4">
                <button 
                    type="submit" 
                    class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
                    :disabled="loading"
                >
                    {{ loading ? 'Booking...' : 'Book Appointment' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from '../../axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const loading = ref(false);
const error = ref('');
const successMessage = ref('');

// Compute minimum allowed date/time (current time)
const minDateTime = computed(() => {
    const now = new Date();
    return now.toISOString().slice(0, 16); // Format: YYYY-MM-DDTHH:mm
});

const form = ref({
    title: '',
    description: '',
    start_time: '',
    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
    guests: [],
    reminder_minutes: 30 // Default to 30 minutes
});

const addGuest = () => {
    form.value.guests.push('');
};

const removeGuest = (index) => {
    form.value.guests.splice(index, 1);
};

const handleSubmit = async () => {
    try {
        loading.value = true;
        error.value = '';
        successMessage.value = '';
        
        // Check if selected time is in the past
        if (new Date(form.value.start_time) < new Date()) {
            error.value = 'Cannot book appointments in the past';
            return;
        }
        
        // Filter out empty guest emails
        form.value.guests = form.value.guests.filter(email => email.trim());
        
        const response = await axios.post('/appointments', form.value);
        
        // Show success message
        successMessage.value = 'Appointment booked successfully!';
        
        // Reset form
        form.value = {
            title: '',
            description: '',
            start_time: '',
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            guests: [],
            reminder_minutes: 30 // Default to 30 minutes
        };

        // Redirect after a short delay
        setTimeout(() => {
            router.push('/appointments');
        }, 2000); // 2 second delay before redirect

    } catch (e) {
        error.value = e.response?.data?.message || 'An error occurred';
        console.error('API Error:', e.response?.data);
    } finally {
        loading.value = false;
    }
};
</script> 