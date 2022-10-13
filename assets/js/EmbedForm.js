import $ from 'jquery';

import { initSelect2 } from './select2';
import initTippyTitle from './popper';

const EmbedForm = {};

/**
 * @param {jQuery} $prototypeContainer - The form container with data-prototype, i.e: $("form [data-prototype]")
 * @param {Array} options
 *                  $addButton (optional) custom Add button
 *                  $itemWrapper (optional) custom wrapper to wrap all added items
 *                  newItemAppend (optional) callback that appends new items. By default, append to $prototypeContainer
 *                  initSelect2 (optional) callback initialize the select2
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

    options.$addButton.on('click', function (e) {
        e.preventDefault();
        var $btn = $(this);
        EmbedForm.addPrototypedItem($prototypeContainer, options);
        var $lastRow = $('.fichier-projets-container').find('.row').last();
        if ($lastRow){
            if ($($btn).data('uploadType')){
                $($lastRow).find('.target-upload-type:not([data-target-upload-type="' + $($btn).data('uploadType') + '"])').hide();
            }
            $($lastRow).find('input:visible').first().focus().select();
        }
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

    if (options.initSelect2) {
        if (options.initSelect2.options){
            initSelect2($('select.select-2:not(.select2-hidden-accessible)'),options.initSelect2.options)
        } else{
            initSelect2($('select.select-2:not(.select2-hidden-accessible)'))
        }
    }

    initTippyTitle()
};

export default EmbedForm;
