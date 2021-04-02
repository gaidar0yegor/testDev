import { FormDataJson, FormDataJsonOptions } from 'form-data-json-convert';
import { addFlashMessage } from './flash-messages';

/**
 * List of forms on which we should keep a draft.
 *
 * @var {HTMLFormElement[]} formsWithDraftSupport
 */
const formsWithDraftSupport = [
    document.projet_form,
    document.fait_marquant,
];

/**
 * Options used to convert form data to json.
 */
const options = new FormDataJsonOptions({
    inputFilter: input => {
        const inputType = input.type.toLowerCase();

        return inputType !== 'hidden' && inputType !== 'password';
    },
});

/**
 * @param {HTMLFormElement} form
 *
 * @returns {string} The string key used to store serialized draft of this form.
 */
const itemKey = form => `form-draft-${form.name}`;

/**
 * @param {HTMLFormElement} form
 *
 * @returns {boolean} Whether there is a draft stored for this form.
 */
const hasDraft = form => {
    return !!localStorage.getItem(itemKey(form));
};

/**
 * Load draft and fill form.
 *
 * @param {HTMLFormElement} form
 */
const loadDraft = form => {
    if (!hasDraft(form)) {
        return;
    }

    FormDataJson.fillFormFromJsonValues(form, JSON.parse(localStorage.getItem(itemKey(form))));
};

/**
 * Removes the draft related to this form.
 *
 * @param {HTMLFontElement} form
 */
const removeDraft = form => {
    localStorage.removeItem(itemKey(form));
};

/**
 * Saves draft.
 *
 * @param {HTMLFormElement} form
 */
const saveDraft = form => {
    const formValues = FormDataJson.formToJson(form, options);

    localStorage.setItem(itemKey(form), JSON.stringify(formValues));
};

/**
 * Auto save draft, display a message if a draft is available on the form.
 *
 * @param {HTMLFormElement} form
 */
const enableDraft = form => {
    if (hasDraft(form)) {
        addFlashMessage('info', 'Vous avez commencez à saisir des données sans les soumettre. <a href="#" class="draft-continue">Continuer la saisie</a>.');

        document.querySelector('.draft-continue').addEventListener('click', e => {
            e.preventDefault();

            loadDraft(form);
            document.querySelector('.draft-continue').closest('.alert').remove();
        });
    }

    const saveDraftCallback = () => {
        saveDraft(form);
    };

    // Save draft before leaving the page, if form has been modified
    form.addEventListener('keypress', () => {
        window.addEventListener('beforeunload', saveDraftCallback);
    }, {once: true});

    // Remove draft on submit
    form.addEventListener('submit', () => {
        removeDraft(form);

        // Dont save draft when leaving page if form has been submitted
        window.removeEventListener('beforeunload', saveDraftCallback);
    });
};

// Enable draft on form if found on the page
formsWithDraftSupport.forEach(form => {
    if (!form) {
        return;
    }

    enableDraft(form);
});
