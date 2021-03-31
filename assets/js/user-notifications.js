import '../styles/user-notifications.scss';

import $ from 'jquery';
import datesFr from './dates.fr';
import userContext from './userContext';

$(() => {
    if (0 === document.querySelectorAll('.user-notifications-list').length) {
        return;
    }

    if (!userContext.societeUserId) {
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
    const apiGetUserNotifications = () => fetch(`/api/user-notifications/${userContext.societeUserId}`);

    /**
     * Mark the list of notifications defined by their ids in acknowledgeIds as read.
     * The url is the same as the get request because it updates the resource,
     * then the http cache is be cleared.
     *
     * @param {Number[]} acknowledgeIds
     */
    const apiPostUserNotifications = acknowledgeIds => fetch(`/api/user-notifications/${userContext.societeUserId}`, {
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

            $('.rdi-notification-container').empty();

            if (0 === json.notifications.length) {
                $('.rdi-notification-container')
                    .append(`<span class="dropdown-item disabled"><small>Vous n'avez pas encore de notifications.</small></span>`)
                ;

                return;
            }

            json.notifications.forEach(notification => {
                const date = new Date(notification.activity.datetime.date);

                if (null === currentMonth || !sameMonth(date, currentMonth)) {
                    currentMonth = date;

                    $('.rdi-notification-container')
                        .append(`<h6 class="dropdown-header">${datesFr.months[date.getMonth()]}</h6>`)
                    ;
                }

                const $notification = $(`<span class="dropdown-item"><small>${notification.activity.rendered}</small></span>`);

                if (!notification.acknowledged) {
                    acknowledgeIds.push(notification.id)

                    $notification.find('small').wrapInner('<strong>');
                    $notification.find('strong').prepend('<i class="fa fa-circle text-danger" aria-hidden="true"></i> ')
                }

                $('.rdi-notification-container').append($notification);
            });

            if (acknowledgeIds.length > 0) {
                $('.user-notifications-list .dropdown-toggle')
                    .append(`<span class="badge badge-light badge-pill">${acknowledgeIds.length}</span>`)
                ;

                $('.user-notifications-list .fa-bell-o')
                    .removeClass('fa-bell-o')
                    .addClass('fa-bell')
                ;
            }
        })
    ;

    $('.user-notifications-list .dropdown-toggle').on('click', () => {
        if (0 === acknowledgeIds.length) {
            return;
        }

        apiPostUserNotifications(acknowledgeIds)
            .then(() => {
                $('.user-notifications-list .dropdown-toggle .badge').remove();

                $('.user-notifications-list .fa-bell')
                    .removeClass('fa-bell')
                    .addClass('fa-bell-o')
                ;
            })
        ;

        acknowledgeIds = [];
    });
});
