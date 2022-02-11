import $ from 'jquery';

if (typeof CKEDITOR !== 'undefined'){
    if ($(window).width() <= 992){
        CKEDITOR.config.removeButtons = 'Cut,Copy,Paste,PasteText,Find,Replace,Subscript,Superscript,SelectAll,SpecialChar,Smiley,ckeditor_wiris_formulaEditor';
    }

    CKEDITOR.config.scayt_autoStartup = true;
}


