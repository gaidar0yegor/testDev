window.historiqueData = {};

import "./js/bo-dashboard/chartUser";
import "./js/bo-dashboard/chartProjet";
import "./js/bo-dashboard/chartSociete";
import "./js/bo-dashboard/table-by-year";

$(".tabs-user-chart").on("click", ".nav-link", function () {
  $(".tabs-user-chart").find(".nav-link").removeClass("active");
  $(this).addClass("active");
  $(".tab-target").hide();
  $("#" + $(this).data("target")).show();
});
