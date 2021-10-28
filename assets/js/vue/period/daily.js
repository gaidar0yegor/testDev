import daily from './weekly';

daily.apiLoadCra = date => {
    const year = date.getFullYear();
    const month = ('0' + (date.getMonth() + 1)).substr(-2);
    const day = ('0' + date.getDate()).substr(-2);

    return fetch(`/api/temps/daily/${year}/${month}/${day}`);
};

daily.apiSaveCra = (cra, date) => {
    const year = date.getFullYear();
    const month = ('0' + (date.getMonth() + 1)).substr(-2);
    const day = ('0' + date.getDate()).substr(-2);

    return fetch(`/api/temps/daily/${year}/${month}/${day}`, {
        method: 'post',
        headers: {
            'Content-Type': 'application/json;charset=utf-8',
        },
        body: JSON.stringify(cra.tempsPasses.map(tempsPasse => ([
            tempsPasse.projet.id,
            tempsPasse.pourcentages,
        ]))),
    });
};

export default daily;
