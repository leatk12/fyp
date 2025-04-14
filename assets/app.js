import './styles/app.css';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';

// Removed timeGridPlugin because we're no longer using time-based blocks

document.addEventListener('DOMContentLoaded', () => {
    const calendarEl = document.getElementById('calendar');
    const toggleBtn = document.getElementById('toggle-view-btn');

    let currentView = 'dayGridMonth';

    if (calendarEl) {
        const calendar = new Calendar(calendarEl, {
            plugins: [dayGridPlugin, interactionPlugin],
            initialView: currentView,
            initialDate: new Date().toISOString().slice(0, 10),
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: '',
            },
            views: {
                customWeekGrid: {
                    type: 'dayGrid',
                    duration: { days: 7 },
                    buttonText: 'Week'
                }
            },
            dateClick: (info) => {
                const bookingUrl = `/booking?date=${info.dateStr}`;
                window.location.href = bookingUrl;
            },
            eventClick: (info) => {
                const event = info.event;
                const start = event.start;
                const dateStr = start.toISOString().split('T')[0];
                const bookingUrl = `/booking?date=${dateStr}`;
                window.location.href = bookingUrl;
            },
            events: async (info, successCallback, failureCallback) => {
                try {
                    const res = await fetch('/api/bookings');
                    const data = await res.json();
                    const events = data.map(b => ({
                        title: `${b.booked_by}, ${b.room_id}, ${b.time_start}–${b.time_end}`,
                        start: `${b.date}` // Just date for all-day bullet-style event
                    }));
                    successCallback(events);
                } catch (err) {
                    failureCallback(err);
                }
            },
            eventContent: function(arg) {
                const container = document.createElement('li');
                container.textContent = arg.event.title;
                container.style.whiteSpace = 'normal';
                container.style.wordWrap = 'break-word';
                container.style.overflowWrap = 'break-word';
                container.style.fontSize = '0.9em';
                container.style.lineHeight = '1.2em';
                return { domNodes: [container] };
            }
        });

        calendar.render();

        toggleBtn.addEventListener('click', () => {
            if (currentView === 'dayGridMonth') {
                currentView = 'customWeekGrid';
                toggleBtn.textContent = 'Switch to Month View';
            } else {
                currentView = 'dayGridMonth';
                toggleBtn.textContent = 'Switch to Week View';
            }
            calendar.changeView(currentView);
        });
    }
});