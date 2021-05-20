import { createApp } from 'vue';
import SaisieDesTemps from './js/vue/SaisieDesTemps.vue';

const app = createApp({});

app.component('saisie-des-temps', SaisieDesTemps);

app.mount('App');
