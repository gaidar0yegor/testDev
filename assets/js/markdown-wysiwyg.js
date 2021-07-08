import '@toast-ui/editor/dist/toastui-editor.css';

import Editor from '@toast-ui/editor';
import '@toast-ui/editor/dist/i18n/fr-fr';
import { detectedLocale as language } from './translation';

let recursion = false;

const callWithoutRecursion = callback => {
    if (recursion) {
        return;
    }

    recursion = true;
    callback();
    recursion = false;
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.markdown-wysiwyg').forEach(el => {
        const editor = new Editor({
            el,
            language,
            height: '500px',
            initialEditType: 'markdown',
            usageStatistics: false,
            hideModeSwitch: true,
            toolbarItems: [
                ['heading', 'bold', 'italic', 'strike'],
                ['link', 'ul', 'ol'],
                ['codeblock'],
            ],
        });

        const input = window[el.dataset.inputId];

        input.addEventListener('change', () => {
            callWithoutRecursion(() => editor.setMarkdown(input.value));
        });

        editor.setMarkdown(input.value);

        editor.on('change', () => {
            callWithoutRecursion(() => {
                input.value = editor.getMarkdown();
                input.dispatchEvent(new CustomEvent('change'));
            });
        });
    });

    document
        .querySelectorAll('.markdown-wysiwyg-textarea')
        .forEach(element => element.style.display = 'none')
    ;
});
