import $ from 'jquery';

/*
 * Yearly charts
 */
function loadYearlyCharts(year) {
    window.dispatchEvent(new CustomEvent('loadYearlyCharts', {detail: {year}}));
}

const year = (new Date()).getFullYear();
const $dropdownYear = $('#dropdownYear');

$dropdownYear.append(`<option selected value="${year}">${year}</option>`);

for (let i = 1; i < 5; ++i) {
    $dropdownYear.append(`<option value="${year - i}">${year - i}</option>`);
}

$dropdownYear.change(function () {
    loadYearlyCharts(this.value);
});

loadYearlyCharts(year);

/*
 * "Since years" charts
 */
function loadSinceYearCharts(year) {
    window.dispatchEvent(new CustomEvent('loadSinceYearCharts', {detail: {year}}));
}

const $dropdownSinceYear = $('#dropdownSinceYear');

$dropdownSinceYear.append(`<option value="${year}">${year}</option>`);

for (let i = 1; i < 5; ++i) {
    $dropdownSinceYear
        .append(`<option ${i === 2 ? 'selected' : ''} value="${year - i}">${year - i}</option>`)
    ;
}

$dropdownSinceYear.change(function () {
    loadSinceYearCharts(this.value);
});

loadSinceYearCharts(year - 2);

import './dashboard/temps-saisis';

import './dashboard/heures-par-projet';
import './dashboard/moi-vs-equipe';
import './dashboard/projets-statuts';
import './dashboard/projets-rdi-vs-non-rdi';
