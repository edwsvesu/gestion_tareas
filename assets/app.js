import './styles/app.css';

import { createApp } from 'vue';
import App from './vue/App.vue';
import router from './vue/router';
import $ from 'jquery';

window.$ = window.jQuery = $;

const app = createApp(App);
app.use(router);
app.mount('#app');

// Se usa jQuery para una pequeña animación inicial global, 
$(document).ready(function () {
    console.log("jQuery está ejecutándose junto a Vue 3!");
    $('body').hide().fadeIn(800);
});
