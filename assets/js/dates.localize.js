import { detectedLocale } from './translation';

const fr = {
    days: ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'],
    daysShort: ['dim', 'lun', 'mar', 'mer', 'jeu', 'ven', 'sam'],
    daysMin: ['di', 'lu', 'ma', 'me', 'je', 've', 'sa'],
    months: ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'],
    monthsShort: ['jan', 'fév', 'mar', 'avr', 'mai', 'jui', 'jui', 'aoû', 'sep', 'oct', 'nov', 'déc'],
    today: 'Aujourd\'hui',
    clear: 'Vider',
    format: 'dd/mm/yyyy',
    titleFormat: 'MM yyyy',
    weekStart: 0,
};

const en = {
    days: ['sunday', 'monday', 'tuesday', 'wednesday', 'thirsday', 'friday', 'satursday'],
    daysShort: ['sun', 'mon', 'tue', 'wed', 'thi', 'fri', 'sat'],
    daysMin: ['su', 'mo', 'tu', 'we', 'th', 'fr', 'sa'],
    months: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
    monthsShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    today: 'Today',
    clear: 'Clear',
    format: 'dd/mm/yyyy',
    titleFormat: 'MM yyyy',
    weekStart: 1,
};

export default {
    en,
    fr,
}[detectedLocale];
