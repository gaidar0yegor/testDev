import $ from 'jquery';
import html2pdf from "html2pdf.js/src";

$(document).on('click', '#btnHtmlToPdf', function (e) {
    e.preventDefault();
    var element = document.getElementById($(this).data('contentId'));
    var elem = element.cloneNode(true);
    $(elem).find('select').each((i, select) => {
        $($(select).parent()).html($(select).find('option[selected]').text());
    });
    $(elem).find('.not-printable').remove();

    var opt = {
        margin: [0.2, 0],
        enableLinks:    false,
        image:  { type: 'jpeg', quality: 1 },
        html2canvas:    { scale: 4, dpi: 300, letterRendering: true },
        jsPDF:  { unit: 'in', format: 'A3', orientation: 'l' }
    };

    html2pdf().set(opt).from(elem).toPdf().get('pdf').then(function (pdf) {
        pdf.save(document.title + ".pdf");
    });
});