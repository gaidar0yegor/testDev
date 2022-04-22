import c3 from 'c3';
import $ from "jquery";

const chartContents = window['projet-budget-charts'];

if (chartContents) {
    const projectId = chartContents.dataset.projetId;
    let modal = window['specialExpenses'];
    let listSpecialExpenses = $(modal).find(".list-special-expenses");
    let hourBudgetDiv = window['hour-budget'];
    let euroBudgetDiv = window['euro-budget'];

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
                title: function (d) { return "Analyse budgétaire en Euro (€)"; },
                value: function (value, ratio, id) {
                    return value + ' €';
                }
            }
        }
    });

    fetch(`/api/stats/budgets/${chartContents.dataset.projetId}`)
        .then(response => {
            if (response.status === 500){
                response.json().then(response => {
                    chartContents.innerHTML = `<div class="alert alert-warning w-100 mt-4" role="alert">
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
        $(modal).modal('show');
    });

    $(modal).on('shown.bs.modal', function () {
        $(this).find('form').trigger("reset");
    });

    $(modal).find('form').submit(function( event ) {
        event.preventDefault();

        let titre = $(this).find("input[name='special_expense_form[titre]']").val();
        let amount = $(this).find("input[name='special_expense_form[amount]']").val();

        if (titre && amount){
            $.ajax({
                url: `/api/projet/${projectId}/budget-expense/save`,
                method: 'POST',
                data: {
                    titre: titre,
                    amount: amount
                },
                success: function (response) {
                    $(listSpecialExpenses).find('tbody').append(`<tr>
                                        <td>${response.titre}</td>
                                        <td>${response.amount}</td>
                                    </tr>`);

                    $(modal).find('form').trigger("reset");
                },
            });
        }

    });

    $(listSpecialExpenses).on('click', '.btn-delete-expense', function (e) {
        const $tr = $(this).parents('tr');
        const expenseId = $(this).data('expenseId');

        if (expenseId){
            $.ajax({
                url: `/api/projet/${projectId}/budget-expense/delete/${expenseId}`,
                method: 'DELETE',
                success: function (response) {
                    $($tr).remove();
                },
            });
        }
    });
}

