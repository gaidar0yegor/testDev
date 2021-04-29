/**
 * Lorsqu'on arrive directement sur un fait marquant
 * avec une url de type "...#fait-marquant-42",
 * ca scrolle déjà vers ce fait marquant,
 * mais ce script js affiche aussi ce fait marquant en surbrillance
 * pour attirer l'attention dessus.
 */

const highlightCurrentFaitMarquant = () => {
    const {hash} = window.location;

    if (!hash || !hash.startsWith('#fait-marquant-')) {
        return;
    }

    const elementToHighlight = window[hash.substr(1)];

    if (!elementToHighlight) {
        return;
    }

    const {classList} = elementToHighlight.querySelector('h3');

    classList.add('highlight-text');
    classList.add('d-inline');
};

document.addEventListener('DOMContentLoaded', highlightCurrentFaitMarquant);
