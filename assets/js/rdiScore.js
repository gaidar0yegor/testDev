import $ from 'jquery';
import rdiKeywords from './rdiKeywords';

const $textSelectors = [
    $('.card .card-body .row:nth-child(1) .col-md-10'),
    $('.card .card-body .row:nth-child(2) .col-md-10'),
    $('.timeline h3'),
    $('.timeline p'),
];

let processed = false;

const highlightWords = () => {
    if (processed) {
        return;
    }

    processed = true;

    $textSelectors.forEach($textSelector => {
        $textSelector.each((i, textContainer) => {
            const $textContainer = $(textContainer);

            const highlightWrap = '$1<span class="rdi-keyword">$2</span>$3';
            let highlightedText = $textContainer.html();

            rdiKeywords.forEach(rdiKeyword => {
                highlightedText = highlightedText.replace(new RegExp(`([^\\w]|^)(${rdiKeyword})([^\\w]|$)`, 'igu'), highlightWrap);
            });

            $textContainer.html(highlightedText);
        });
    });
};

if ($('#rdi-highlight')) {
    $('#rdi-highlight').on('click', e => {
        e.preventDefault();
        highlightWords();

        if (0 === $('.rdi-keyword').length) {
            throw new Error('Impossible to explain RDI score: no RDI keywords in current projet.');
        }

        $('.rdi-keyword')[0].scrollIntoView({
            behavior: 'smooth',
            block: 'center',
        });
    });
}

global.highlightWords = highlightWords;
