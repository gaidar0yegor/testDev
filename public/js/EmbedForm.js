'use strict';

(function (_export, $) {

    const EmbedForm = {};

    /**
     * @param {jQuery} $prototypeContainer - The form container with data-prototype, i.e: $("form [data-prototype]")
     * @param {Array} options
     *                  $addButton (optional) custom Add button
     *                  $itemWrapper (optional) custom wrapper to wrap all added items
     *                  newItemAppend (optional) callback that appends new items. By default, append to $prototypeContainer
     */
    EmbedForm.init = function ($prototypeContainer, options = {}) {
        if ($prototypeContainer.data('embedformprocessed')) {
            return;
        }

        $prototypeContainer.data('index', $prototypeContainer.find(':input').length);

        if (!options.$addButton) {
            options.$addButton = $('<button type="button" class="btn btn-primary">Add</button>');
            $prototypeContainer.after(options.$addButton);
        }

        options.$addButton.on('click', function () {
            EmbedForm.addPrototypedItem($prototypeContainer, options);
        });

        options.$addButton.show();

        $prototypeContainer.data('embedformprocessed', true);
    };

    EmbedForm.addPrototypedItem = function ($prototypeContainer, options) {
        const prototype = $prototypeContainer.data('prototype');
        const index = (Math.random()+'').substr(2);

        const newForm = prototype
            .replace(/__name__label__/g, 'Label')
            .replace(/__name__/g, index)
        ;

        let $newItem = newForm;

        if (options.$itemWrapper) {
            $newItem = options.$itemWrapper.clone().append($newItem);
        }

        if (options.newItemAppend) {
            options.newItemAppend($newItem);
        } else {
            $prototypeContainer.append($newItem);
        }
    };

    _export.EmbedForm = EmbedForm;
})(window, jQuery);
