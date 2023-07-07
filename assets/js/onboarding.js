import '../../node_modules/smartwizard/dist/css/smart_wizard_all.min.css';
import '../styles/onboarding.scss';

import $ from 'jquery';

$(() => {
    $('.close-onboarding').on('click', e => {
        e.preventDefault();

        const undoneSteps = $.map($.makeArray($('.onboarding-messages .nav-link:not(.done) a')), $a => $a.textContent);

        let confirmMessage = 'Êtes-vous sûr de ne plus vouloir être guidé pour les étapes :';
        confirmMessage += undoneSteps.map(step => '\n  - ' + step).join(',');

        if (!confirm(confirmMessage)) {
            return;
        }

        $.post('/corp/api/onboarding/close');

        $('.onboarding-messages').remove();
    });
});
