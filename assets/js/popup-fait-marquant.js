import '../styles/popup-fait-marquant.scss';
import $ from 'jquery';
import { initSelect2 } from './select2';

let container = $('#rdi-popup-fait-marquant-container');
let form = $(container).find('form');
let mainWidget = $(container).find('.widget[data-widget="widget-main"]');
let btnReturn = $(container).find('.btn-return');
let btnWidgets = $(container).find('.btn-widgets button');

$(document).ready(function () {
    $(document)
        .on('click', '.btn-add-fm-popup', function () {
            $(container).show();
        });
    $(container)
        .on('click', '.rdi-popup-fait-marquant .col_form_close .close', function () {
            $(container).hide();
            $(btnReturn).trigger("click");
            $(form).trigger("reset");
        })
        .on('click', 'button[data-widget-target]', function () {
            let widget = $(`.widget[data-widget="${$(this).data('widgetTarget')}"]`);

            $(btnWidgets).removeClass('active');
            $(this).addClass('active');

            $('.widget').hide();
            $(widget).show();

            $(btnReturn).show();

            initSelect2( $(widget).find('select'), { tags: true });
        })
        .on('click', '.btn-return', function () {
            $('.widget').hide();
            $(btnWidgets).removeClass('active');
            $(mainWidget).show();
            $(btnReturn).hide();
        })
    ;

    // afficher popup si error
    let errorsContainer = $(document).find('.rdi-popup-fait-marquant .invalid-feedback');
    if (errorsContainer){
        let dataWidget = $($(errorsContainer)[0]).parents('.widget').data('widget');
        $(document).find(`.rdi-popup-fait-marquant .rdi-popup-footer button[data-widget-target="${dataWidget}"]`).trigger('click');
    }
});

