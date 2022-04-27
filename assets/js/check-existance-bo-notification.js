import $ from "jquery";

fetch(`/back-office/api/user-notifications`)
    .then((response) => response.json())
    .then((response) => {
        if (response.hasNotifs){
            $('.back-office-link .notif-badge').show();
        }
    });