import '../styles/helpText.scss';

const acknowledge = helpId => {
    fetch('/api/help-text/acknowledge', {
        method: 'post',
        headers: {
            'Content-Type': 'application/json;charset=utf-8',
        },
        body: JSON.stringify({
            helpId,
        }),
    });
};

document.querySelectorAll('.rdi-help-text').forEach(helpTextCard => {
    helpTextCard
        .querySelector('.rdi-help-text-ack')
        .addEventListener('click', () => {
            acknowledge(helpTextCard.dataset.helpId);
            helpTextCard.classList.add('ack');

            setTimeout(() => {
                helpTextCard.remove();
            }, 420);
        })
    ;
});
