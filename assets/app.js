import './styles/app.css';

import { createApp } from 'vue';
import App from './vue/App.vue';
import router from './vue/router';
import $ from 'jquery';

// Expose jQuery globally for potential plugins or simple manipulations
window.$ = window.jQuery = $;

const app = createApp(App);
app.use(router);
app.mount('#app');

// --- Evidencia de coexistencia con jQuery ---
// Usamos jQuery para una pequeña animación inicial global, 
// demostrando que puede convivir con el DOM que Vue gestiona.
$(document).ready(function() {
    console.log("jQuery está ejecutándose junto a Vue 3!");
    $('body').hide().fadeIn(800);
});
