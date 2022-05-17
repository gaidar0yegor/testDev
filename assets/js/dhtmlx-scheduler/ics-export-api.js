/**
 * Send api to generate a ICS file
 *
 * @param {number} projectId
 * @param {number} eventId
 */
const apiGenerateIcsCalendar = async (projectId, eventId) => {
    return fetch(`/api/projet/${projectId}/events/ics_calendar/${eventId}`, {method: 'GET'})
        .then(() => {
            window.location = `/api/projet/${projectId}/events/ics_calendar/${eventId}`;
        });
};

export default apiGenerateIcsCalendar;