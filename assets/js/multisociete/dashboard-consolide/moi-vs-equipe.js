import {formatHours} from '../../dashboard/utils';
import tippy from "tippy.js";

const displayGraph = (year,dashboardConsolide) => {

    document.querySelector('.moi-vs-equipe.moi.projet').innerText = '…';
    document.querySelector('.moi-vs-equipe.equipe.projet').innerText = '…';
    document.querySelector('.moi-vs-equipe.moi.projet-rdi').innerText = '…';
    document.querySelector('.moi-vs-equipe.equipe.projet-rdi').innerText = '…';
    document.querySelector('.moi-vs-equipe.moi.temps-total').innerText = '…';
    document.querySelector('.moi-vs-equipe.equipe.temps-total').innerText = '…';

    fetch(`/corp/api/mes-societes/dashboard/consolide/moi-vs-equipe/${year}/${dashboardConsolide ? dashboardConsolide : ""}`)
        .then(response => response.json())
        .then(moiVsEquipe => {
            document.querySelector('.moi-vs-equipe.moi.projet').innerText = moiVsEquipe.projets.moi;
            document.querySelector('.moi-vs-equipe.equipe.projet').innerText = moiVsEquipe.projets.equipe;
            document.querySelector('.moi-vs-equipe.moi.projet-rdi').innerText = moiVsEquipe.projetsRdi.moi;
            document.querySelector('.moi-vs-equipe.equipe.projet-rdi').innerText = moiVsEquipe.projetsRdi.equipe;
            document.querySelector('.moi-vs-equipe.moi.temps-total').innerText = formatHours(moiVsEquipe.tempsTotal.moi);
            document.querySelector('.moi-vs-equipe.equipe.temps-total').innerText = formatHours(moiVsEquipe.tempsTotal.equipe);
            document.querySelector('.moi-vs-equipe.moi.temps-total').setAttribute('title', "Sur "+ moiVsEquipe.moisValides.moi +" mois validés");
            tippy('.moi-vs-equipe.moi.temps-total', {
                content: element => element.getAttribute('title'),
                onCreate(instance) {
                    instance.reference.removeAttribute('title');
                },
            });
        })
    ;
};

window.addEventListener('loadYearlyCharts', event => {
    const {dashboardConsolide} = event.detail;
    const {year} = event.detail;

    displayGraph(parseInt(year, 10),dashboardConsolide);
});