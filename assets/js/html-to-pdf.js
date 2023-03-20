import $ from "jquery";
import userContext from "./userContext";
import html2pdf from "html2pdf.js/src";
import { el } from "date-fns/locale";

fetch(
  `/corp/api/dashboard/mon-tableau-de-bord-api/${userContext.societeUserId}`
)
  .then((response) => response.json())
  .then((initData) => {
    $(document).on("click", "#btnHtmlToPdf", function (e) {

      $(".exportTitle").addClass('text-danger');
      var element = document.getElementById($("#btnHtmlToPdf").data("contentId"));
      var elem = element.cloneNode(true);
      $(elem)
        .find("select")
        .each((i, select) => {
          $($(select).parent()).html($(select).find("option[selected]").text());
        });
      $(elem).find(".not-printable").remove();

      $(elem)
        .find("svg")
        .each((i, mySVG) => {
          var tgtImage = $(mySVG).parent(),
            can = document.createElement("canvas"),
            ctx = can.getContext("2d"),
            loader = new Image();

          loader.width = can.width = $(mySVG).width();
          loader.height = can.height = $(mySVG).height();
          loader.onload = function () {
            ctx.drawImage(loader, 0, 0, loader.width, loader.height);
            tgtImage.src = can.toDataURL();
          };
          var svgAsXML = new XMLSerializer().serializeToString(mySVG);
          loader.src = "data:image/svg+xml," + encodeURIComponent(svgAsXML);
          $(loader).addClass("svg-to-img");
          $(mySVG).parent().html(loader);
        });

      var place = $(elem).find("canvas");
      if ($(place)) {
        var canvasElements = document.querySelectorAll(".canvas");
        var images = [];
        for (var i = 0; i < canvasElements.length; i++) {
          var canvas = canvasElements[i];
          var img = new Image();
          $(img).width($(canvas).width());
          $(img).height($(canvas).height());
          if(canvas) {
            img.src = canvas.toDataURL();
          }
          images.push(img);
          $(img).addClass("svg-to-img");
        }
        var canvasParents = place.parent();
          for (var i = 0; i < canvasParents.length; i++) {
            var parents = canvasParents[i];
            $(parents).html($(images[i]));
          }
      }

      $(elem)
        .find("img.svg-to-img")
        .each((i, img) => {
          $(img).width("100%");
          $(img).height("100%");
        });
        
      var opt = {
        margin: [0.5, 0.5],
        enableLinks: false,
        autoPaging: 'text',
        pagebreak: { after: ".newPage", before: ".oldPage" },
        image: {
          type: "jpeg",
          quality: 1,
        },
        html2canvas: {
          scale: 2,
          dpi: 300,
          letterRendering: false,
          windowWidth: 1920
        },
        jsPDF: {
          unit: "in",
          format: "A4",
          orientation: "L",
        },
      };

      html2pdf()
        .set(opt)
        .from(elem)
        .toPdf()
        .get("pdf")
        .then(function (pdf) {
          var today = new Date();
          var options = {
            day: "numeric",
            month: "numeric",
            year: "numeric",
          };
          var sDay = today.toLocaleDateString("fr-FR", options);

          const pageCount = pdf.internal.getNumberOfPages();
          for (let i = 1; i <= pageCount; i++) {
            pdf.setPage(i);
            const pageSize = pdf.internal.pageSize;
            const pageWidth = pageSize.width ? pageSize.width : pageSize.getWidth();
            const pageHeight = pageSize.height
              ? pageSize.height
              : pageSize.getHeight();
            const headerLeft = initData.societe_name;
            const footerLeft = `Strictement confidentiel`;
            const headerRight = initData.societe_logo;
            const footerRight = `${sDay} | Powered by RDI`;

            pdf.setFontSize(8);
            pdf.setTextColor(150);
            pdf.text(headerLeft, 0.2, 0.2, { baseline: "top" });
            pdf.text(footerLeft, 0.2, pageHeight - 0.2, { baseline: "bottom" });
            pdf.addImage(headerRight, "PNG", pageWidth - 1.2, 0.1, 0.8, 0.3, {
              baseline: "top",
            });
            pdf.text(footerRight, pageWidth - 2, pageHeight - 0.2, {
              baseline: "bottom",
            });
          }
            pdf.save(document.title + ".pdf");
        });
        $(".exportTitle").removeClass('text-danger');
      });
    })
    .catch((err) => console.error(err));
