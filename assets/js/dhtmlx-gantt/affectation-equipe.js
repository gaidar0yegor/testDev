import './../../styles/affectation-equipe.scss';
import $ from "jquery";

$(document).on('change', '#table_affectation_equipe input:checkbox', function (e) {
    var participantValue = $(this).val(),
        isChecked = $(this).prop('checked'),
        taskId = $(this).parents('.planning-task-affectation-participants').data('id');

    $(`.planning-task-affectation-participants[data-parent-id="${taskId}"]`).each(function(key, childRow) {
        $(childRow).find(`input[value="${participantValue}"]`).prop('checked', isChecked);
        var childRowId = $(childRow).data('id');
        $(`.planning-task-affectation-participants[data-parent-id="${childRowId}"]`).each(function(key, subChildRow) {
            $(subChildRow).find(`input[value="${participantValue}"]`).prop('checked', isChecked);
        });
    });
});