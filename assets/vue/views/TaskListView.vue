<template>
  <div class="task-dashboard">
    <div class="dashboard-header">
      <h1 class="page-title">Gestión de Tareas</h1>
      <div class="header-actions">
        <button @click="downloadReport('pdf')" class="btn btn-outline" :disabled="downloading">
          {{ downloading ? 'Generando...' : '📄 Reporte PDF' }}
        </button>
        <button @click="downloadReport('csv')" class="btn btn-outline" :disabled="downloading">
           {{ downloading ? 'Generando...' : '📊 Reporte CSV' }}
        </button>
        <button @click="toggleForm" class="btn btn-primary">
          {{ showForm ? 'Cancelar' : '+ Nueva Tarea' }}
        </button>
      </div>
    </div>

    <!-- Formulario Nueva Tarea -->
    <div v-if="showForm" class="task-form-card">
      <h3 class="form-title">{{ form.id ? 'Editar Tarea' : 'Nueva Tarea' }}</h3>
      <form @submit.prevent="saveTask" class="form-grid">
        <div class="form-group">
          <label>Título</label>
          <input type="text" v-model="form.titulo" required class="form-control" />
        </div>
        <div class="form-group">
          <label>Prioridad</label>
          <select v-model="form.prioridad" class="form-control">
            <option value="baja">Baja</option>
            <option value="media">Media</option>
            <option value="alta">Alta</option>
          </select>
        </div>
        <div class="form-group">
          <label>Estado</label>
          <select v-model="form.estado" class="form-control">
            <option value="pendiente">Pendiente</option>
            <option value="en_progreso">En Progreso</option>
            <option value="completada">Completada</option>
          </select>
        </div>
        <div class="form-group">
          <label>Fecha Vencimiento</label>
          <input type="date" v-model="form.fechaVencimiento" class="form-control" />
        </div>
        <div class="form-group" style="grid-column: 1 / -1;">
          <label>Categorías</label>
          <div class="categories-selection">
            <label v-for="cat in categoriesList" :key="cat.id" class="cat-checkbox">
              <input type="checkbox" :value="cat.id" v-model="form.categorias" />
              {{ cat.nombre }}
            </label>
          </div>
        </div>
        <div class="form-group" style="grid-column: 1 / -1;">
          <label>Descripción</label>
          <textarea v-model="form.descripcion" class="form-control" rows="2"></textarea>
        </div>
        <div class="form-actions" style="grid-column: 1 / -1;">
          <button type="submit" class="btn btn-primary" :disabled="loading">Guardar</button>
        </div>
      </form>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="filters-container">
      <div class="search-wrapper">
        <span class="search-icon">🔍</span>
        <input type="text" v-model="filters.search" placeholder="Buscar tarea por título o descripción..." class="form-control search-input" @input="fetchTasks" />
      </div>
      
      <div class="filters-grid">
        <div class="filter-group">
          <label class="filter-label">Estado</label>
          <select v-model="filters.estado" class="form-control select-filter" @change="fetchTasks">
            <option value="">Todos los estados</option>
            <option value="pendiente">Pendientes</option>
            <option value="en_progreso">En Progreso</option>
            <option value="completada">Completadas</option>
          </select>
        </div>
        <div class="filter-group">
          <label class="filter-label">Prioridad</label>
          <select v-model="filters.prioridad" class="form-control select-filter" @change="fetchTasks">
            <option value="">Todas</option>
            <option value="baja">Baja</option>
            <option value="media">Media</option>
            <option value="alta">Alta</option>
          </select>
        </div>
        <div class="filter-group">
          <label class="filter-label">Asignado a</label>
          <select v-model="filters.usuario_id" class="form-control select-filter" @change="fetchTasks">
            <option value="">Todos</option>
            <option v-for="user in usersList" :key="user.id" :value="user.id">
              {{ user.email }}
            </option>
          </select>
        </div>
        <div class="filter-group">
          <label class="filter-label">Ordenar por</label>
          <select v-model="filters.sort_by" class="form-control select-filter" @change="fetchTasks">
            <option value="fechaCreacion">Creación</option>
            <option value="fechaVencimiento">Vencimiento</option>
            <option value="prioridad">Prioridad</option>
            <option value="titulo">Título</option>
          </select>
        </div>
        <div class="filter-group date-filters-group">
          <label class="filter-label">Rango de fechas</label>
          <div class="date-filters">
            <input type="date" v-model="filters.fecha_inicio" class="form-control" title="Fecha Inicio" @change="fetchTasks" />
            <span class="date-separator">a</span>
            <input type="date" v-model="filters.fecha_fin" class="form-control" title="Fecha Fin" @change="fetchTasks" />
          </div>
        </div>
      </div>
    </div>

    <!-- Listado -->
    <div v-if="loading && tasks.length === 0" class="loading-state">
      Cargando tareas...
    </div>
    <div v-else-if="tasks.length === 0" class="empty-state">
      No se encontraron tareas.
    </div>
    <div v-else class="task-grid">
      <div v-for="task in tasks" :key="task.id" class="task-card">
        <div class="task-card-header">
          <span :class="['badge', `badge-${task.prioridad}`]">{{ task.prioridad }}</span>
          <span :class="['badge', `badge-${task.estado}`]">{{ task.estado.replace('_', ' ') }}</span>
        </div>
        <h3 class="task-title">{{ task.titulo }}</h3>
        <p class="task-desc">{{ task.descripcion }}</p>
        <div v-if="task.categorias.length > 0" class="task-categories">
          <span v-for="cat in task.categorias" :key="cat.id" class="cat-tag">#{{ cat.nombre }}</span>
        </div>
        <div class="task-meta">
          <small>👤 {{ task.usuario.email }}</small><br/>
          <small>📅 Creada: {{ formatDate(task.fechaCreacion) }}</small>
          <small v-if="task.fechaVencimiento" :class="{'text-danger': isOverdue(task.fechaVencimiento)}">
            | ⏳ Vence: {{ formatDate(task.fechaVencimiento) }}
          </small>
        </div>
        <div class="task-actions">
          <button v-if="task.estado !== 'completada'" @click="updateStatus(task, 'completada')" class="btn-text text-success">
            ✓ Completar
          </button>
          <button @click="editTask(task)" class="btn-text text-primary">
            ✏️ Editar
          </button>
          <button @click="deleteTask(task.id)" class="btn-text text-danger">
            🗑 Eliminar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import api from '../services/api';

const tasks = ref([]);
const categoriesList = ref([]);
const usersList = ref([]);
const loading = ref(false);
const downloading = ref(false);
const showForm = ref(false);

const filters = reactive({
  estado: '',
  prioridad: '',
  search: '',
  usuario_id: '',
  sort_by: 'fechaCreacion',
  fecha_inicio: '',
  fecha_fin: ''
});

const form = reactive({
  id: null,
  titulo: '',
  descripcion: '',
  prioridad: 'media',
  estado: 'pendiente',
  fechaVencimiento: '',
  categorias: []
});
const toggleForm = () => {
  if (showForm.value) {
    resetForm();
    showForm.value = false;
  } else {
    showForm.value = true;
  }
};
const fetchTasks = async () => {
  loading.value = true;
  try {
    const params = new URLSearchParams();
    if (filters.estado) params.append('estado', filters.estado);
    if (filters.prioridad) params.append('prioridad', filters.prioridad);
    if (filters.search) params.append('search', filters.search);
    if (filters.sort_by) params.append('sort_by', filters.sort_by);
    if (filters.usuario_id) params.append('usuario_id', filters.usuario_id);
    if (filters.fecha_inicio) params.append('fecha_inicio', filters.fecha_inicio);
    if (filters.fecha_fin) params.append('fecha_fin', filters.fecha_fin);
    
    const response = await api.get(`/tareas?${params.toString()}`);
    tasks.value = response.data;
  } catch (error) {
    console.error('Error fetching tasks', error);
  } finally {
    loading.value = false;
  }
};

const fetchCategories = async () => {
  try {
    const response = await api.get('/categorias');
    categoriesList.value = response.data;
  } catch (error) {
    console.error('Error fetching categories', error);
  }
};

const fetchUsers = async () => {
  try {
    const response = await api.get('/usuarios');
    usersList.value = response.data;
  } catch (error) {
    console.error('Error fetching users', error);
  }
};

const saveTask = async () => {
  loading.value = true;
  try {
    if (form.id) {
      await api.put(`/tareas/${form.id}`, form);
    } else {
      await api.post('/tareas', form);
    }
    
    resetForm();
    showForm.value = false;
    await fetchTasks();
  } catch (error) {
    console.error('Error saving task', error);
  } finally {
    loading.value = false;
  }
};

const editTask = (task) => {
  form.id = task.id;
  form.titulo = task.titulo;
  form.descripcion = task.descripcion;
  form.prioridad = task.prioridad;
  form.estado = task.estado;
  form.fechaVencimiento = task.fechaVencimiento ? task.fechaVencimiento.split(' ')[0] : '';
  form.categorias = task.categorias.map(c => c.id);
  showForm.value = true;
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const resetForm = () => {
  form.id = null;
  form.titulo = '';
  form.descripcion = '';
  form.prioridad = 'media';
  form.estado = 'pendiente';
  form.fechaVencimiento = '';
  form.categorias = [];
};

const updateStatus = async (task, newStatus) => {
  try {
    await api.put(`/tareas/${task.id}`, { estado: newStatus });
    await fetchTasks();
  } catch (error) {
    console.error('Error updating status', error);
  }
};

const deleteTask = async (id) => {
  if (!confirm('¿Seguro que deseas eliminar esta tarea?')) return;
  try {
    await api.delete(`/tareas/${id}`);
    await fetchTasks();
  } catch (error) {
    if (error.response && error.response.status === 403) {
      alert('Acceso Denegado: Solo un administrador (ROLE_ADMIN) puede eliminar tareas.');
    } else {
      console.error('Error deleting task', error);
      alert('Ocurrió un error al intentar eliminar la tarea.');
    }
  }
};

const downloadReport = async (format) => {
  downloading.value = true;
  try {
    const params = new URLSearchParams();
    params.append('formato', format);
    if (filters.estado) params.append('estado', filters.estado);
    if (filters.prioridad) params.append('prioridad', filters.prioridad);
    if (filters.usuario_id) params.append('usuario_id', filters.usuario_id);
    if (filters.fecha_inicio) params.append('fecha_inicio', filters.fecha_inicio);
    if (filters.fecha_fin) params.append('fecha_fin', filters.fecha_fin);

    const response = await api.get(`/reportes/tareas?${params.toString()}`, {
      responseType: 'blob'
    });
    
    // Crear enlace temporal para forzar descarga
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    const extension = format === 'csv' ? 'csv' : 'pdf';
    link.setAttribute('download', `reporte_tareas.${extension}`);
    document.body.appendChild(link);
    link.click();
    link.remove();
  } catch (error) {
    console.error('Error downloading report', error);
    alert('Error al generar el reporte');
  } finally {
    downloading.value = false;
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
};

const isOverdue = (dateString) => {
  if (!dateString) return false;
  return new Date(dateString) < new Date() && form.estado !== 'completada';
};

onMounted(() => {
  fetchTasks();
  fetchCategories();
  fetchUsers();
});
</script>

<style scoped>
.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.page-title {
  font-size: 1.75rem;
  color: var(--text-color);
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 0.5rem;
}

.filters-container {
  background: white;
  padding: 1.25rem;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  margin-bottom: 2rem;
}

.search-wrapper {
  position: relative;
  margin-bottom: 1rem;
}

.search-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
  font-size: 1.1rem;
}

.search-input {
  width: 100%;
  padding-left: 2.75rem;
  font-size: 1rem;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  padding-top: 0.75rem;
  padding-bottom: 0.75rem;
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 1rem;
  align-items: end;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.filter-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.select-filter {
  width: 100%;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
  padding: 0.5rem;
  background-color: #f8fafc;
}

.date-filters-group {
  grid-column: span 2;
}

@media (max-width: 768px) {
  .date-filters-group {
    grid-column: span 1;
  }
  .filters-grid {
    grid-template-columns: 1fr;
  }
}

.date-filters {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.date-separator {
  color: #64748b;
  font-size: 0.875rem;
  font-weight: 500;
}

.categories-selection {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  padding: 0.5rem;
  background: #f8fafc;
  border-radius: 6px;
}

.cat-checkbox {
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  gap: 0.3rem;
  cursor: pointer;
}

.task-categories {
  margin-bottom: 0.75rem;
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
}

.cat-tag {
  font-size: 0.75rem;
  color: var(--primary-color);
  font-weight: 500;
}

.task-form-card {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  margin-bottom: 2rem;
  border-left: 4px solid var(--primary-color);
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.task-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
}

.task-card {
  background: white;
  border-radius: 10px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  display: flex;
  flex-direction: column;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.task-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.task-card-header {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.badge {
  padding: 0.25rem 0.6rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
}

/* Colores de badges */
.badge-alta { background-color: #fee2e2; color: #991b1b; }
.badge-media { background-color: #fef3c7; color: #92400e; }
.badge-baja { background-color: #e0f2fe; color: #075985; }
.badge-pendiente { background-color: #f1f5f9; color: #475569; }
.badge-en_progreso { background-color: #dbeafe; color: #1e40af; }
.badge-completada { background-color: #dcfce3; color: #166534; }

.task-title {
  font-size: 1.1rem;
  margin: 0 0 0.5rem 0;
  color: #1f2937;
}

.task-desc {
  color: #6b7280;
  font-size: 0.9rem;
  flex-grow: 1;
  margin-bottom: 1rem;
}

.task-meta {
  color: #9ca3af;
  margin-bottom: 1rem;
}

.task-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  border-top: 1px solid #f3f4f6;
  padding-top: 1rem;
}

.btn-text {
  background: none;
  border: none;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  padding: 0;
}

.text-success { color: #16a34a; }
.text-danger { color: #dc2626; }
.text-success:hover { color: #15803d; text-decoration: underline; }
.text-danger:hover { color: #b91c1c; text-decoration: underline; }

.empty-state, .loading-state {
  text-align: center;
  padding: 3rem;
  color: #6b7280;
  background: white;
  border-radius: 8px;
}
</style>
