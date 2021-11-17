import $ from 'jquery';

$(document).on('change', '.upload-img', function (e) {
    var input = $(this)[0];
    var preview_container = $('.img-uploaded-container .img-thumbnail');

    if (input.files && input.files[0]) {

        var reader = new FileReader();

        reader.onload = function (e) {
            $(preview_container).attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);
    }
});