<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50">
        <div class="max-w-md w-full space-y-8 p-8 bg-white rounded-lg shadow">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Create your account
                </h2>
            </div>
            <form class="mt-8 space-y-6" @submit.prevent="handleRegister">
                <div class="rounded-md shadow-sm space-y-4">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input v-model="form.name" id="name" name="name" type="text" required
                            class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="Full name" :class="{ 'is-invalid': errors.name }">
                        <div class="invalid-feedback " v-if="errors.name">
                            <span class="text-red-500"> {{ errors.name[0] }} </span>
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input v-model="form.email" id="email" name="email" type="email" required
                            class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="Email address" :class="{ 'is-invalid': errors.email }">
                        <div class="invalid-feedback" v-if="errors.email">
                            <span class="text-red-500"> {{ errors.email[0] }} </span>
                        </div>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                        <input v-model="form.phone" id="phone" name="phone" type="tel"
                            class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="Phone number" :class="{ 'is-invalid': errors.phone }">
                        <div class="invalid-feedback" v-if="errors.phone">
                            <span class="text-red-500"> {{ errors.phone[0] }} </span>
                        </div>
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea v-model="form.address" id="address" name="address"
                            class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="Your address" :class="{ 'is-invalid': errors.address }"></textarea>
                        <div class="invalid-feedback" v-if="errors.address">
                            <span class="text-red-500">{{ errors.address[0] }} </span>
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input v-model="form.password" id="password" name="password" type="password" required
                            class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="Password" :class="{ 'is-invalid': errors.password }">
                        <div class="invalid-feedback text-danger" v-if="errors.password">
                            <span class="text-red-500"> {{ errors.password[0] }} </span>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <input v-model="form.password_confirmation" id="password_confirmation" name="password_confirmation"
                            type="password" required
                            class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="Confirm password" :class="{ 'is-invalid': errors.password_confirmation }">
                        <div class="invalid-feedback" v-if="errors.password_confirmation">
                            <span class="text-red-500"> {{ errors.password_confirmation[0] }} </span>
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Register
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, onMounted, ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()
const form = reactive({
    name: '',
    email: '',
    phone: '',
    address: '',
    password: '',
    password_confirmation: ''
})

const errors = ref({})

onMounted(async () => {
    await axios.get('/sanctum/csrf-cookie')
})

const handleRegister = async () => {
    try {
        errors.value = {}
        // First get CSRF cookie
        await axios.get('/sanctum/csrf-cookie');
        
        // Then make the registration request
        const response = await axios.post('/api/register', form);
        console.log('Registration response:', response.data);
        
        if (response.data.token) {
            localStorage.setItem('token', response.data.token);
             // Emit auth state change event
        
        
        // Navigate to dashboard
        router.push('/dashboard');
        window.location.reload();
        }
    } catch (error) {
        console.error('Registration error:', error);
        console.error('Error response:', error.response?.data);
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        }
    }
}
</script> 