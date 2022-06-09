import { addWeeks, getWeek, format, startOfWeek } from 'date-fns';
import locale from '../../dateFnsLocale';
import { t } from '../../translation';

export default {
    apiLoadCra(date) {
        const year = date.getFullYear();
        const month = ('0' + (date.getMonth() + 1)).substr(-2);
        const day = ('0' + date.getDate()).substr(-2);

        return fetch(`/corp/api/temps/weekly/${year}/${month}/${day}`);
    },

    apiSaveCra(cra, date) {
        const year = date.getFullYear();
        const month = ('0' + (date.getMonth() + 1)).substr(-2);
        const day = ('0' + date.getDate()).substr(-2);

        return fetch(`/corp/api/temps/weekly/${year}/${month}/${day}`, {
            method: 'post',
            headers: {
                'Content-Type': 'application/json;charset=utf-8',
            },
            body: JSON.stringify(cra.tempsPasses.map(tempsPasse => ([
                tempsPasse.projet.id,
                tempsPasse.pourcentage,
            ]))),
        });
    },

    calculateInitialDate(date) {
        return startOfWeek(date, {weekStartsOn: 1});
    },

    displaySelectedDate(date) {
        const endWeek = new Date(date.getTime());

        endWeek.setDate(date.getDate() + 6);

        return t('week_from_to', {
            from: format(date, date.getMonth() === endWeek.getMonth() ? 'd' : 'd LLLL', {locale}),
            to: format(endWeek, 'd LLLL yyyy', {locale}),
        });
    },

    isCurrentDate(date) {
        const now = new Date();

        return now.getFullYear() === date.getFullYear() && getWeek(now) === getWeek(date);
    },

    calculateNextDate(date) {
        return addWeeks(date, 1);
    },

    calculatePrevDate(date) {
        return addWeeks(date, -1);
    },
}
