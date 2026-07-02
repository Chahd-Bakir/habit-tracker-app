import './bootstrap';
import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import App from './src/App.vue';
import routes from './src/router';

createApp(App).use(createRouter({ history: createWebHistory(), routes })).mount('#app');
