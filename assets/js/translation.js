import i18next from 'i18next';
import messagesFr from '../translations/messages.fr.json';
import messagesEn from '../translations/messages.en.json';

import i18n_dt from './datatable/languages';

const detectedLocale = document.querySelector('html').lang || 'fr';

i18next.init({
    lng: detectedLocale,
    fallbackLng: 'fr',
    resources: {
        fr: {
            translation: messagesFr,
        },
        en: {
            translation: messagesEn,
        },
    },
});

const t = i18next.t.bind(i18next);
const language_dt = i18n_dt[detectedLocale];

export {
    i18next,
    t,
    detectedLocale,
    language_dt
};
