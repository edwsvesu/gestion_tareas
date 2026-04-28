# Resumen de Archivos Intervenidos

Este documento lista todos los archivos en los que hemos intervenido directamente (ya sea con código, correcciones o sugerencias arquitectónicas) durante la configuración del proyecto y la mejora del mismo.

## ⚙️ Backend (Symfony)

1. **`src/Entity/Tarea.php`**
   * **Qué hicimos:** Añadimos el atributo `#[ORM\HasLifecycleCallbacks]` encima de la clase para que el método que actualiza automáticamente la `fechaModificacion` (`#[ORM\PreUpdate]`) funcionara correctamente al guardar en la base de datos.

2. **`src/Controller/HomeController.php`**
   * **Qué hicimos:** Implementamos la "Ruta Comodín" (Catch-All route) usando una expresión regular (`^(?!api|build|_profiler|_wdt).*$`) para interceptar peticiones no dirigidas a la API y enviarlas a la plantilla de Twig, delegando la navegación a Vue Router.

3. **`src/Controller/RegistrationController.php`** *(o `AuthController.php`)*
   * **Qué hicimos:** Proporcioné la lógica para recibir el JSON del frontend, instanciar el Usuario, cifrar la contraseña con `UserPasswordHasherInterface` y persistirlo en la base de datos.

4. **`config/packages/security.yaml`**
   * **Qué hicimos:** Intervención crítica. Corregimos la estructura YAML para unificar bajo `security` los firewalls (`login` para autenticación y `api` para validar JWT) y establecimos las reglas de `access_control` para proteger las rutas bajo `/api`.

5. **`config/packages/lexik_jwt_authentication.yaml`**
   * **Qué hicimos:** Configuramos la "Opción B" (HS256). Instruimos a Lexik para que no buscara archivos `.pem`, sino que usara directamente una variable de entorno como clave secreta, configurando el `encoder` con el algoritmo `HS256`.

6. **`config/routes.yaml`**
   * **Qué hicimos:** Declaramos explícitamente la ruta `api_login_check` para que Symfony supiera dónde escuchar las peticiones de inicio de sesión manejadas por Lexik JWT.

7. **`.env`**
   * **Qué hicimos:** Configuramos las variables de entorno principales: ajustamos el `DATABASE_URL` (SQLite/MySQL) y añadimos tu clave personalizada en `JWT_SECRET_KEY`.

---

## 🎨 Frontend (Vue.js & Webpack)

1. **`assets/vue/views/TaskListView.vue`**
   * **Qué hicimos:** Refactorización visual completa de la sección de "Filtros y Búsqueda". Reemplazamos flexbox por `CSS Grid` (`.filters-grid`), añadimos etiquetas descriptivas, un ícono de lupa y encapsulamos todo en una tarjeta (`.filters-container`).

2. **`webpack.config.js`**
   * **Qué hicimos:** Habilitamos el soporte para el compilador de componentes de un solo archivo (SFC) en Vue descomentando y configurando `.enableVueLoader(() => {}, { version: 3 })`.

3. **`assets/app.js`**
   * **Qué hicimos:** Modificamos el punto de entrada principal de JS. Importamos `createApp`, el `router`, y configuramos el montaje de la aplicación Vue al elemento `#app` del DOM.

4. **`assets/vue/App.vue`**
   * **Qué hicimos:** Creamos el esqueleto inicial ("Hola Mundo") para comprobar la compilación de Vue, sentando las bases para el enrutamiento principal.

5. **`assets/vue/router/index.js`**
   * **Qué hicimos:** Proporcioné el código para instanciar Vue Router, mapear las rutas (`/login`, `/register`, `/`) a los componentes correspondientes y aplicar un "Navigation Guard" básico para proteger las rutas privadas.

6. **`templates/base.html.twig`** *(y `home.html.twig`)*
   * **Qué hicimos:** Modificamos el esqueleto HTML base para añadir el punto de anclaje de Vue (`id="app"`) y la carga de los scripts de Webpack Encore.
