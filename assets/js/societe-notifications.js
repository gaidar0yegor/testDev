const apiGetUserNotifications = (id) => fetch(`/corp/api/user-notifications/${id}`);

const cards = document.querySelectorAll('.card[data-societe-user-id]');

cards.forEach(card => {

    const societeUserId = card.dataset.societeUserId;

    apiGetUserNotifications(societeUserId)
        .then(response => response.json())
        .then(json => {

            const societeNotif = json.notifications.filter(notif => !notif.acknowledged).length;

            if (societeNotif > 0) {

                card.querySelector('.notif-container').innerHTML += `<i class="fa fa-lg fa-bell" aria-hidden="true"></i><span class="badge badge-danger badge-pill">` + societeNotif + `</span>`;

            }
        });
});