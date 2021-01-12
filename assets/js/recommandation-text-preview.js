import $ from 'jquery';

const updateText = ($container, text) => {
    if (!text) {
        $container.hide();
        return;
    }

    $container
        .show()
        .css('white-space', 'pre-wrap')
        .text(text.trim())
    ;
};

const textPreview = ($textInput, $container) => {
    $textInput.keyup(e => updateText($container, e.target.value));

    updateText($container, $textInput.val());
};

$(() => {
    if (!window['recommandation_message_customText']) {
        return;
    }

    textPreview(
        $('#recommandation_message_customText'),
        $('.custom-text-preview'),
    );
});
