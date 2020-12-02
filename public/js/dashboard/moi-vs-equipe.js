(function (window, selector, fetch) {
    'use strict';

    const {formatHours} = window;

    const displayGraph = year => {

        selector('.moi-vs-equipe.moi.projet').innerText = '…';
        selector('.moi-vs-equipe.equipe.projet').innerText = '…';
        selector('.moi-vs-equipe.moi.projet-rdi').innerText = '…';
        selector('.moi-vs-equipe.equipe.projet-rdi').innerText = '…';
        selector('.moi-vs-equipe.moi.temps-total').innerText = '…';
        selector('.moi-vs-equipe.equipe.temps-total').innerText = '…';

        fetch(`/api/dashboard/moi-vs-equipe/${year}`)
            .then(response => response.json())
            .then(moiVsEquipe => {
                selector('.moi-vs-equipe.moi.projet').innerText = moiVsEquipe.projets.moi;
                selector('.moi-vs-equipe.equipe.projet').innerText = moiVsEquipe.projets.equipe;
                selector('.moi-vs-equipe.moi.projet-rdi').innerText = moiVsEquipe.projetsRdi.moi;
                selector('.moi-vs-equipe.equipe.projet-rdi').innerText = moiVsEquipe.projetsRdi.equipe;
                selector('.moi-vs-equipe.moi.temps-total').innerText = formatHours(moiVsEquipe.tempsTotal.moi);
                selector('.moi-vs-equipe.equipe.temps-total').innerText = formatHours(moiVsEquipe.tempsTotal.equipe);
            })
        ;
    };

    window.addEventListener('loadYearlyCharts', event => displayGraph(parseInt(event.detail.year, 10)));

})(window, document.querySelector.bind(document), fetch);
