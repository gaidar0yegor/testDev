import $ from 'jquery';
import { addToastrFlashMessage } from './flash-messages';

$(document).on('click', '.btn-patchnote-readed', function (e) {
    fetch('/api/patchnote/readed', { method: 'post' })
        .then(response => response.json())
        .then(response => {
            if ("readed" in response && response.readed === true){
                $('.patchnote-modal').remove();
            } else if ("message" in response) {
                addToastrFlashMessage('error', response.message);
            }
        });
});

