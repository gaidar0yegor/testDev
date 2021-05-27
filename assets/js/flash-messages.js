const container = document.querySelector('.flash-messages');

const addFlashMessage = (level, message) => {
    container.insertAdjacentHTML('beforeend', `
        <div class="alert alert-${level}" role="alert">
            ${message}
        </div>
    `);
};

const clearFlashMessages = () => {
    container.innerHTML = '';
};

export {
    addFlashMessage,
    clearFlashMessages,
};
