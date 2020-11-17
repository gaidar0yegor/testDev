(function ($) {
    'use strict';

    // Wrap disabled elements to make sure tooltip appears
    $('[title].disabled').wrap(function () {
        return $('<span>').prop('title', $(this).prop('title'));
    });

    $('[title]').tooltip({
        placement: 'auto',
        trigger: 'hover focus',
    });

})(jQuery);
