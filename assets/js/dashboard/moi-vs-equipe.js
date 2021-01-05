import {formatHours} from './utils';

const displayGraph = year => {

    document.querySelector('.moi-vs-equipe.moi.projet').innerText = '…';
    document.querySelector('.moi-vs-equipe.equipe.projet').innerText = '…';
    document.querySelector('.moi-vs-equipe.moi.projet-rdi').innerText = '…';
    document.querySelector('.moi-vs-equipe.equipe.projet-rdi').innerText = '…';
    document.querySelector('.moi-vs-equipe.moi.temps-total').innerText = '…';
    document.querySelector('.moi-vs-equipe.equipe.temps-total').innerText = '…';

    fetch(`/api/dashboard/moi-vs-equipe/${year}`)
        .then(response => response.json())
        .then(moiVsEquipe => {
            document.querySelector('.moi-vs-equipe.moi.projet').innerText = moiVsEquipe.projets.moi;
            document.querySelector('.moi-vs-equipe.equipe.projet').innerText = moiVsEquipe.projets.equipe;
            document.querySelector('.moi-vs-equipe.moi.projet-rdi').innerText = moiVsEquipe.projetsRdi.moi;
            document.querySelector('.moi-vs-equipe.equipe.projet-rdi').innerText = moiVsEquipe.projetsRdi.equipe;
            document.querySelector('.moi-vs-equipe.moi.temps-total').innerText = formatHours(moiVsEquipe.tempsTotal.moi);
            document.querySelector('.moi-vs-equipe.equipe.temps-total').innerText = formatHours(moiVsEquipe.tempsTotal.equipe);
        })
    ;
};

window.addEventListener('loadYearlyCharts', event => displayGraph(parseInt(event.detail.year, 10)));
