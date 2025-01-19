import './styles/app.css';

import { Calendar } from '@fullcalendar/core';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import resourceTimeGridPlugin from '@fullcalendar/resource-timegrid';

document.addEventListener('DOMContentLoaded', () => {
    const calendarEl = document.getElementById('calendar');

    const calendar = new Calendar(calendarEl, {
        plugins: [timeGridPlugin, interactionPlugin, resourceTimeGridPlugin],
        initialView: 'resourceTimeGridDay',
        initialDate: new Date().toISOString().slice(0, 10),
        slotMinTime: '08:00:00',
        slotMaxTime: '21:00:00',
        schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'resourceTimeGridWeek'
        },
        resources: '/api/rooms',
        selectable: true,
        select: (info) => {
            openBookingModal(info);
        },
        events: '/api/bookings',
        eventClick: (info) => {
            openEditBookingModal(info.event);
        }
    });

    calendar.render();
});

function openBookingModal(info) {
    const modal = document.getElementById('bookingModal');
    const overlay = document.getElementById('modalOverlay');
    const addCustomerSection = document.getElementById('addCustomerSection');

    modal.style.display = 'block';
    overlay.style.display = 'block';

    document.getElementById('bookingDate').value = info.start.toISOString().slice(0, 10);
    document.getElementById('bookingStart').value = info.start.toISOString().slice(11, 16);
    document.getElementById('bookingEnd').value = info.end.toISOString().slice(11, 16);
    document.getElementById('bookingRoom').value = info.resource?.title || '';

    fetch('/api/customers')
        .then((res) => res.json())
        .then((customers) => {
            const select = document.getElementById('bookingBookedBy');
            select.innerHTML = '';
            customers.forEach((customer) => {
                const option = document.createElement('option');
                option.value = customer.representative;
                option.textContent = customer.representative;
                select.appendChild(option);
            });

            const newCustomerOption = document.createElement('option');
            newCustomerOption.value = 'new';
            newCustomerOption.textContent = 'Add New Customer';
            select.appendChild(newCustomerOption);

            select.addEventListener('change', () => {
                if (select.value === 'new') {
                    addCustomerSection.style.display = 'block';
                } else {
                    addCustomerSection.style.display = 'none';
                }
            });
        });

    document.getElementById('bookingForm').onsubmit = async (e) => {
        e.preventDefault();

        const selectedCustomer = document.getElementById('bookingBookedBy').value;

        let customerId = null;
        if (selectedCustomer === 'new') {
            const customerData = {
                organisation: document.getElementById('customerOrganisation').value,
                representative: document.getElementById('customerRepresentative').value,
                phone: document.getElementById('customerPhone').value,
                email: document.getElementById('customerEmail').value,
            };

            const customerResponse = await fetch('/api/customers', {
                method: 'POST',
                body: JSON.stringify(customerData),
                headers: { 'Content-Type': 'application/json' },
            });

            if (!customerResponse.ok) {
                alert('Failed to add customer.');
                return;
            }

            const customer = await customerResponse.json();
            customerId = customer.id;
        } else {
            customerId = selectedCustomer;
        }

        const bookingData = {
            date: document.getElementById('bookingDate').value,
            time_start: document.getElementById('bookingStart').value,
            time_end: document.getElementById('bookingEnd').value,
            recurring: document.getElementById('bookingRecurring').value,
            recurring_exception: document.getElementById('bookingRecurringException').value || null,
            room: document.getElementById('bookingRoom').value,
            booked_by: selectedCustomer,
            cust_id: customerId,
        };

        fetch('/api/bookings', {
            method: 'POST',
            body: JSON.stringify(bookingData),
            headers: { 'Content-Type': 'application/json' },
        })
            .then((res) => {
                if (!res.ok) {
                    return res.json().then((errorData) => {
                        throw new Error(errorData.error || 'Failed to create booking.');
                    });
                }
                return res.json();
            })
            .then((responseData) => {
                console.log('Booking created successfully:', responseData);
                closeModal();
            })
            .catch((error) => {
                console.error('Error creating booking:', error);
                alert('Failed to create booking. Please try again.');
            });
    };

    document.getElementById('cancelBooking').onclick = closeModal;
    overlay.onclick = closeModal;
}


function submitBooking(bookedBy) {
    const bookingData = {
        date: document.getElementById('bookingDate').value,
        time_start: document.getElementById('bookingStart').value,
        time_end: document.getElementById('bookingEnd').value,
        recurring: document.getElementById('bookingRecurring').value,
        recurring_exception: document.getElementById('bookingRecurringException').value || null,
        room: document.getElementById('bookingRoom').value,
        booked_by: selectedCustomer,
        cust_id: customerId,
    };

// Debugging: Log the data to ensure it's correct
    console.log('Booking Data:', bookingData);

    fetch('/api/bookings', {
        method: 'POST',
        body: JSON.stringify(bookingData),
        headers: { 'Content-Type': 'application/json' },
    })
        .then((res) => {
            if (!res.ok) {
                return res.json().then((errorData) => {
                    console.error('Response text:', text);
                    throw new Error(errorData.error || 'Failed to create booking.');
                });
            }
            return res.json();
        })
        .then((responseData) => {
            console.log('Booking created successfully:', responseData);
            closeModal();
        })
        .catch((error) => {
            console.error('Error creating booking:', error);
            alert('Failed to create booking. Please try again.');
        });

}



function closeModal() {
    const modal = document.getElementById('bookingModal');
    const overlay = document.getElementById('modalOverlay');
    modal.style.display = 'none';
    overlay.style.display = 'none';
}

function openEditBookingModal(event) {
    console.log(`Editing booking for ${event.title}`);
}
