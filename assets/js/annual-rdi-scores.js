window.addEventListener('projet-score-rdi-year-changed', event => {
    let yearSwitcher = document.querySelector('[data-event-name="projet-score-rdi-year-changed"]');
    const {annualRdiScores} = yearSwitcher.dataset;
    const object = JSON.parse(annualRdiScores);
    let rdiPercentElement = document.querySelector('.rdi-percent');
    let rdiValueElement = document.querySelector('.rdi-percent .rdi-value');
    let rdiScore = Math.ceil(object[event.detail.year] * 100);
    rdiValueElement.innerText = rdiScore;
    rdiPercentElement.classList.remove('text-success', 'text-warning', 'text-danger');
    if (rdiScore >= 50){
        rdiPercentElement.classList.add('text-success');
    } else if (rdiScore >= 30){
        rdiPercentElement.classList.add('text-warning');
    } else {
        rdiPercentElement.classList.add('text-danger');
    }
});

