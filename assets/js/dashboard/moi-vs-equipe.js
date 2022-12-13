import {formatHours} from './utils';
import tippy from "tippy.js";

const displayGraph = year => {

    let my_contributions = document.querySelector('div.myContributions');

    if (my_contributions){
        my_contributions.querySelector('.moi-vs-equipe.moi.projet').innerText = '…';
        my_contributions.querySelector('.moi-vs-equipe.equipe.projet').innerText = '…';
        my_contributions.querySelector('.moi-vs-equipe.moi.projet-rdi').innerText = '…';
        my_contributions.querySelector('.moi-vs-equipe.equipe.projet-rdi').innerText = '…';
        my_contributions.querySelector('.moi-vs-equipe.moi.temps-total').innerText = '…';
        my_contributions.querySelector('.moi-vs-equipe.equipe.temps-total').innerText = '…';

        fetch(`/corp/api/dashboard/moi-vs-equipe/${year}`)
            .then(response => response.json())
            .then(moiVsEquipe => {
                    my_contributions.querySelector('.moi-vs-equipe.moi.projet').innerText = moiVsEquipe.projets.moi;
                    my_contributions.querySelector('.moi-vs-equipe.equipe.projet').innerText = moiVsEquipe.projets.equipe;
                    my_contributions.querySelector('.moi-vs-equipe.moi.projet-rdi').innerText = moiVsEquipe.projetsRdi.moi;
                    my_contributions.querySelector('.moi-vs-equipe.equipe.projet-rdi').innerText = moiVsEquipe.projetsRdi.equipe;
                    my_contributions.querySelector('.moi-vs-equipe.moi.temps-total').innerText = formatHours(moiVsEquipe.tempsTotal.moi);
                    my_contributions.querySelector('.moi-vs-equipe.equipe.temps-total').innerText = formatHours(moiVsEquipe.tempsTotal.equipe);
                    my_contributions.querySelector('.moi-vs-equipe.moi.temps-total').setAttribute('title', "Sur "+ moiVsEquipe.moisValides.moi +" mois validés");
                    tippy('.moi-vs-equipe.moi.temps-total', {
                        content: element => element.getAttribute('title'),
                        onCreate(instance) {
                            instance.reference.removeAttribute('title');
                        },
                    });

                document.querySelector('[data-target-highlight="my_contributions"]').innerText = formatHours(moiVsEquipe.tempsTotal.moi);
            })
        ;
    }
};

window.addEventListener('loadYearlyCharts', event => displayGraph(parseInt(event.detail.year, 10)));
