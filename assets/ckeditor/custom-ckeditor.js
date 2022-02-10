import $ from 'jquery';

if ($(window).width() <= 992 && typeof CKEDITOR !== 'undefined'){
    CKEDITOR.config.removeButtons = 'Cut,Copy,Paste,PasteText,Find,Replace,Subscript,Superscript,SelectAll,SpecialChar,Smiley,ckeditor_wiris_formulaEditor';
}

CKEDITOR.config.scayt_autoStartup = true;

