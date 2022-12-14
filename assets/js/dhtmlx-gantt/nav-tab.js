import $ from "jquery";

$('.tab-filter-planning')
    .on('click', '.nav-link', function () {
        $('.tab-filter-planning').find('.nav-link').removeClass('active');
        $(this).addClass('active');
    })
    .on('click', '.planning-stats', function () {
        $('#planning-stats').removeClass('d-none');
        $('#planning-graph').addClass('d-none');
    })
    .on('click', '.planning-graph', function () {
        $('#planning-stats').addClass('d-none');
        $('#planning-graph').removeClass('d-none');
    })