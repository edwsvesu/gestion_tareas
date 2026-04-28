import axios from 'axios';
import router from '../router';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
    }
});

// Request Interceptor: Añadir JWT a las peticiones
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('jwt_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Response Interceptor: Manejar expiración del JWT
api.interceptors.response.use(
    (response) => response,
    async (error) => {
        if (error.response && error.response.status === 401) {
            // Token expirado o inválido
            localStorage.removeItem('jwt_token');
            router.push({ name: 'Login' });
        }
        return Promise.reject(error);
    }
);

export default api;
