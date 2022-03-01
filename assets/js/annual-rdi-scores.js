window.addEventListener('projet-score-rdi-year-changed', event => {
    let yearSwitcher = document.querySelector('[data-event-name="projet-score-rdi-year-changed"]');
    const {annualRdiScores} = yearSwitcher.dataset;
    const object = JSON.parse(annualRdiScores);
    let rdiValueElement = document.querySelector('.rdi-percent .rdi-value');
    rdiValueElement.innerText = Math.ceil(object[event.detail.year] * 100);
});

