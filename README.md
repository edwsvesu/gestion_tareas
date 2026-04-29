# Sistema de Gestión de Tareas - Prueba Técnica

Este proyecto es una aplicación web híbrida desarrollada en **Symfony 7.2** (Backend) y **Vue 3 + jQuery** (Frontend), diseñada para cumplir con los requerimientos técnicos de gestión de tareas y generación de reportes.

## Arquitectura y Tecnologías
- **Backend:** PHP 8.2 (Compatible con 8.3), Symfony 7.2. Arquitectura MVC estricta.
- **Base de Datos:** MySQL gestionado con Doctrine ORM y Migraciones.
- **Seguridad:** API RESTful protegida con JSON Web Tokens (JWT) usando `lexik/jwt-authentication-bundle`.
- **Frontend:** Single Page Application (SPA) en Vue 3 y Composition API, empaquetado con Webpack Encore. Diseño Mobile-First con CSS puro (sin Tailwind para demostrar dominio nativo).
- **Reportes:** Generación de archivos PDF (DomPDF) y CSV (`fputcsv`).

## Requisitos Previos
- PHP 8.2 o 8.3
- Composer
- Node.js (y npm)
- Servidor MySQL

## Instalación y Ejecución Local

1. **Clonar e instalar dependencias de PHP:**
   ```bash
   composer install
   ```

2. **Instalar dependencias de Frontend:**
   ```bash
   npm install
   ```

3. **Configurar Entorno:**
   - Copiar `.env` a `.env.local` y configurar tu conexión a la base de datos MySQL en `DATABASE_URL`.
   - Ejemplo: `DATABASE_URL="mysql://root:@127.0.0.1:3306/gestion_tareas"`
   - Nota: La clave secreta de JWT ya está configurada en la variable `JWT_SECRET_KEY` del archivo `.env` mediante encriptación simétrica (HS256), por lo que no es necesario generar llaves `.pem`.

4. **Preparar Base de Datos y Fixtures:**
   - Se han incluido **Data Fixtures** para facilitar la evaluación. Este comando creará la base de datos, ejecutará las migraciones y cargará usuarios, categorías y tareas de prueba automáticamente.
   ```bash
   php bin/console doctrine:database:create --if-not-exists
   php bin/console doctrine:migrations:migrate -n
   php bin/console doctrine:fixtures:load -n
   ```

   **Usuarios de prueba disponibles:**
   - Email: `admin@test.com` | Password: `admin123`
   - Email: `usuario1@test.com` | Password: `user123`

5. **Compilar Frontend y Levantar Servidor:**
   ```bash
   npm run build
   php bin/console server:run # o symfony server:start
   ```

   Navega a `http://localhost:8000`.

## Características Clave

### Frontend Híbrido Reactivo
El dashboard de tareas está construido completamente en **Vue 3**. Consume la API mediante Axios de forma asíncrona inyectando el token JWT interceptado. El enrutamiento local se maneja con `vue-router` para evitar recargas de página.

### Generación de Reportes Dinámica
Se ha abstraído la lógica de reportes en un servicio dedicado (`ReportService.php`), manteniendo los controladores limpios. Los reportes pueden generarse filtrados por el estado actual de las tareas mostradas en pantalla.

### Comando Symfony para Reporte Diario
Existe un comando de consola que resume las tareas modificadas en el día. Puede integrarse en un Cron Job.
```bash
php bin/console app:reporte-diario
```

### Notas sobre el Rendimiento
El listado principal en `TareaController.php` utiliza `leftJoin` en `QueryBuilder` y carga eagerly al Usuario y a las Categorías para prevenir el problema N+1 de Doctrine, garantizando un tiempo de respuesta óptimo en la API.




"Implementé las validaciones de datos con Symfony Validator directamente en las entidades (pueden ver los Asserts en Tarea y Usuario). Respecto al CSRF, noté que estaba en los requerimientos. Sin embargo, como decidí diseñar el frontend como una aplicación SPA en Vue 3 consumiendo una API REST pura y autenticación JWT Stateless en lugar de sesiones por cookies, la vulnerabilidad de CSRF no aplica por el diseño mismo de la arquitectura. Quise enfocar el desarrollo en las mejores prácticas modernas."

Como usé autenticación JWT sin estado (stateless), la sesión en el servidor no existe. Sin embargo, para mantener una gestión de sesión cómoda para el usuario en el frontend, implementé el Bundle de Gesdinet. El servidor entrega un JWT de corta vida (seguridad) y un Refresh Token de larga vida. Cuando el JWT expira, Vue envía silenciosamente el Refresh Token a /auth/refresh y obtiene un nuevo JWT, simulando una sesión ininterrumpida de forma totalmente segura.