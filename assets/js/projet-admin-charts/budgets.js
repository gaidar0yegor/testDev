import c3 from 'c3';
import $ from "jquery";

const chartContents = window['projet-budget-charts'];

if (chartContents) {
    const projectId = chartContents.dataset.projetId;

    let specialExpensesModal = window['specialExpenses'];
    let projetRevenuesModal = window['projetRevenues'];

    let listSpecialExpenses = $(specialExpensesModal).find(".list-special-expenses");
    let listProjetRevenues = $(projetRevenuesModal).find(".list-projet-revenues");

    let hourBudgetDiv = window['hour-budget'];
    let euroBudgetDiv = window['euro-budget'];
    let euroRevenueDiv = window['euro-revenue'];

    const hourBudgetChart = c3.generate({
        bindto: hourBudgetDiv,
        data: { type: 'bar', columns: [], colors: {} },
        bar: { width: { ratio: 0.3 } },
        axis: { rotated: true, x: { show:false }, y: { show:false } },
        tooltip: {
            format: {
                title: function (d) { return "Analyse budgétaire en Heure (H)"; },
                value: function (value, ratio, id) {
                    return value + ' H';
                }
            }
        }
    });

    const euroBudgetChart = c3.generate({
        bindto: euroBudgetDiv,
        data: { type: 'bar', columns: [], colors: {} },
        bar: { width: { ratio: 0.3 } },
        axis: { rotated: true, x: { show:false }, y: { show:false } },
        tooltip: {
            format: {
                title: function (d) { return `Analyse budgétaire en ${euroBudgetDiv.dataset.devise}`; },
                value: function (value, ratio, id) {
                    return `${value} ${euroBudgetDiv.dataset.devise}`;
                }
            }
        }
    });

    const euroRevenueChart = c3.generate({
        bindto: euroRevenueDiv,
        data: { type: 'bar', columns: [], colors: {} },
        bar: { width: { ratio: 0.3 } },
        axis: { rotated: true, x: { show:false }, y: { show:false } },
        tooltip: {
            format: {
                title: function (d) { return `Retour sur investissement (ROI)`; },
                value: function (value, ratio, id) {
                    return `${value} ${euroRevenueDiv.dataset.devise}`;
                }
            }
        }
    });

    /********************* Expense budget ********************************/

    fetch(`/corp/api/stats/budgets/${chartContents.dataset.projetId}`)
        .then(response => {
            if (response.status === 500){
                response.json().then(response => {
                    chartContents.querySelector('.charts').innerHTML = `<div class="alert alert-warning w-100 mt-4" role="alert">
                        <i class="fa fa-clock-o" aria-hidden="true"></i>
                            ${response.message}
                        </a></div>`
                });
            } else {
                response.json().then(response => {
                    let budgets = response.budgets;

                    hourBudgetChart.load({
                        unload: true,
                        columns: [
                            ['Prévisionnel', Math.ceil(budgets.heure.prev)],
                            ['Réel', Math.ceil(budgets.heure.reel)]
                        ],
                        colors: {
                            Prévisionnel: '#91a6c7',
                            Réel: Math.ceil(budgets.heure.prev) >= Math.ceil(budgets.heure.reel) ? '#00ff00' : '#ce352c',
                        }
                    });

                    euroBudgetChart.load({
                        unload: true,
                        columns: [
                            ['Prévisionnel', Math.ceil(budgets.euro.prev)],
                            ['Réel', Math.ceil(budgets.euro.reel)]
                        ],
                        colors: {
                            Prévisionnel: '#91a6c7',
                            Réel: Math.ceil(budgets.euro.prev) >= Math.ceil(budgets.euro.reel) ? '#00ff00' : '#ce352c',
                        }
                    });
                });
            }
        });

    $('#projet-budget-charts').on('click', '.btn-add-expenses', function () {
        $(specialExpensesModal).modal('show');
    });

    $(specialExpensesModal).on('shown.bs.modal', function () {
        $(this).find('form').trigger("reset");
    });

    $(specialExpensesModal).find('form').submit(function( event ) {
        event.preventDefault();

        let titre = $(this).find("input[name='special_expense_form[titre]']").val();
        let amount = $(this).find("input[name='special_expense_form[amount]']").val();
        let date = $(this).find("input[name='special_expense_form[date]']").val();
        let updateId = $(this).find("input[name='special_expense_form[updateId]']").val();

        if (titre && amount){
            $.ajax({
                url: `/corp/api/projet/${projectId}/budget-expense/save`,
                method: 'POST',
                data: {
                    titre: titre,
                    amount: amount,
                    date: date,
                    updateId: updateId,
                },
                success: function (response) {
                    let addToEuroBudgetChart = parseFloat(response.amount),
                        $oldTr = $(listSpecialExpenses).find(`tr[data-expense-id='${response.id}']`),
                        $newTr = $("<tr>", {"data-expense-id": response.id});

                    $($newTr).html(`
                            <td class="expense-titre">${response.titre}</td>
                            <td class="expense-date">${response.date}</td>
                            <td class="expense-amount">${response.amount}</td>
                            <td>
                                <a href="javascript:;" class="text-warning btn-edit-expense"><i class="fa fa-pencil"></i></a>
                                <a href="javascript:;" class="text-danger btn-delete-expense"><i class="fa fa-trash"></i></a>
                            </td>`);

                    if ($oldTr.length){
                        addToEuroBudgetChart = addToEuroBudgetChart - parseFloat($($oldTr).find('.expense-amount').text());
                        $(listSpecialExpenses).find(`tbody tr[data-expense-id='${response.id}']`).html($($newTr).html());
                    } else {
                        $(listSpecialExpenses).find('tbody').append($newTr);
                    }

                    euroBudgetChart.load({
                        unload: ['Réel'],
                        columns: [
                            ['Réel', Math.ceil(euroBudgetChart.data("Réel")[0].values[0].value + addToEuroBudgetChart)]
                        ],
                    });

                    if (euroRevenueDiv){
                        let revenue = euroRevenueChart.data("Revenue")[0].values[0].value,
                            depense = Math.ceil(euroRevenueChart.data("Dépense")[0].values[0].value + addToEuroBudgetChart);

                        euroRevenueChart.load({
                            unload: ['Dépense'],
                            columns: [
                                ['Dépense', depense]
                            ],
                        });

                        updateRoiPercent(revenue, depense);
                    }

                    $(specialExpensesModal).find('form').trigger("reset");
                    $(specialExpensesModal).find('form input[name="special_expense_form[updateId]"]').val('')
                },
            });
        }

    });

    $(listSpecialExpenses).on('click', '.btn-edit-expense', function (e) {
        const $tr = $(this).parents('tr');
        const expenseId = $($tr).data('expenseId');

        $(specialExpensesModal).find('form').find("input[name='special_expense_form[updateId]']").val(expenseId);
        $(specialExpensesModal).find('form').find("input[name='special_expense_form[titre]']").val($($tr).find('.expense-titre').text());
        $(specialExpensesModal).find('form').find("input[name='special_expense_form[date]']").val($($tr).find('.expense-date').text());
        $(specialExpensesModal).find('form').find("input[name='special_expense_form[amount]']").val(parseFloat($($tr).find('.expense-amount').text()));
    });

    $(listSpecialExpenses).on('click', '.btn-delete-expense', function (e) {
        const $tr = $(this).parents('tr');
        const expenseId = $($tr).data('expenseId');

        if (expenseId){
            $.ajax({
                url: `/corp/api/projet/${projectId}/budget-expense/delete/${expenseId}`,
                method: 'DELETE',
                success: function (response) {
                    $($tr).remove();

                    euroBudgetChart.load({
                        unload: ['Réel'],
                        columns: [
                            ['Réel', Math.ceil(euroBudgetChart.data("Réel")[0].values[0].value - parseFloat(response.amount))]
                        ],
                    });

                    if (euroRevenueDiv){
                        let revenue = euroRevenueChart.data("Revenue")[0].values[0].value,
                            depense = Math.ceil(euroRevenueChart.data("Dépense")[0].values[0].value - parseFloat(response.amount));

                        euroRevenueChart.load({
                            unload: ['Dépense'],
                            columns: [
                                ['Dépense', depense]
                            ],
                        });

                        updateRoiPercent(revenue, depense);
                    }

                },
            });
        }
    });

    /********************* Revenue budget ********************************/

    if (euroRevenueDiv){
        fetch(`/corp/api/stats/revenues/${chartContents.dataset.projetId}`)
            .then(response => {
                if (response.status === 500){
                    response.json().then(response => {
                        chartContents.querySelector('.charts').innerHTML = `<div class="alert alert-warning w-100 mt-4" role="alert">
                        <i class="fa fa-clock-o" aria-hidden="true"></i>
                            ${response.message}
                        </a></div>`
                    });
                } else {
                    response.json().then(response => {
                        let expense = response.roi.expense,
                            revenue = response.roi.revenue;

                        euroRevenueChart.load({
                            unload: true,
                            columns: [
                                ['Dépense', Math.ceil(expense)],
                                ['Revenue', Math.ceil(revenue)]

                            ],
                            colors: {
                                Dépense: '#ce352c',
                                Revenue: '#5dc041',
                            }
                        });

                        updateRoiPercent(revenue, expense);
                    });
                }
            });

        $('#projet-budget-charts').on('click', '.btn-add-revenue', function () {
            $(projetRevenuesModal).modal('show');
        });

        $(projetRevenuesModal).on('shown.bs.modal', function () {
            $(this).find('form').trigger("reset");
        });

        $(projetRevenuesModal).find('form').submit(function( event ) {
            event.preventDefault();

            let titre = $(this).find("input[name='projet_revenues_form[titre]']").val();
            let amount = $(this).find("input[name='projet_revenues_form[amount]']").val();
            let date = $(this).find("input[name='projet_revenues_form[date]']").val();
            let updateId = $(this).find("input[name='projet_revenues_form[updateId]']").val();

            if (titre && amount){
                $.ajax({
                    url: `/corp/api/projet/${projectId}/revenue/save`,
                    method: 'POST',
                    data: {
                        titre: titre,
                        amount: amount,
                        date: date,
                        updateId: updateId,
                    },
                    success: function (response) {
                        let addToEuroRevenueChart = parseFloat(response.amount),
                            $oldTr = $(listProjetRevenues).find(`tr[data-revenue-id='${response.id}']`),
                            $newTr = $("<tr>", {"data-revenue-id": response.id});

                        $($newTr).html(`
                            <td class="revenue-titre">${response.titre}</td>
                            <td class="revenue-date">${response.date}</td>
                            <td class="revenue-amount">${response.amount}</td>
                            <td>
                                <a href="javascript:;" class="text-warning btn-edit-revenue"><i class="fa fa-pencil"></i></a>
                                <a href="javascript:;" class="text-danger btn-delete-revenue"><i class="fa fa-trash"></i></a>
                            </td>`);

                        if ($oldTr.length){
                            addToEuroRevenueChart = addToEuroRevenueChart - parseFloat($($oldTr).find('.revenue-amount').text());
                            $(listProjetRevenues).find(`tbody tr[data-revenue-id='${response.id}']`).html($($newTr).html());
                        } else {
                            $(listProjetRevenues).find('tbody').append($newTr);
                        }

                        let revenue = Math.ceil(euroRevenueChart.data("Revenue")[0].values[0].value + addToEuroRevenueChart),
                            depense = euroRevenueChart.data("Dépense")[0].values[0].value;

                        euroRevenueChart.load({
                            unload: ['Revenue'],
                            columns: [
                                ['Revenue', revenue]
                            ],
                        });

                        updateRoiPercent(revenue, depense);

                        $(projetRevenuesModal).find('form').trigger("reset");
                        $(projetRevenuesModal).find('form input[name="projet_revenues_form[updateId]"]').val('')
                    },
                });
            }

        });

        $(listProjetRevenues).on('click', '.btn-edit-revenue', function (e) {
            const $tr = $(this).parents('tr');
            const revenueId = $($tr).data('revenueId');

            $(projetRevenuesModal).find('form').find("input[name='projet_revenues_form[updateId]']").val(revenueId);
            $(projetRevenuesModal).find('form').find("input[name='projet_revenues_form[titre]']").val($($tr).find('.revenue-titre').text());
            $(projetRevenuesModal).find('form').find("input[name='projet_revenues_form[date]']").val($($tr).find('.revenue-date').text());
            $(projetRevenuesModal).find('form').find("input[name='projet_revenues_form[amount]']").val(parseFloat($($tr).find('.revenue-amount').text()));
        });

        $(listProjetRevenues).on('click', '.btn-delete-revenue', function (e) {
            const $tr = $(this).parents('tr');
            const revenueId = $($tr).data('revenueId');

            if (revenueId){
                $.ajax({
                    url: `/corp/api/projet/${projectId}/revenue/delete/${revenueId}`,
                    method: 'DELETE',
                    success: function (response) {
                        $($tr).remove();

                        let revenue = Math.ceil(euroRevenueChart.data("Revenue")[0].values[0].value - parseFloat(response.amount)),
                            depense = euroRevenueChart.data("Dépense")[0].values[0].value;

                        euroRevenueChart.load({
                            unload: ['Revenue'],
                            columns: [
                                ['Revenue', revenue]
                            ],
                        });

                        updateRoiPercent(revenue, depense);
                    },
                });
            }
        });
    }
}

function updateRoiPercent(revenue, expense) {
    let roiPercent = window['roi_percent'];
    if (roiPercent){
        roiPercent.innerHTML = expense > 0
            ? Math.ceil(((revenue - expense) / expense) * 100) + '%'
            : 'N/A';
    }
}

