# Sistema de Gestión de Tareas

Aplicación web desarrollada con **Symfony 7.2**, **PHP 8.2+** y **Vue 3** como parte de una prueba técnica. Implementa un sistema CRUD de tareas con autenticación JWT, control de acceso por roles, generación de reportes y una interfaz reactiva en el cliente.

## Stack tecnológico

| Capa | Tecnología |
|------|-----------|
| Backend | Symfony 7.2 / PHP 8.2+ |
| ORM | Doctrine ORM 3 + Migrations |
| Autenticación | LexikJWT + GesdinetRefreshToken |
| Base de datos | MySQL 8 |
| Frontend | Vue 3 + Vue Router + Axios |
| Bundler | Webpack Encore |
| Reportes PDF | DomPDF |

---

## Requisitos previos

- PHP 8.2 o superior con extensiones: `pdo_mysql`, `ctype`, `iconv`
- Composer
- Node.js 18+ y npm
- MySQL 8
- Symfony CLI (opcional, para el servidor de desarrollo)

---

## Instalación

### 1. Clonar el repositorio

```bash
git clone <url-del-repositorio>
cd gestion_tareas
```

### 2. Instalar dependencias PHP

```bash
composer install
```

### 3. Instalar dependencias JavaScript

```bash
npm install
```

### 4. Configurar el entorno

Crear el archivo `.env.local` y definir la conexión a la base de datos:

```dotenv
DATABASE_URL="mysql://usuario:contraseña@127.0.0.1:3306/gestion_tareas?serverVersion=8.0.32&charset=utf8mb4"
```

> La clave JWT ya está preconfigurada en el `.env` para facilitar la evaluación.

### 5. Crear la base de datos y ejecutar migraciones

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
```

Esto ejecutará las dos migraciones en orden:
- `Version20260427031324` — tablas principales (`usuario`, `tarea`, `categoria`, `messenger_messages`)
- `Version20260429000000` — tabla `refresh_tokens` y campos de recuperación de contraseña en `usuario`

### 6. Cargar datos de prueba

```bash
php bin/console doctrine:fixtures:load --no-interaction
```

Esto crea usuarios, categorías y tareas de ejemplo listos para usar.

**Credenciales de acceso tras los fixtures:**

| Email | Contraseña | Rol |
|-------|-----------|-----|
| `admin@test.com` | `admin123` | ROLE_ADMIN |
| `user@test.com` | `user123` | ROLE_USER |

### 7. Compilar el frontend

```bash
npm run dev
```

### 8. Iniciar el servidor

```bash
symfony server:start
# o alternativamente:
php -S localhost:8000 -t public/
```

La aplicación estará disponible en `http://localhost:8000`.

---

## Capturas de pantalla

### Pantalla de login
> _(agregar captura)_

### Dashboard de tareas con filtros activos
> _(agregar captura)_

### Panel de creación / edición de tarea
> _(agregar captura)_

### Exportación de reporte en PDF
> _(agregar captura)_

### Vista mobile
> _(agregar captura)_

---

## Endpoints de la API

Todos los endpoints bajo `/api` requieren el header:
```
Authorization: Bearer <jwt_token>
```

### Autenticación

| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| POST | `/api/login_check` | Obtener JWT + refresh token | Pública |
| POST | `/api/register` | Registrar nuevo usuario | Pública |
| POST | `/auth/refresh` | Renovar JWT con refresh token | Pública |
| POST | `/api/forgot-password` | Solicitar recuperación de contraseña | Pública |
| POST | `/api/reset-password` | Confirmar nueva contraseña con token | Pública |

### Tareas

| Método | Endpoint | Descripción | Rol |
|--------|----------|-------------|-----|
| GET | `/api/tareas` | Listar tareas con filtros | USER |
| GET | `/api/tareas/{id}` | Detalle de una tarea | USER |
| POST | `/api/tareas` | Crear tarea | USER |
| PUT/PATCH | `/api/tareas/{id}` | Editar tarea | USER |
| DELETE | `/api/tareas/{id}` | Eliminar tarea | ADMIN |

**Filtros disponibles en `GET /api/tareas`:**

```
?estado=pendiente
?prioridad=alta
?usuario_id=1
?search=nombre
?fecha_inicio=2024-01-01&fecha_fin=2024-12-31
?sort_by=fechaCreacion&sort_order=DESC
```

### Categorías

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/categorias` | Listar todas las categorías |

### Reportes

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/reportes/tareas?formato=pdf` | Reporte PDF personalizable |
| GET | `/api/reportes/tareas?formato=csv` | Reporte CSV personalizable |

Los reportes aceptan los mismos filtros que el listado de tareas (`estado`, `prioridad`, `usuario_id`, `fecha_inicio`, `fecha_fin`).

---

## Reporte diario automático

El sistema incluye un comando de Symfony para generar el reporte diario de tareas activas:

```bash
php bin/console app:reporte-diario
```

El archivo PDF se guarda automáticamente en `var/reportes/reporte_diario_YYYY-MM-DD.pdf`.

Para automatizarlo en producción, agregar al crontab:

```cron
0 7 * * * /usr/bin/php /ruta/del/proyecto/bin/console app:reporte-diario
```

---

## Análisis de rendimiento — EXPLAIN de la consulta principal

La consulta principal del sistema es la que lista tareas con filtros dinámicos, joins y ordenamiento. Se ejecuta desde `TareaRepository::findTareasByFilters()`.

**SQL equivalente analizado:**

```sql
EXPLAIN
SELECT t.id, t.titulo, t.estado, t.prioridad, t.fecha_creacion, t.fecha_vencimiento,
       u.id AS usuario_id, u.email,
       c.id AS categoria_id, c.nombre
FROM tarea t
LEFT JOIN usuario u ON t.usuario_id = u.id
LEFT JOIN tarea_categoria tc ON t.id = tc.tarea_id
LEFT JOIN categoria c ON tc.categoria_id = c.id
WHERE t.estado = 'pendiente'
ORDER BY t.fecha_creacion DESC;
```

**Resultado del EXPLAIN:**

| id | select_type | table | type | possible_keys | key | key_len | ref | rows | Extra |
|----|-------------|-------|------|---------------|-----|---------|-----|------|-------|
| 1 | SIMPLE | t | ALL | — | — | — | — | N | Using filesort |
| 1 | SIMPLE | u | eq_ref | PRIMARY | PRIMARY | 4 | t.usuario_id | 1 | — |
| 1 | SIMPLE | tc | ref | PRIMARY, IDX_tarea_id | IDX_tarea_id | 4 | t.id | 1 | Using index |
| 1 | SIMPLE | c | eq_ref | PRIMARY | PRIMARY | 4 | tc.categoria_id | 1 | — |

**Observaciones y decisión de diseño:**

- Los joins a `usuario` y `categoria` utilizan sus claves primarias (`eq_ref`), lo cual es óptimo.
- La tabla `tarea_categoria` aprovecha el índice `IDX_5124921E6D5BDFE1` generado por Doctrine sobre `tarea_id`.
- El filtro por `estado` realiza un full scan (`ALL`) porque la columna no tiene índice propio. En un entorno con alta carga, añadir este índice sería la primera optimización:

```sql
-- Índice recomendado para entornos de producción con alta carga
CREATE INDEX idx_tarea_estado ON tarea (estado);

-- Índice compuesto recomendado si el filtro más común es estado + fecha
CREATE INDEX idx_tarea_estado_fecha ON tarea (estado, fecha_creacion);
```

- El `Using filesort` en el `ORDER BY fecha_creacion` desaparecería al agregar el índice compuesto anterior, ya que MySQL podría usar el índice tanto para filtrar como para ordenar en una sola pasada.

> **Nota:** Para el volumen de datos de esta aplicación (decenas a cientos de tareas), el rendimiento actual es completamente adecuado. Los índices adicionales se justifican a partir de miles de registros o bajo carga concurrente alta.

---

## Estructura del proyecto

```
src/
├── Command/
│   └── ReporteDiarioCommand.php    # Comando para reporte diario automático
├── Controller/
│   ├── AuthController.php          # Registro de usuarios
│   ├── CategoriaController.php     # CRUD categorías
│   ├── HomeController.php          # Sirve la SPA Vue
│   ├── PasswordResetController.php # Recuperación de contraseña
│   ├── ReporteController.php       # Exportación PDF/CSV
│   ├── TareaController.php         # CRUD tareas con filtros
│   └── UsuarioController.php       # Perfil del usuario
├── Entity/
│   ├── Categoria.php
│   ├── RefreshToken.php
│   ├── Tarea.php
│   └── Usuario.php
├── Repository/
│   ├── TareaRepository.php         # findTareasByFilters() — consulta principal
│   └── UsuarioRepository.php
└── Service/
    └── ReportService.php           # Generación de PDF y CSV

assets/vue/
├── views/
│   ├── LoginView.vue
│   ├── RegisterView.vue
│   └── TaskListView.vue            # Dashboard principal con filtros
├── router/index.js
└── App.vue

migrations/
├── Version20260427031324.php       # Esquema base
└── Version20260429000000.php       # refresh_tokens + campos reset_token
```

---

## Recuperación de contraseña

El flujo de recuperación de contraseña está implementado a nivel de API:

1. `POST /api/forgot-password` con `{ "email": "..." }` — genera un token seguro de 256 bits y lo almacena con expiración de 1 hora. Siempre devuelve el mismo mensaje genérico para evitar user enumeration.
2. `POST /api/reset-password` con `{ "token": "...", "password": "nueva" }` — valida el token, actualiza la contraseña con hash bcrypt e invalida el token para que no pueda reutilizarse.

> En producción, el token se enviaría por email via `symfony/mailer`. Para esta evaluación, el token puede consultarse directamente en la columna `reset_token` de la tabla `usuario` y usarse en el endpoint de reset.