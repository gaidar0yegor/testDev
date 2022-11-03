import $ from "jquery";
import { detectedLocale } from "./../js/translation";

if (typeof CKEDITOR !== "undefined") {
    if ($(window).width() <= 992) {
        CKEDITOR.config.removeButtons =
            "Cut,Copy,Paste,PasteText,Find,Replace,Subscript,Superscript,SelectAll,SpecialChar,Smiley,ckeditor_wiris_formulaEditor";
    }

    CKEDITOR.config.scayt_autoStartup = true;
    CKEDITOR.config.scayt_sLang = detectedLocale + "_" + detectedLocale.toUpperCase();
    CKEDITOR.timestamp = (Math.random() + 1).toString(36).substring(7);

    $('.container.main-container').append(`<div class="ckeditor-preview-popup rdi-popup bg-modal" style="display: none;">
        <div class="content">
            <div class="rdi-popup-body pb-3 event-content text-justify text-word-break position-relative" style="max-height: 70vh !important;"></div>
            <div class="rdi-popup-footer">
                <button class="btn btn-success rdi-popup-close">Fermer</button>
            </div>
        </div>
    </div>`);
}

$(document).on('click', '.ckeditor-preview', function (e) {
    var content = CKEDITOR.instances[$(this).data('ckeditorInstance')].getData();
    let popup = $('.ckeditor-preview-popup');
    $(popup).find('.rdi-popup-body').html(content);
    $(popup).show();
});

$(document).on('click', '.ckeditor-preview-popup .rdi-popup-close', function (e) {
    $('.ckeditor-preview-popup').hide();
});