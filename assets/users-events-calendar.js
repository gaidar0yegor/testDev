import './js/dhtmlx-scheduler/users-events';

$(".tabs-user-projets-events").on("click", ".nav-link", function () {
    $(".tabs-user-projets-events").find(".nav-link").removeClass("active");
    $(this).addClass("active");
    $(".tab-target").hide();
    $("#" + $(this).data("target")).show();
});
