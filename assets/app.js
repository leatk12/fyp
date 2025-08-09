import './styles/app.css';

// Import FullCalendar core and required plugins
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';

// TimeGridPlugin import removed because time-basedblock views are no longer used

//Wait for the full DOM to load before executing
document.addEventListener('DOMContentLoaded', () => {
    const calendarEl = document.getElementById('calendar'); // Calendar container element
    const toggleBtn = document.getElementById('toggle-view-btn'); // Button to toggle between month and week views

    let currentView = 'dayGridMonth';  // Default view is the monthly calendar

    // Helper: local YYYY-MM-DD (no UTC conversion)
    const getLocalYmd = (d = new Date()) => {
        const pad = n => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
    };

    if (calendarEl) { // Proceed only if the calendar container exists
        const calendar = new Calendar(calendarEl, {
            plugins: [dayGridPlugin, interactionPlugin],  //Load the necessary plugins
            initialView: currentView, // Set the default view
            initialDate: getLocalYmd(),  //Today's date in YYYY-MM-DD format using local time
            timeZone: 'local',
            headerToolbar: {
                left: 'prev,next today', // Navigation buttons
                center: 'title', //Display current view title
                right: '',  //No buttons on the right side
            },
            views: {
                customWeekGrid: {  //Define a custom week view using dayGrid
                    type: 'dayGrid',
                    duration: { days: 7 },
                    buttonText: 'Week'  //Label for the custom week view
                }
            },
            dateClick: (info) => {  //Handler for clicking a date
                const bookingUrl = `/booking?date=${info.dateStr}`;
                window.location.href = bookingUrl;  //Redirect to the booking page for the selected date
            },
            eventClick: (info) => { // Handler for clicking an event
                // For all-day events, startStr is already YYYY-MM-DD and tz-safe
                const dateStr = info.event.allDay ? info.event.startStr : getLocalYmd(info.event.start);
                const bookingUrl = `/booking?date=${dateStr}`;
                window.location.href = bookingUrl; //Redirect to booking page for the event's date
            },
            events: async (info, successCallback, failureCallback) => {  // Fetch events dynamically
                try {
                    const res = await fetch('/api/bookings');  //Request bookings from server
                    const data = await res.json(); //Parse JSON response
                    const events = data.map(b => ({
                        title: `${b.booked_by}, ${b.room_id}, ${b.time_start}–${b.time_end}`,  //Format event title
                        start: `${b.date}`, // Start date for the event (all-day)
                        allDay: true
                    }));
                    successCallback(events); // Pass events to the calendar
                } catch (err) {
                    failureCallback(err); //Handle fetch failure
                }
            },
            eventContent: function(arg) { //Customised event rendering
                const container = document.createElement('li');  //Render the event inside an <li> element
                container.textContent = arg.event.title;
                container.style.whiteSpace = 'normal';
                container.style.wordWrap = 'break-word';
                container.style.overflowWrap = 'break-word';
                container.style.fontSize = '0.9em';
                container.style.lineHeight = '1.2em';
                return { domNodes: [container] };
            }
        });

        calendar.render();  // Render the calendar page

        // Add click listener to toggle between month and week views (guarded)
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                if (currentView === 'dayGridMonth') {
                    currentView = 'customWeekGrid';
                    toggleBtn.textContent = 'Switch to Month View';
                } else {
                    currentView = 'dayGridMonth';
                    toggleBtn.textContent = 'Switch to Week View';
                }
                calendar.changeView(currentView); //Apply the new view
            });
        }
    }
});
