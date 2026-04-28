<template>
  <div class="login-container">
    <div class="login-card">
      <h2 class="login-title">Crear Cuenta</h2>
      <form @submit.prevent="handleRegister" class="login-form">
        <div class="form-group">
          <label for="email">Correo Electrónico</label>
          <input 
            type="email" 
            id="email" 
            v-model="email" 
            required 
            placeholder="correo@ejemplo.com"
            class="form-control"
          />
        </div>
        <div class="form-group">
          <label for="password">Contraseña</label>
          <input 
            type="password" 
            id="password" 
            v-model="password" 
            required 
            placeholder="Tu contraseña"
            class="form-control"
            minlength="6"
          />
        </div>
        <div v-if="error" class="error-message">
          {{ error }}
        </div>
        <div v-if="success" class="success-message">
          {{ success }}
        </div>
        <button type="submit" class="btn btn-primary btn-block" :disabled="loading">
          {{ loading ? 'Registrando...' : 'Registrarse' }}
        </button>
        <div class="form-footer">
          ¿Ya tienes cuenta? <router-link :to="{ name: 'Login' }">Inicia sesión aquí</router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';

const email = ref('');
const password = ref('');
const error = ref('');
const success = ref('');
const loading = ref(false);
const router = useRouter();

const handleRegister = async () => {
  loading.value = true;
  error.value = '';
  success.value = '';
  
  try {
    await api.post('/register', {
      email: email.value,
      password: password.value
    });
    
    success.value = 'Registro exitoso. Redirigiendo al login...';
    
    setTimeout(() => {
      router.push({ name: 'Login' });
    }, 1500);
    
  } catch (err) {
    if (err.response && err.response.data && err.response.data.error) {
      error.value = err.response.data.error;
    } else {
      error.value = 'Error al registrar el usuario. Por favor intente de nuevo.';
    }
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: calc(100vh - 4rem);
}

.login-card {
  background: white;
  padding: 2.5rem;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
}

.login-title {
  text-align: center;
  margin-bottom: 2rem;
  color: var(--text-color);
  font-size: 1.5rem;
  font-weight: 600;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.error-message {
  color: #dc2626;
  font-size: 0.875rem;
  text-align: center;
  background-color: #fee2e2;
  padding: 0.75rem;
  border-radius: 6px;
}

.success-message {
  color: #166534;
  font-size: 0.875rem;
  text-align: center;
  background-color: #dcfce3;
  padding: 0.75rem;
  border-radius: 6px;
}

.form-footer {
  text-align: center;
  font-size: 0.875rem;
  color: #6b7280;
  margin-top: 1rem;
}

.form-footer a {
  color: var(--primary-color);
  text-decoration: none;
  font-weight: 500;
}

.form-footer a:hover {
  text-decoration: underline;
}
</style>
