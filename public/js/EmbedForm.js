'use strict';

(function (_export, $) {

    const EmbedForm = {};

    /**
     * @param {jQuery} $prototypeContainer - The form container with data-prototype
     * @param {Array} options
     *                  $addButton (optional) custom Add button
     *                  $itemWrapper (optional) custom wrapper to wrap all added items
     */
    EmbedForm.init = function ($prototypeContainer, options = {}) {
        $prototypeContainer.data('index', $prototypeContainer.find(':input').length);

        if (!options.$addButton) {
            options.$addButton = $('<button type="button" class="btn btn-primary">Add</button>');
        }

        options.$addButton.on('click', function (e) {
            EmbedForm.addPrototypedItem($prototypeContainer, options);
        });

        $prototypeContainer.after(options.$addButton);
        options.$addButton.show();
    };

    EmbedForm.addPrototypedItem = function ($prototypeContainer, options) {
        var prototype = $prototypeContainer.data('prototype');
        var index = $prototypeContainer.data('index');

        var newForm = prototype.replace(/__name__/g, index);
        $prototypeContainer.data('index', index + 1);

        if (options.$itemWrapper) {
            $prototypeContainer.append(options.$itemWrapper.clone().append(newForm));
        } else {
            $prototypeContainer.append(newForm);
        }
    };

    _export.EmbedForm = EmbedForm;
})(window, jQuery);
