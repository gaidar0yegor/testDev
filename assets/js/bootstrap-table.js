import '../../node_modules/bootstrap-table/dist/bootstrap-table';
import '../../node_modules/bootstrap-table/dist/bootstrap-table.css';
import '../../node_modules/bootstrap-table/dist/locale/bootstrap-table-fr-FR.js';
import '../../node_modules/bootstrap-table/dist/locale/bootstrap-table-en-US.js';

import $ from 'jquery';
import { detectedLocale } from './translation';

/**
 * Sort a custom value.
 * Example:
 *  - add sort options on column with `data-sortable="true" data-sorter="bySorterValue"`:
 *      <thead>
 *          <tr>
 *              <th data-sortable="true" data-sorter="bySorterValue">Projet</th>
 *              ...
 *
 *  - add custom sort value in `data-sorter-value` attribute
 *      <tbody>
 *          <tr>
 *              <td data-sorter-value="{{ projet.acronyme }}"><a href="/projet/X">{{ projet.acronyme }}</a></td>
 *              ...
 */
const bySorterValue = (a, b, aRow, bRow) => {
    let i = 0;

    while (i in aRow && aRow[i] !== a) ++i;

    if (!(i in aRow)) {
        return 0;
    }

    const dataIndex = `_${i}_data`;
    const aValue = aRow[dataIndex]['sorter-value'];
    const bValue = bRow[dataIndex]['sorter-value'];

    if (aValue === bValue) {
        return 0;
    }

    return aValue > bValue ? 1 : -1;
}

/**
 * Remove 'table-bordered' class added by bootstrapTable.
 *
 * @see https://bootstrap-table.com/
 */
$(() => {
    $('table[data-toggle="table"]').bootstrapTable('refreshOptions', {
        classes: '',
        locale: detectedLocale,
    });
});

global.bySorterValue = bySorterValue;
