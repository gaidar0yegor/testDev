import '../styles/toggle-password.css';
import $ from "jquery";

$(document).on('click', '.showHiddenPassword-toggle', function (e) {
    const _passwordField = $('#' + $(this).data('targetId'));
    const _showHideToggle = $('#showHideToggle-' + $(this).data('targetId'));
    if ($(_showHideToggle).hasClass('fa-eye-slash')) {
        $(_showHideToggle).removeClass('fa-eye-slash');
        $(_showHideToggle).addClass('fa-eye');
        $(_passwordField).attr('type', 'text');
    } else {
        $(_showHideToggle).removeClass('fa-eye');
        $(_showHideToggle).addClass('fa-eye-slash');
        $(_passwordField).attr('type', 'password');
    }
});