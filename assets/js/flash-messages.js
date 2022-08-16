import toastr from './toastr';
import $ from "jquery";

const container = document.querySelector('.flash-messages');

const addToastrFlashMessage = (level, message) => {
    toastr[level](message);
};

const addHtmlFlashMessage = (level, message) => {
     container.insertAdjacentHTML('beforeend', `
        <div class="alert alert-${level}" role="alert">
            ${message}
            <i class='fa fa-times alert-close'></i>
        </div>
    `);
};

const clearHtmlFlashMessages = () => {
    container.innerHTML = '';
};

$(document).on('click', '.flash-messages .alert-close', function (e) {
    $(this).parent().remove();
});

export {
    addHtmlFlashMessage,
    addToastrFlashMessage,
    clearHtmlFlashMessages,
};
