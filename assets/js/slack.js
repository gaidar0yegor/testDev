document
    .querySelectorAll('.remove-slack-access-token')
    .forEach(element => {
        element.addEventListener('click', e => {
            e.preventDefault();

            if (!confirm('RDI-Manager n\'enverra plus de notification sur ce channel. Supprimer cet accès ?')) {
                return;
            }

            fetch('/corp/api/slack/remove-token/' + element.dataset.id, {method: 'post'})
                .then(() => {
                    if (element.closest('ul').querySelectorAll('li').length < 3) {
                        element.closest('ul').remove();
                    } else {
                        element.closest('li').remove();
                    }
                })
            ;
        });
    })
;
