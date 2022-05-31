/**
 * Send api to generate a ICS file
 *
 * @param {number} eventId
 */
const apiGenerateIcsCalendar = async (eventId) => {
    return fetch(`/api/utilisateur/evenement/ics_calendar/${eventId}`, {method: 'GET'})
        .then(() => {
            window.location = `/api/utilisateur/evenement/ics_calendar/${eventId}`;
        });
};

export default apiGenerateIcsCalendar;