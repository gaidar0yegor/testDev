import { enUS, fr } from 'date-fns/locale';
import { detectedLocale } from './translation';

export default {
    fr,
    en: enUS,
}[detectedLocale];
