import '../styles/user-notifications.scss';

import $ from 'jquery';
import { format } from 'date-fns';
import locale from './dateFnsLocale';
import userContext from './userContext';

$(() => {
    if (0 === document.querySelectorAll('.user-rappels-notifications-list').length) {
        return;
    }

    if (!userContext.userId) {
        return;
    }

    /**
     * @param {Date} date0
     * @param {Date} date1
     *
     * @returns {Boolean} Whether date0 and date1 are in the same month.
     */
    const sameMonth = (date0, date1) => date0.getMonth() === date1.getMonth() && date0.getFullYear() === date1.getFullYear();

    /**
     * Retrieve last user notifications.
     */
    const apiGetUserNotifications = () => fetch(`/api/rappel/notifications/${userContext.userId}`);

    /**
     * Mark the list of notifications defined by their ids in acknowledgeIds as read.
     * The url is the same as the get request because it updates the resource,
     * then the http cache is be cleared.
     *
     * @param {Number[]} acknowledgeIds
     */
    const apiPostUserNotifications = acknowledgeIds => fetch(`/api/rappel/notifications/${userContext.userId}`, {
        method: 'post',
        headers: {
            'Content-Type': 'application/json;charset=utf-8',
        },
        body: JSON.stringify({
            acknowledgeIds,
        }),
    });

    /**
     * @var {Number[]} acknowledgeIds Stores unread notifications ids that user just read,
     *                                       in order to re-post them to acknowledge.
     */
    let acknowledgeIds = [];

    apiGetUserNotifications()
        .then(response => response.json())
        .then(json => {
            let currentMonth = null;

            $('.rappels-notification-container').empty();

            if (0 === json.rappels.length) {
                $('.rappels-notification-container')
                    .append(`<span class="dropdown-item disabled mt-3"><small>Vous n'avez pas de rappels.</small></span>`)
                ;

                return;
            }

            json.rappels.forEach(rappel => {
                const date = new Date(rappel.rappelDate);

                if (null === currentMonth || !sameMonth(date, currentMonth)) {
                    currentMonth = date;

                    $('.rappels-notification-container')
                        .append(`<h6 class="dropdown-header">${format(date, 'MMMM', {locale})}</h6>`)
                    ;
                }

                const $notification = $(`<span class="dropdown-item"><small><i class="fa fa-clock-o" aria-hidden="true"></i> Rappel - ${format(date, 'd MMMM Y H:m', {locale})} | ${rappel.titre}</small></span>`);

                if (!rappel.acknowledged) {
                    acknowledgeIds.push(rappel.id)

                    $notification.find('small').wrapInner('<strong>');
                    $notification.find('strong').prepend('<i class="fa fa-circle text-danger" aria-hidden="true"></i> ')
                }

                $('.rappels-notification-container').append($notification);
            });

            if (acknowledgeIds.length > 0) {
                $('.user-rappels-notifications-list .dropdown-toggle')
                    .append(`<span class="badge badge-light badge-pill">${acknowledgeIds.length}</span>`)
                ;

                $('.user-rappels-notifications-list > .dropdown-toggle > .fa-clock-o')
                    .removeClass('opacity-25')
                ;
            }
        })
    ;

    $('.user-rappels-notifications-list .dropdown-toggle').on('click', () => {
        if (0 === acknowledgeIds.length) {
            return;
        }

        apiPostUserNotifications(acknowledgeIds)
            .then(() => {
                $('.user-rappels-notifications-list .dropdown-toggle .badge').remove();

                $('.user-rappels-notifications-list > .dropdown-toggle > .fa-clock-o')
                    .addClass('opacity-25')
                ;
            })
        ;

        acknowledgeIds = [];
    });
});
