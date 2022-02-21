// import c3 from 'c3';
//
// const chartDiv = window['annual-rdi-scores'];
//
// if (chartDiv) {
//
//     const {annualRdiScores} = chartDiv.dataset;
//
//     const object = JSON.parse(annualRdiScores);
//     let data = ['Score RDI'];
//     let axis_x = ['x'];
//
//     for (var i in object) {
//         axis_x.push(i);
//         data.push(object[i]);
//     }
//
//     console.log([axis_x, data]);
//
//     const chart = c3.generate({
//         bindto: chartDiv,
//         data: {
//             x: 'x',
//             columns: [axis_x, data],
//         },
//         grid: {
//             y: {
//                 lines: [
//                     {value: 0.4, text: 'Line seuil 1'},
//                     {value: 0.6, text: 'Line seuil 2'},
//                 ]
//             }
//         },
//         axis: {
//             y: {
//                 max: 1,
//             }
//         },
//         padding: {
//             right: 50,
//             left: 50,
//         },
//     });
// }

window.addEventListener('projet-score-rdi-year-changed', event => {
    let yearSwitcher = document.querySelector('[data-event-name="projet-score-rdi-year-changed"]');
    const {annualRdiScores} = yearSwitcher.dataset;
    const object = JSON.parse(annualRdiScores);
    let rdiValueElement = document.querySelector('.rdi-percent .rdi-value');
    rdiValueElement.innerText = Math.ceil(object[event.detail.year] * 100);
});

