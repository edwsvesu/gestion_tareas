import { createRouter, createWebHistory } from 'vue-router';
import LoginView from '../views/LoginView.vue';
import RegisterView from '../views/RegisterView.vue';
import TaskListView from '../views/TaskListView.vue';

const routes = [
    {
        path: '/',
        redirect: '/tareas'
    },
    {
        path: '/login',
        name: 'Login',
        component: LoginView,
        meta: { guest: true }
    },
    {
        path: '/register',
        name: 'Register',
        component: RegisterView,
        meta: { guest: true }
    },
    {
        path: '/tareas',
        name: 'TaskListView',
        component: TaskListView,
        meta: { requiresAuth: true }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

// Navigation Guard
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('jwt_token');

    if (to.matched.some(record => record.meta.requiresAuth)) {
        if (!token) {
            next({ name: 'Login' });
        } else {
            next();
        }
    } else if (to.matched.some(record => record.meta.guest)) {
        if (token) {
            next({ name: 'TaskListView' });
        } else {
            next();
        }
    } else {
        next();
    }
});

export default router;
