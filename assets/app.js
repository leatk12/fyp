import './styles/app.css';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';

document.addEventListener('DOMContentLoaded', () => {
    const calendarEl = document.getElementById('calendar');

    if (calendarEl) {
        // Initialize FullCalendar only if #calendar exists
        const calendar = new Calendar(calendarEl, {
            plugins: [dayGridPlugin, interactionPlugin],
            initialView: 'dayGridMonth', // Month view
            initialDate: new Date().toISOString().slice(0, 10),
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth',
            },
            dateClick: (info) => {
                // Redirect to the booking page for the selected date
                const bookingUrl = `/booking?date=${info.dateStr}`;
                window.location.href = bookingUrl;
            },
        });

        // Render the calendar
        calendar.render();
    }
});




