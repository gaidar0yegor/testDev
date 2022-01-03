import 'select2/dist/css/select2.min.css'
import 'select2/dist/js/select2.full.min'

import $ from "jquery";
import {validateEmail} from "./utils";

const initSelect2 = ($element = null, options = {}) => {
    var $elem = $element ? $element : $('select.select-2');
    $($elem).select2(options);
};

const initFmSendedToSelect2 = () => {
    if ($('#fait_marquant_sendedToSocieteUsers.select-2.select2-with-add')) {
        var $elem = $('#fait_marquant_sendedToSocieteUsers.select-2.select2-with-add');

        $($elem).select2({
            placeholder: function () {
                $(this).data('placeholder');
            },
            tags: true,
            createTag: function (params) {
                return {
                    id: '@' + params.term,
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
        }).on('select2:select', function (e) {
            var data = e.params.data,
                vars = [];
            if (data.newOption){
                if (validateEmail(data.text)){
                    if ($($elem).find("option[value='" + data.text + "']").length){
                        vars = $($elem).val();
                        $($elem).find("option[value='" + data.id + "']").remove();
                        vars.indexOf(data.text) === -1 ? vars.push(data.text) : false;
                        $($elem).val(vars);
                    }
                } else {
                    $($elem).find("option[value='" + data.id + "']").remove();
                }
                $($elem).trigger('change');
            }
        });
    }
};

$(document).ready(function () {
    initSelect2();
    initFmSendedToSelect2();
});

export {
    initSelect2
}


