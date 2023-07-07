<template>
    <div class="saisie-des-temps saisie-des-temps-daily">
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

        <p v-if="cra && cra.isUserBelongingToSociete === false" class="lead text-center">
            Impossible de saisir les temps de cette période car vous n'étiez pas dans la société.<br>
            Dans le cas contraire, contactez votre administrateur RDI-Manager ou votre N+1 pour mettre à jour votre date d’entrée dans la société.
        </p>

        <p v-if="cra && cra.tempsPasses.length === 0 && cra.isUserBelongingToSociete === true" class="lead text-center">
            Cette période, vous n'avez pas de temps à saisir car vous n'étiez contributeur sur aucun projet.
        </p>

        <form v-if="cra && cra.tempsPasses.length > 0 && cra.isUserBelongingToSociete === true" @submit="submitCra">
            <div class="message-validation text-center">
                <p v-if="cra.tempsPassesModifiedAt" class="text-success">
                    <i class="fa fa-check" aria-hidden="true"></i>
                    {{ t('month_validated_on', {month: formatDate(cra.tempsPassesModifiedAt)}) }}
                </p>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Projet</th>
                        <th>Lun</th>
                        <th>Mar</th>
                        <th>Mer</th>
                        <th>Jeu</th>
                        <th>Ven</th>
                        <th>Sam</th>
                        <th>Dim</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="tempsPasse in cra.tempsPasses" :key="tempsPasse.id">
                        <td>{{ t('project_heading', {project_name: tempsPasse.projet.acronyme}) }}</td>
                        <td v-for="(_, i) in tempsPasse.pourcentages" :key="tempsPasse.id + '-' + i">
                            <p>
                                <input
                                    v-model="tempsPasse.heures[i]"
                                    class="form-control form-control-sm d-inline"
                                    :class="{'is-invalid': !validHours(i)}"
                                />
                                h
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <p
                v-if="!validCra(cra)"
                class="text-danger text-center"
            >
                {{ t('invalid_hours', {max: hoursPerDays}) }}
            </p>
            <button
                type="submit"
                class="mt-5 btn btn-success btn-lg mx-auto d-block"
                :disabled="submitting || !validCra(cra)"
            >{{ t('update') }}</button>
        </form>
    </div>
</template>

<script>
import { format, parseISO } from 'date-fns';
import locale from '../dateFnsLocale';
import { directive as tippy } from 'vue-tippy';
import { addHtmlFlashMessage, addToastrFlashMessage, clearHtmlFlashMessages } from './../flash-messages';
import { t } from '../translation';
import daily from './period/daily';

const strategy = daily;
const { round } = Math;

export default {
    directives: {
        tippy,
    },

    props: {
        urlToAbsences: {
            type: String,
        },
        hoursPerDays: {
            type: Number,
        },
    },

    data() {
        return {
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

            this.selectedDate = strategy.calculateInitialDate(new Date(
                parseInt(year, 10),
                parseInt(month, 10) - 1,
                7,
            ));
        }
    },

    methods: {
        t,

        loadCurrentTempsPasses() {
            this.cra = null;

            strategy.apiLoadCra(this.selectedDate)
                .then(response => response.json())
                .then(cra => {
                    if (cra.tempsPassesModifiedAt) {
                        cra.tempsPassesModifiedAt = parseISO(cra.tempsPassesModifiedAt);
                    }

                    cra.tempsPasses = cra.tempsPasses.map(tempsPasse => {
                        tempsPasse.heures = tempsPasse.pourcentages
                            .map(p => (this.hoursPerDays * p) / 100.0)
                            .map(n => round(n * 10000.0) / 10000.0)
                        ;

                        return tempsPasse;
                    });

                    this.cra = cra;
                })
            ;
        },

        next() {
            this.selectedDate = strategy.calculateNextDate(this.selectedDate);
            this.loadCurrentTempsPasses();
        },

        prev() {
            this.selectedDate = strategy.calculatePrevDate(this.selectedDate);
            this.loadCurrentTempsPasses();
        },

        formatDate(date) {
            if (!date) {
                return '-';
            }

            return format(date, 'EEEE d LLLL yyyy', {locale});
        },

        validHours(dayIndex) {
            const totalHours = this.cra.tempsPasses.reduce((sum, tempsPasse) => {
                return sum + parseFloat(tempsPasse.heures[dayIndex]);
            }, 0);

            return totalHours <= this.hoursPerDays;
        },

        validCra(cra) {
            for (let i = 0; i < 7; ++i) {
                if (!this.validHours(i)) {
                    return false;
                }
            }

            return true;
        },

        submitCra(e) {
            e.preventDefault();

            clearHtmlFlashMessages();
            this.submitting = true;

            this.cra.tempsPasses = this.cra.tempsPasses.map(tempsPasse => {
                tempsPasse.pourcentages = tempsPasse.heures
                    .map(h => h * 100.0 / this.hoursPerDays)
                ;

                return tempsPasse;
            });

            strategy.apiSaveCra(this.cra, this.selectedDate)
                .then(() => {
                    this.submitting = false;
                    this.cra.tempsPassesModifiedAt = new Date();

                    addToastrFlashMessage('success', t('time_spent_updated'));

                    if (this.urlToAbsences) {
                        const url = this.urlToAbsences
                            .replace('1111', this.selectedDate.getFullYear())
                            .replace('99', ('0' + (this.selectedDate.getMonth() + 1)).substr(-2))
                        ;

                        addHtmlFlashMessage('warning', t('enter_absences_if_taken_this_month', {
                            link_start: `<a href="${url}" class="alert-link">`,
                            link_end: `</a>`,
                            interpolation: {
                                escapeValue: false,
                            },
                        }));
                    }
                })
            ;

            return false;
        },
    },

    computed: {
        displayDate() {
            return strategy.displaySelectedDate(this.selectedDate);
        },

        isCurrentDate() {
            return strategy.isCurrentDate(this.selectedDate);
        },
    },
};
</script>

<style scoped>
    .saisie-des-temps-daily input {
        width: 4em;
    }
    .saisie-des-temps-daily table tbody td {
        padding: 0.25em;
        width: 4.5em;
        white-space: nowrap;
    }
    .saisie-des-temps-daily table thead th {
        border-bottom: none;
    }
    .saisie-des-temps-daily table tbody td:nth-last-child(-n+2){
        background-color: #788189;
        border-color: #788189;
    }
</style>
