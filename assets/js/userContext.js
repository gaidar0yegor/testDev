export default {
    userId: window['user-context-ids'] ? window['user-context-ids'].dataset.userId : null,
    societeUserId: window['user-context-ids'] ? window['user-context-ids'].dataset.societeUserId : null,
    userBookId: window['user-context-ids'] ? window['user-context-ids'].dataset.userBookId : null,
};
