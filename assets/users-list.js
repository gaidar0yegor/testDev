import './styles/organigramme-users.css';
import './js/team-organigramme';

import $ from "jquery";

$('.tab-filter-users')
    .on('click', '.organigramme-team', function () {
        $('#users_list_dt_wrapper').slideUp(500);
        $('#users_list_organigramme').slideDown(500);
    });