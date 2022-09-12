import 'select2/dist/css/select2.min.css'
import 'select2/dist/js/select2.full.min'

import $ from "jquery";

const initSelect2 = ($element = null, options = {}) => {
    var $elem = $element ? $element : $('select.select-2');
    options.width = '100%';
    $($elem).select2(options);
};

$(document).ready(function () {
    initSelect2();
});

export {
    initSelect2
}


