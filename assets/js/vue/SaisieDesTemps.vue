<template>
    <div>
        <div v-if="period === 'daily'">
            <saisie-des-temps-daily
                :urlToAbsences="urlToAbsences"
                :hoursPerDays="hoursPerDays"
            ></saisie-des-temps-daily>
        </div>
        <div v-if="period === 'monthly' || period === 'weekly'" class="row" :class="'saisie-des-temps saisie-des-temps-' + period">
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

                <p v-if="cra && cra.isUserBelongingToSociete === false" class="lead text-center">
                    Impossible de saisir les temps de cette période car vous n'étiez pas dans la société.<br>
                    Dans le cas contraire, contactez votre administrateur RDI-Manager ou votre N+1 pour mettre à jour votre date d’entrée dans la société.
                </p>

                <p v-if="cra && cra.isUserBelongingToSociete === true && cra.tempsPasses.length === 0 " class="lead text-center">
                    Cette période, vous n'avez pas de temps à saisir car vous n'étiez contributeur sur aucun projet.
                </p>

                <form v-if="cra && cra.isUserBelongingToSociete === true && cra.tempsPasses.length > 0 " @submit="submitCra">
                    <div class="message-validation text-center">
                        <p v-if="cra.tempsPassesModifiedAt" class="text-success">
                            <i class="fa fa-check" aria-hidden="true"></i>
                            {{ t('month_validated_on', {month: formatDate(cra.tempsPassesModifiedAt)}) }}
                        </p>
                    </div>

                    <div
                        v-for="tempsPasse in cra.tempsPasses" :key="tempsPasse.id"
                        class="mb-3 d-flex flex-row justify-content-center align-items-center"
                    >

                            <div class="input-group input-group-lg w-50 w-sm-100 m-auto">
                                <div class="input-group-append">
                                    <label :for="'temps_passe_pourcentage_' + tempsPasse.id"
                                           v-tippy="{content: tempsPasse.projet.titre}"
                                           class="input-group-text min-w-12 d-flex flex-column justify-content-start align-items-start pb-1"
                                    >
                                        <div>
                                            <span class="badge d-inline-block rounded-circle mt-1 mr-2" :style="{ 'background-color': tempsPasse.projet.colorCode, 'width': '15px', 'height': '15px' }"></span>
                                            {{ t('project_heading', {project_name: tempsPasse.projet.acronyme}) }}
                                        </div>
                                        <a
                                                href="javascript:;"
                                                v-if="tempsPasse.pourcentageMin"
                                                class="f-s-75"
                                        >
                                            <i class="fa fa-exclamation-circle"></i> {{ t('help_percentage', {min: tempsPasse.pourcentageMin}) }}
                                        </a>
                                    </label>
                                </div>
                                <input
                                    v-model="tempsPasse.pourcentage"
                                    type="number"
                                    class="form-control h-auto"
                                    :class="{'is-invalid': !validPercentage(tempsPasse.pourcentage, tempsPasse.pourcentageMin) || !validPercentagesSum(cra.tempsPasses)}"
                                    :id="'temps_passe_pourcentage_' + tempsPasse.id"
                                    :min="tempsPasse.pourcentageMin"
                                    max="100"
                                    placeholder="0"
                                    required
                                />
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>

                    </div>
                    <p 
                        v-if="!validCra(cra)" 
                        class="text-danger text-center"
                    >
                        {{ t('invalid_percentages', {min: sumPercentageMin(cra.tempsPasses)}) }}
                    </p>
                    <button
                        type="submit"
                        class="mt-5 btn btn-success btn-lg mx-auto d-block"
                        :disabled="submitting || !validCra(cra)"
                    >{{ t('update') }}</button>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { format, parseISO } from 'date-fns';
import locale from '../dateFnsLocale';
import { directive as tippy } from 'vue-tippy';
import { addToastrFlashMessage, addHtmlFlashMessage, clearHtmlFlashMessages } from './../flash-messages';
import { t } from '../translation';
import monthly from './period/monthly';
import weekly from './period/weekly';
import daily from './period/daily';

const strategies = {
    monthly,
    weekly,
    daily,
};

export default {
    directives: {
        tippy,
    },

    props: {
        period: {
            type: String,
            validator: value => 'monthly' === value || 'weekly' === value || 'daily' === value,
            default: 'monthly',
        },
        urlToAbsences: {
            type: String,
        },
        hoursPerDays: {
            type: Number,
        },
    },

    data() {
        const strategy = strategies[this.period];

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

        if (5 === path.length) {
            const [year, month] = path.slice(-2);

            this.selectedDate = this.strategy.calculateInitialDate(new Date(
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

            clearHtmlFlashMessages();
            this.submitting = true;

            this.strategy.apiSaveCra(this.cra, this.selectedDate)
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

        sumPercentageMin(tempsPasses) {
            return tempsPasses.reduce((sum, tempsPasse) => sum + tempsPasse.pourcentageMin, 0);
        },

        validPercentage(percentage, min) {
            return percentage >= min && percentage <= 100;
        },

        validPercentagesSum(tempsPasses) {
            const sumPercentage = tempsPasses.reduce((sum, tempsPasse) => sum + parseInt(tempsPasse.pourcentage, 10), 0);

            return this.validPercentage(sumPercentage, this.sumPercentageMin(tempsPasses));
        },

        validCra(cra) {
            if (cra.tempsPasses.some(tempsPasse => !this.validPercentage(tempsPasse.pourcentage, tempsPasse.pourcentageMin))) {
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
    .min-w-12{
        min-width: 12rem;
    }
    .f-s-75{
        font-size: 0.75rem;
    }
    @media (max-width: 945px) {
        .w-sm-100{
            width: 100% !important;
        }
    }
</style>
