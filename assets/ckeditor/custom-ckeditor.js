import $ from "jquery";
import { detectedLocale } from "./../js/translation";

if (typeof CKEDITOR !== "undefined") {
    if ($(window).width() <= 992) {
        CKEDITOR.config.removeButtons =
            "Cut,Copy,Paste,PasteText,Find,Replace,Subscript,Superscript,SelectAll,SpecialChar,Smiley,ckeditor_wiris_formulaEditor";
    }

    CKEDITOR.config.scayt_autoStartup = true;
    CKEDITOR.config.scayt_sLang = detectedLocale + "_" + detectedLocale.toUpperCase();
}