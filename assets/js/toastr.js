import toastr from 'toastr';
import 'toastr/toastr.scss';
window.toastr = toastr;

toastr.options = {
    closeButton: true,
    debug: false,
    newestOnTop: true,
    progressBar: true,
    positionClass: "toast-top-right",
    preventDuplicates: false,
    onclick: null,
    showDuration: "300",
    hideDuration: "1000",
    extendedTimeOut: 0,
    timeOut: 0,
    tapToDismiss: false,
    showEasing: "swing",
    hideEasing: "linear",
    showMethod: "fadeIn",
    hideMethod: "fadeOut"
};

export default toastr;