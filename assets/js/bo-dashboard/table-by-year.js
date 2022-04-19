import $ from "jquery";

const $table = $("#table-sequences");

window.addEventListener("bo-table-by-year", (e) => {
  const yearSelected = e.detail.year;

  if (
    window.historiqueData.hasOwnProperty("users") &&
    window.historiqueData.hasOwnProperty("projets") &&
    window.historiqueData.hasOwnProperty("societes")
  ) {
    $table.find("tbody tr").each((i, tr) => {
      const $tr = $(tr);

      $($tr)
        .find("td")
        .each((i, td) => {
          if (i === 0) {
            return;
          }

          $(td).text(window.historiqueData[$($tr).attr("id")][yearSelected][i]);
        });
    });
  }
});
