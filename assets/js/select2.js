import 'select2/dist/css/select2.min.css'
import 'select2/dist/js/select2.full.min'

import $ from "jquery";
import {validateEmail} from "./utils";

const initSelect2 = ($element = null, options = {}) => {
    var $elem = $element ? $element : $('select.select-2');
    $($elem).select2(options);
};

const initFmSendedToSelect2 = () => {
    if ($('#fait_marquant_sendedToEmails.select-2.select2-with-add')) {
        var $elem = $('#fait_marquant_sendedToEmails.select-2.select2-with-add');

        $($elem).select2({
            placeholder: function () {
                $(this).data('placeholder');
            },
            tags: true,
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },
            templateResult: function (data) {
                var $result = $("<span></span>");
                $result.text(data.text);
                if (data.newOption) {
                    $result.append(" <em>Nouveau destinataire ...</em>");
                }
                return $result;
            }
        })
    }
};

$(document).ready(function () {
    initSelect2();
    initFmSendedToSelect2();
});

export {
    initSelect2
}


