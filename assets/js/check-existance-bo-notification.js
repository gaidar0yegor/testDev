import $ from "jquery";

if ($('.back-office-link .notif-badge').length > 0){
    fetch(`/back-office/api/user-notifications`)
        .then((response) => response.json())
        .then((response) => {
            if (response.hasNotifs){
                $('.back-office-link .notif-badge').show();
            }
        });
}
