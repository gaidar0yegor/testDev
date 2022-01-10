import $ from 'jquery';

if ($(window).width() <= 992){
    CKEDITOR.config.removeButtons = 'Cut,Copy,Paste,PasteText,Find,Replace,Subscript,Superscript,SelectAll,SpecialChar,Smiley,ckeditor_wiris_formulaEditor';
}

