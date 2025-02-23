import './bootstrap.js';
import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import Login from './components/auth/Login.vue'
import Register from './components/auth/Register.vue'
import Dashboard from './components/Dashboard.vue';
import Appointments from './components/appointments/CreateAppointment.vue';
import ViewAppointments from './components/appointments/ViewAppointments.vue';
import ViewAppointment from './components/appointments/ViewAppointment.vue';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { 
            path: '/', 
            redirect: { name: 'dashboard' }  // Redirect root to dashboard
        },
        { 
            path: '/login', 
            component: Login,
            name: 'login',
            meta: { requiresGuest: true }  // Only for non-authenticated users
        },
        { 
            path: '/register', 
            component: Register,
            name: 'register',
            meta: { requiresGuest: true }  // Only for non-authenticated users
        },
        {
            path: '/dashboard',
            component: Dashboard,
            name: 'dashboard',
            meta: { requiresAuth: true }  // Requires authentication
        },
        {
            path: '/appointments',
            component: Appointments,
            name: 'appointments',
            meta: { requiresAuth: true }
        },
        {
            path: '/view-appointments',
            component: ViewAppointments,
            name: 'view-appointments',
            meta: { requiresAuth: true }
        },
        {
            path: '/appointments/:id',
            component: ViewAppointment,
            name: 'view-appointment',
            meta: { requiresAuth: true }
        },
        // Catch all 404
        { 
            path: '/:pathMatch(.*)*', 
            redirect: { name: 'login' }
        }
    ]
})

// Navigation guard
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('token');
    
    // If route requires auth and no token exists
    if (to.meta.requiresAuth && !token) {
        next({ name: 'login' });
        return;
    }
    
    // If route requires guest and token exists
    if (to.meta.requiresGuest && token) {
        next({ name: 'dashboard' });
        return;
    }
    
    next();
});

const app = createApp(App)
app.use(router)
app.mount('#app') 