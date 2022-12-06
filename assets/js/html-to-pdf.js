import $ from "jquery";
import html2pdf from "html2pdf.js/src";

$(document).on("click", "#btnHtmlToPdf", function (e) {
    e.preventDefault();
    var element = document.getElementById($(this).data("contentId"));
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

    var place = $(elem).find("#efficacite-moyenne");
    if ($(place)) {
        var canvas = document.getElementById("efficacite-moyenne");
        var img = document.createElement("img");
        $(img).width($(canvas).width());
        $(img).height($(canvas).height());
        $(img).attr("src", canvas.toDataURL());
        $(img).addClass("svg-to-img");
        $(place).parent().html($(img));
    }

    $(elem)
        .find("img.svg-to-img")
        .each((i, img) => {
            $(img).width("100%");
            $(img).height("100%");
        });

    var opt = {
        margin: [0.4, 0.4],
        enableLinks: false,
        image: {
            type: "jpeg",
            quality: 1
        },
        html2canvas: {
            scale: 4,
            dpi: 300,
            letterRendering: true
        },
        jsPDF: {
            unit: "in",
            format: "A4",
            orientation: "L"
        },
        pagebreak: {before: '.newPage'},
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
                month: "long", 
                year: "numeric"
            }
            var sDay = today.toLocaleDateString("fr-FR", options);


            const pageCount = pdf.internal.getNumberOfPages();
            for(let i = 1; i <= pageCount; i++) {
                pdf.setPage(i);
                const pageSize = pdf.internal.pageSize;
                const pageWidth = pageSize.width ? pageSize.width : pageSize.getWidth();
                const pageHeight = pageSize.height ? pageSize.height : pageSize.getHeight();
                const headerLeft = 'Report 2014';
                const footerLeft = `Strictement confidentiel`;
                const headerRight = 'Report 2014';
                const footerRight = `${sDay} | Powered by RDI`;

                pdf.setFontSize(8);
                pdf.setTextColor(150);
                // Header Left
                pdf.text(headerLeft, 0.2, 0.2, { baseline: 'top' });
                // Footer Left 
                pdf.text(footerLeft, 0.2, pageHeight - 0.2, { baseline: 'bottom' });
                // Header Right
                pdf.text(headerRight, pageWidth - 0.8, 0.2, { baseline: 'top' });
                // Footer Right
                pdf.text(footerRight, pageWidth - 2, pageHeight - 0.2, { baseline: 'bottom' });
            }


            pdf.save(document.title + ".pdf");
        });
});