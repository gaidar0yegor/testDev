(function ($) {
    'use strict';

    $('input.custom-file-input').on('change', function () {
        const $input = $(this);
        const path = $input.val();
        const filename = path.split(/[\/\\]+/).pop();

        $input
            .next('.custom-file-label')
            .html(filename)
        ;
    })
})(jQuery);
