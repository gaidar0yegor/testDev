<template>
    <div class="row" :class="'saisie-des-temps saisie-des-temps-' + monthlyOrWeekly">
        <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2">
            <h2 class="mb-4 d-flex flex-row align-items-center justify-content-between">
                <button class="btn btn-link" @click="prev">
                    <i class="fa fa-angle-left fa-2x"></i>
                </button>
                <span class="mx-3 text-month">
                    {{ displayDate }}
                </span>
                <span v-tippy="{content: isCurrentDate ? 'Impossible de saisir les temps passés dans le futur.' : null}">
                    <button
                        class="btn btn-link"
                        :class="{'text-muted': isCurrentDate}"
                        :disabled="isCurrentDate"
                        @click="next"
                    >
                        <i class="fa fa-angle-right fa-2x"></i>
                    </button>
                </span>
            </h2>

            <form v-if="cra && cra.tempsPasses.length > 0" @submit="submitCra">
                <div class="message-validation text-center">
                    <p v-if="cra.tempsPassesModifiedAt" class="text-success">
                        <i class="fa fa-check" aria-hidden="true"></i>
                        Vous avez validé ce mois le {{ formatDate(cra.tempsPassesModifiedAt) }}.
                    </p>
                </div>

                <div
                    v-for="tempsPasse in cra.tempsPasses" :key="tempsPasse.id"
                    class="row mb-2 align-items-center"
                >
                    <div class="col text-right lead">
                        <label
                            :for="'temps_passe_pourcentage_' + tempsPasse.id"
                            v-tippy="{content: tempsPasse.projet.titre}"
                            class="m-0"
                        >Projet {{ tempsPasse.projet.acronyme }}</label>
                    </div>
                    <div class="col">
                        <div class="input-group input-group-lg">
                            <input
                                v-model="tempsPasse.pourcentage"
                                type="number"
                                class="form-control"
                                :class="{'is-invalid': !validPercentage(tempsPasse.pourcentage) || !validPercentagesSum(cra.tempsPasses)}"
                                :id="'temps_passe_pourcentage_' + tempsPasse.id"
                                min="0"
                                max="100"
                                required
                            />
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="col d-none d-sm-block">
                    </div>
                </div>

                <button
                    type="submit"
                    class="mt-5 btn btn-success btn-lg mx-auto d-block"
                    :disabled="submitting || !validCra(cra)"
                >Mettre à jour</button>
            </form>

            <p v-if="cra && cra.tempsPasses.length === 0" class="lead text-center">
                Ce mois-ci, vous n'avez pas de temps à saisir car vous n'étiez contributeur sur aucun projet.
            </p>
        </div>
    </div>
</template>

<script>
import { addWeeks, getWeek, format, parseISO, startOfWeek } from 'date-fns';
import { fr as locale } from 'date-fns/locale';
import { directive as tippy } from 'vue-tippy';
import { addFlashMessage, clearFlashMessages } from './../flash-messages';

const updateMonth = (date, increment) => {
    const updatedDate = new Date(date.getTime());
    updatedDate.setMonth(date.getMonth() + increment);
    return updatedDate;
};

const strategies = {
    monthly: {
        apiLoadCra(date) {
            const year = date.getFullYear();
            const month = ('0' + (date.getMonth() + 1)).substr(-2);

            return fetch(`/api/temps/${year}/${month}`);
        },

        apiSaveCra(cra, date) {
            const year = date.getFullYear();
            const month = ('0' + (date.getMonth() + 1)).substr(-2);

            return fetch(`/api/temps/${year}/${month}`, {
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
    },

    weekly: {
        apiLoadCra(date) {
            const year = date.getFullYear();
            const month = ('0' + (date.getMonth() + 1)).substr(-2);
            const day = ('0' + date.getDate()).substr(-2);

            return fetch(`/api/temps/weekly/${year}/${month}/${day}`);
        },

        apiSaveCra(cra, date) {
            const year = date.getFullYear();
            const month = ('0' + (date.getMonth() + 1)).substr(-2);
            const day = ('0' + date.getDate()).substr(-2);

            return fetch(`/api/temps/weekly/${year}/${month}/${day}`, {
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

            return [
                'Semaine du',
                format(date, date.getMonth() === endWeek.getMonth() ? 'd' : 'd LLLL', {locale}),
                'au',
                format(endWeek, 'd LLLL yyyy', {locale}),
            ].join(' ');
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
    },
};

export default {
    directives: {
        tippy,
    },

    props: {
        monthlyOrWeekly: {
            type: String,
            validator: value => 'monthly' === value || 'weekly' === value,
            default: 'monthly',
        },
        urlToAbsences: {
            type: String,
        },
    },

    data() {
        const strategy = strategies[this.monthlyOrWeekly];

        return {
            strategy,
            selectedDate: strategy.calculateInitialDate(new Date()),
            cra: null,
            submitting: false,
        };
    },

    mounted() {
        this.loadCurrentTempsPasses();

        const path = window.location.pathname.split('/');

        if (4 === path.length) {
            const [year, month] = path.slice(-2);

            this.selectedDate = this.strategy.calculateInitialDate(new Date(
                parseInt(year, 10),
                parseInt(month, 10) - 1,
                7,
            ));
        }
    },

    methods: {
        loadCurrentTempsPasses() {
            this.cra = null;

            this.strategy.apiLoadCra(this.selectedDate)
                .then(response => response.json())
                .then(cra => {
                    if (cra.tempsPassesModifiedAt) {
                        cra.tempsPassesModifiedAt = parseISO(cra.tempsPassesModifiedAt);
                    }

                    this.cra = cra;
                })
            ;
        },

        submitCra(e) {
            e.preventDefault();

            clearFlashMessages();
            this.submitting = true;

            this.strategy.apiSaveCra(this.cra, this.selectedDate)
                .then(() => {
                    this.submitting = false;
                    this.cra.tempsPassesModifiedAt = new Date();

                    addFlashMessage('success', 'Temps passés mis à jour.');

                    if (this.urlToAbsences) {
                        const url = this.urlToAbsences
                            .replace('1111', this.selectedDate.getFullYear())
                            .replace('22', ('0' + (this.selectedDate.getMonth() + 1)).substr(-2))
                        ;

                        addFlashMessage('warning', `<a href="${url}" class="alert-link">Saisissez vos absences</a> si vous en avez pris ce mois ci.`);
                    }
                })
            ;

            return false;
        },

        next() {
            this.selectedDate = this.strategy.calculateNextDate(this.selectedDate);
            this.loadCurrentTempsPasses();
        },

        prev() {
            this.selectedDate = this.strategy.calculatePrevDate(this.selectedDate);
            this.loadCurrentTempsPasses();
        },

        formatDate(date) {
            if (!date) {
                return '-';
            }

            return format(date, 'EEEE d LLLL yyyy', {locale});
        },

        validPercentage(percentage) {
            return percentage >= 0 && percentage <= 100;
        },

        validPercentagesSum(tempsPasses) {
            return this.validPercentage(tempsPasses.reduce((sum, tempsPasse) => sum + parseInt(tempsPasse.pourcentage, 10), 0));
        },

        validCra(cra) {
            if (cra.tempsPasses.some(tempsPasse => !this.validPercentage(tempsPasse.pourcentage))) {
                return false;
            }

            return this.validPercentagesSum(cra.tempsPasses);
        },
    },

    computed: {
        displayDate() {
            return this.strategy.displaySelectedDate(this.selectedDate);
        },

        isCurrentDate() {
            return this.strategy.isCurrentDate(this.selectedDate);
        },
    },
};
</script>

<style scoped>
    .saisie-des-temps-weekly .text-month {
        font-size: 0.75em;
    }
</style>
