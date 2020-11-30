(function ($, window) {
    'use strict';

    function loadYearlyCharts(year) {
        window.dispatchEvent(new CustomEvent('loadYearlyCharts', {detail: {year}}));
    }

    const year = (new Date()).getFullYear();

    $('#dropdownYear').append(`<option selected value="${year}">${year}</option>`);

    for (let i = 1; i < 5; ++i) {
        $('#dropdownYear').append(`<option value="${year - i}">${year - i}</option>`);
    }

    $('#dropdownYear').change(function () {
        loadYearlyCharts(this.value);
    });

    loadYearlyCharts(year);

})(jQuery, window);
