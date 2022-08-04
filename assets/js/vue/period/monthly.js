import { format } from 'date-fns';
import locale from '../../dateFnsLocale';

const updateMonth = (date, increment) => {
    const updatedDate = new Date(date.getTime());
    updatedDate.setMonth(date.getMonth() + increment);
    return updatedDate;
};

export default {
    apiLoadCra(date) {
        const year = date.getFullYear();
        const month = ('0' + (date.getMonth() + 1)).substr(-2);

        return fetch(`/corp/api/temps/${year}/${month}`);
    },

    apiSaveCra(cra, date) {
        const year = date.getFullYear();
        const month = ('0' + (date.getMonth() + 1)).substr(-2);

        return fetch(`/corp/api/temps/${year}/${month}`, {
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
        const initialDate = new Date(date.getTime());

        initialDate.setDate(1);

        return initialDate;
    },

    displaySelectedDate(date) {
        return format(date, 'LLLL yyyy', {locale});
    },

    isCurrentDate(date) {
        const now = new Date();

        return now.getFullYear() === date.getFullYear() && now.getMonth() === date.getMonth();
    },

    calculateNextDate(date) {
        return updateMonth(date, +1);
    },

    calculatePrevDate(date) {
        return updateMonth(date, -1);
    },
}