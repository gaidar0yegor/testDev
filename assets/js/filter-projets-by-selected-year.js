import $ from 'jquery';

const $projets = $('.filter-projets-by-selected-year');

if ($projets.length > 0) {
    window.addEventListener(
        'year-changed',
        e => {
            const yearSelected = e.detail.year;

            $projets.find('tr').each((i, tr) => {
                const $tr = $(tr);
                const {yearStart, yearEnd} = tr.dataset;

                $tr.show();

                if ('-' !== yearStart && yearSelected < yearStart) {
                    $tr.hide();
                }

                if ('-' !== yearEnd && yearSelected > yearEnd) {
                    $tr.hide();
                }
            });
        },
    );
}
