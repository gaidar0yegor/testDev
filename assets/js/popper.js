import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';

document.addEventListener('DOMContentLoaded', () => {
    // Wrap disabled elements to make sure tooltip appears
    document.querySelectorAll('[title]').forEach(element => {
        const wrapper = document.createElement('span');

        element.parentNode.insertBefore(wrapper, element);
        wrapper.appendChild(element);
        wrapper.setAttribute('title', element.getAttribute('title'));
        element.removeAttribute('title');
    });

    tippy('[title]', {
        content: element => element.getAttribute('title'),
        onCreate(instance) {
            instance.reference.removeAttribute('title');
        },
    });
});
