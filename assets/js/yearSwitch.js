import $ from 'jquery';
const detectedLocale = document.querySelector('html').lang || 'fr';

/*
 * Permet de créer un selecteur d'année à partir de :
 *
 *      <div
 *          class="year-switch"
 *          data-event-name="year-changed"  // if not set, does not dispatch event
 *          data-order="asc"                // year order, default "desc"
 *          data-year-selected="2020"       // year to select on load, default current year or first year on the list
 *          data-year-from="2015"           // oldest date to display, default year - 5
 *          data-year-to="2025"             // latest date to display, default current year
 *      ></div>
 *
 * et d'écouter l'event avec :
 *
 *      window.addEventListener(
 *           'year-changed',
 *           e => {
 *               const yearSelected = e.detail.year;
 *               // ...
 *           },
 *      );
 *
 */
$(() => {
    $('.temps-unit-switch').each((_, divSwitch) => {
        const $divSwitch = $(divSwitch);
        const defaultUnit = 'hour';

        const selectedUnit = divSwitch.dataset.unitSelected ? divSwitch.dataset.unitSelected : defaultUnit;

        const $select = $('<select class="border-0 font-weight-bold-unset">' +
            '<option value="hour" selected>'+ (detectedLocale === 'fr' ? 'heure' : 'hour') +'</option>' +
            '<option value="percent">%</option>' +
            '</select>');

        $divSwitch.append($select);
    });
});
$(() => {
    const currentYear = (new Date()).getFullYear();

    $('.year-switch').each((_, divSwitch) => {
        const $divSwitch = $(divSwitch);
        const withUnit = divSwitch.dataset.withUnit ? divSwitch.dataset.withUnit : false;
        const societeUser = divSwitch.dataset.societeUser ? divSwitch.dataset.societeUser : false;
        const dashboardConsolide = divSwitch.dataset.dashboardConsolide ? divSwitch.dataset.dashboardConsolide : false;
        const yearFrom = divSwitch.dataset.yearFrom ? parseInt(divSwitch.dataset.yearFrom, 10) : currentYear - 5;
        const toYear = divSwitch.dataset.yearTo ? parseInt(divSwitch.dataset.yearTo, 10) : currentYear;
        const selectedYear = divSwitch.dataset.yearSelected ? parseInt(divSwitch.dataset.yearSelected, 10) : currentYear;
        const $select = $('<select class="border-0 font-weight-bold-unset">');

        for (let i = yearFrom; i <= toYear; ++i) {
            $select.append(`<option ${i === selectedYear ? 'selected' : ''}>${i}</option>`);
        }

        if (!divSwitch.dataset.order || 'desc' === divSwitch.dataset.order) {
            $select.find('option').each(function () {
                $(this).prependTo($select);
            });
        }

        $divSwitch.append($select);

        if (divSwitch.dataset.eventName) {
            var unit = false;
            if (withUnit){
                unit = $('.' + withUnit + ' select').val()
            }
            const dispatchEvent = (year, unit, societeUser, dashboardConsolide) => {
                window.dispatchEvent(
                    new CustomEvent(
                        divSwitch.dataset.eventName,
                        {
                            detail:{
                                year: year,
                                unit: unit,
                                societeUser: societeUser,
                                dashboardConsolide: dashboardConsolide,
                            }
                        }
                    ));
            };

            $select.on('change', function () {
                dispatchEvent(parseInt(this.value, 10), $('.' + withUnit + ' select').val(), societeUser, dashboardConsolide);
            });
            if (withUnit){
                $('.' + withUnit + ' select').on('change', function () {
                    dispatchEvent(parseInt($(".year-switch[data-with-unit='"+ withUnit +"'] select").val(), 10), this.value, societeUser, dashboardConsolide);
                });
            }
            dispatchEvent(selectedYear, unit, societeUser, dashboardConsolide);
        }
    });
});
