<template>
  <div class="app-container">
    <nav class="navbar" v-if="isAuthenticated">
      <div class="navbar-brand">Task Manager</div>
      <div class="navbar-menu">
        <button @click="logout" class="btn btn-outline">Cerrar Sesión</button>
      </div>
    </nav>
    <main class="main-content">
      <router-view></router-view>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();
const isLogged = ref(false);

const checkAuth = () => {
  isLogged.value = localStorage.getItem('jwt_token') !== null;
};

// Observar cambios en la ruta para actualizar el estado de autenticación
watch(route, () => {
  checkAuth();
});

onMounted(() => {
  checkAuth();
});

const isAuthenticated = computed(() => {
  return isLogged.value && route.name !== 'Login';
});

const logout = () => {
  localStorage.removeItem('jwt_token');
  router.push({ name: 'Login' });
};
</script>

<style scoped>
.app-container {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background-color: var(--bg-color);
}

.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  background-color: white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.navbar-brand {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--primary-color);
}

.main-content {
  flex: 1;
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
  width: 100%;
}

@media (max-width: 768px) {
  .main-content {
    padding: 1rem;
  }
}
</style>
