import $ from "jquery";
import { initSelect2 } from '../select2';

if (window['assignUsersForm']){
    const projectId = window['assignUsersForm'].dataset.projectId;
    let $tasksSelect = $("select[name='assign_users_form[projetPlanningTask]']");
    let $form = $('#assignUsersForm');
    let $btnSubmit = $($form).find('.btn-validate-assign');

    $('#assignUsers').on('shown.bs.modal', function () {
        $btnSubmit.prop('disabled', true);
        $('#tasksParticipants').empty();
        $('.success-msg').remove();
    });

    $(document).on('click', '.gantt-controls [data-action="assignUsers"]', function (e) {
        $.ajax({
            url: `/corp/api/projet/${projectId}/planning/list`,
            method: 'GET',
            success: function (response) {
                var tasks = response.data;
                $($tasksSelect).empty();
                $($tasksSelect).append('<option>Sélectionnez une tâche</option>');

                $.each( tasks, function (i, task) {
                    $($tasksSelect).append(`<option value="${task.id}">${task.text}</option>`);
                });

                $($tasksSelect).css('width', '100%');
                $('#assignUsers').modal('show');
                initSelect2($($tasksSelect));
            },
        });
    });

    $($tasksSelect).on('select2:select', function (e) {
        $('#tasksParticipants').empty();
        $('.success-msg').remove();

        var task = e.params.data;
        var $tasksParticipantsSelect = $('<select name="assign_users_form[participants][]" class="select-2 form-control w-100" data-placeholder="Sélectionner des collaborateurs" multiple></select>');

        $.ajax({
            url: `/corp/api/projet/${projectId}/planning/participants/${task.id}`,
            method: 'GET',
            success: function (response) {
                let participants = response.data;

                $.each( participants, function (i, participant) {
                    $($tasksParticipantsSelect).append(`<option value="${participant.id}" ${participant.assigned ? "selected" : ""}>${participant.fullName}</option>`);
                });

                $('#tasksParticipants').append($tasksParticipantsSelect);
                $($tasksParticipantsSelect).css('width', '100%');
                initSelect2($($tasksParticipantsSelect));
            },
            error: function (response) {
                $('#tasksParticipants').html(`<p class="text-center">${response.responseJSON.message}</p>`);
            }
        });
    });

    $(document).on('select2:select select2:unselecting', "select[name='assign_users_form[participants][]']", function (e) {
        $btnSubmit.prop('disabled', false);
        $('.success-msg').remove();
    });

    $($form).submit(function( event ) {
        event.preventDefault();

        $.ajax({
            url: `/corp/api/projet/${projectId}/planning/participants/${$($tasksSelect).val()}`,
            method: 'POST',
            data: {
                assigned: $("select[name='assign_users_form[participants][]']").val()
            },
            success: function (response) {
                $('#tasksParticipants').append('<p class="text-success success-msg">Tâche affectée avec succés.</p>');
                $btnSubmit.prop('disabled', true);
            }
        });
    });

    $(document).on('click', '.show-assigned-to-task', function (e) {
        var taskId = $(this).data('taskId');
        var $modal = $('#assignedUsers');

        $($modal).find('.modal-body').empty();

        $.ajax({
            url: `/corp/api/projet/${projectId}/planning/participants/${taskId}`,
            method: 'GET',
            success: function (response) {
                let participants = response.data,
                    noAssignedUser = true,
                    $html = $('<ul></ul>');
                $.each( participants, function (i, participant) {
                    if (participant.assigned){
                        noAssignedUser = false;
                        $($html).append(`<li>${participant.fullName}</li>`)
                    }
                });

                if (noAssignedUser){
                    $html = $('<p class="text-center m-2">Aucun utilisateur est affecté à cette tâche.</p>')
                }

                $($modal).find('.modal-body').html($html);
                $($modal).modal('show');
            },
        });
    });
}