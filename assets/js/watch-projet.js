/**
 * Send api request to watch or unwatch a projet.
 *
 * @param {number} projetId
 * @param {boolean} watch
 */
const apiProjetWatch = async (projetId, watch) => {
    return fetch(`/corp/api/projet/${projetId}/${watch ? 'watch' : 'unwatch'}`, {method: 'post'});
};

const main = () => {
    const watchProjets = document.querySelectorAll('.watch-projet');

    if (!watchProjets) {
        return;
    }

    watchProjets.forEach(watchProjet => {
        const projetId = watchProjet.dataset.projetId;
        const button = watchProjet.querySelector('button');

        button
            .addEventListener('click', async () => {
                button.setAttribute('disabled', 'disabled');

                const watch = button.classList.contains('btn-outline-primary');

                await apiProjetWatch(projetId, watch);

                button.removeAttribute('disabled');

                if (watch) {
                    button.innerHTML = '<i class="fa fa-eye"></i> Suivre le projet <i class="fa fa-check"></i>';
                    button.classList.remove('btn-outline-primary');
                    button.classList.add('btn-primary');
                } else {
                    button.innerHTML = '<i class="fa fa-eye"></i> Suivre le projet';
                    button.classList.add('btn-outline-primary');
                    button.classList.remove('btn-primary');
                }
            })
        ;
    });
};

main();
