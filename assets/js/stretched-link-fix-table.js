import $ from 'jquery';

/**
 * Hard fix class="stretched-link" on table rows
 * because it does not work on chrome.
 * To use it:
 *      - add class="stretched-link-table-row" on <table>
 *      - then class="stretched-link" on "td a" element
 *
 * {@see https://github.com/twbs/bootstrap/issues/28608}
 */

$('table.stretched-link-table-row tbody td a.stretched-link').each((i, a) => {
    const $a = $(a);
    const $aEmpty = $(a).clone().removeAttr('title').text('');
    const $td = $a.closest('tr').find('td');

    $a.removeClass('stretched-link');

    $td.wrapInner('<div class="position-relative p-2-3">');

    $td
        .addClass('p-0')
        .find('.position-relative')
        .prepend($aEmpty)
    ;
});
