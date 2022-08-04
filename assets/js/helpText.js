import '../styles/helpText.scss';

import $ from 'jquery';

$(document).ready( function () {
    if ($('.help_text_to_review').length){
        $('#reviewHelpText .fa').removeClass (function (index, className) {
            return (className.match (/(^|\s)opacity-\S+/g) || []).join(' ');
        });
    }
});

const acknowledge = helpId => {
    fetch('/corp/api/help-text/acknowledge', {
        method: 'post',
        headers: {
            'Content-Type': 'application/json;charset=utf-8',
        },
        body: JSON.stringify({
            helpId,
        }),
    });
};
const reactive = helpId => {
    return fetch('/corp/api/help-text/reactive', {
        method: 'post',
        headers: {
            'Content-Type': 'application/json;charset=utf-8',
        },
        body: JSON.stringify({
            helpId,
        }),
    });
};

function bindButtons() {
    document.querySelectorAll('.rdi-help-text').forEach(helpTextCard => {
        helpTextCard
            .querySelector('.rdi-help-text-ack')
            .addEventListener('click', () => {
                acknowledge(helpTextCard.dataset.helpId);
                helpTextCard.classList.add('ack');
                $(helpTextCard).slideUp();
                setTimeout(() => {
                    helpTextCard.remove();
                }, 420);
            })
        ;
    });
};
bindButtons();

if (document.getElementById('reviewHelpText')){
    document.getElementById('reviewHelpText').addEventListener('click', () => {
        document.querySelectorAll('.help_text_to_review').forEach(helpTextToReview => {
            reactive(helpTextToReview.dataset.helpId)
                .then(response => response.text())
                .then(text => {
                    $(helpTextToReview).hide();
                    helpTextToReview.innerHTML = text;
                    $(helpTextToReview).slideDown();
                    bindButtons();
                })
        });
    });
}
