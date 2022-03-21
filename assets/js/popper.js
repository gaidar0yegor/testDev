import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';

import $ from 'jquery';

const initTippyTitle = () => {
    tippy('[title]:not([title=""]):not(.cke [title])', {
        allowHTML: true,
        content: element => element.getAttribute('title'),
        onCreate(instance) {
            instance.reference.removeAttribute('title');
        },
    });
};

document.addEventListener('DOMContentLoaded', () => {
    // Wrap disabled elements to make sure tooltip appears
    document.querySelectorAll('[title]').forEach(element => {
        const wrapper = document.createElement('span');

        element.parentNode.insertBefore(wrapper, element);
        wrapper.appendChild(element);
        wrapper.setAttribute('title', element.getAttribute('title'));
        element.removeAttribute('title');
    });

    initTippyTitle();

    $('[miniature]').mouseover(function(elem) {
        let img_url = $(this).attr('href');
        let img_fileName = $(this).text();
        tippy(this, {
            content: element => element.getAttribute('title'),
            flipOnUpdate: true,
            onShow(instance) {
                // Create an image
                const image = new Image();
                image.style.display = 'block';
                image.style.maxWidth  = '300px';
                image.style.maxHeight  = '200px';
                image.src = img_url;
                image.title = img_fileName;
                // Update the tippy content with the image
                instance.setContent(image);
            }
        });
    });

});

export default initTippyTitle;