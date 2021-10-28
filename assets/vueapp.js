import { createApp } from 'vue';
import SaisieDesTemps from './js/vue/SaisieDesTemps.vue';
import SaisieDesTempsDaily from './js/vue/SaisieDesTempsDaily.vue';

const app = createApp({});

app.component('saisie-des-temps', SaisieDesTemps);
app.component('saisie-des-temps-daily', SaisieDesTempsDaily);

app.mount('App');
