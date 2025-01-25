document.addEventListener('DOMContentLoaded', () => {
    // Elements
    const bookingContainer = document.getElementById('booking-grid');
    const bookingModal = document.getElementById('bookingModal');
    const modalOverlay = document.getElementById('modalOverlay');
    const bookingForm = document.getElementById('bookingForm');
    const cancelBookingButton = document.getElementById('cancelBooking');
    const addNewCustomerButton = document.getElementById('addNewCustomer');
    const newCustomerFields = document.getElementById('newCustomerFields');
    const existingCustomerDropdown = document.getElementById('existingCustomer');
    const recurringDropdown = document.getElementById('recurring');

    let isAddingNewCustomer = false; // Track if a new customer is being added

    // Helper to check for element existence
    const checkElement = (element, elementName) => {
        if (!element) {
            console.error(`${elementName} not found in the DOM.`);
            console.log("Current DOM structure:", document.body.innerHTML);
            return false;
        }
        return true;
    };

    // Load bookings and update the grid
    const loadBookings = async () => {
        try {
            const response = await fetch('/api/bookings');
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

            const bookings = await response.json();

            bookings.forEach(booking => {
                const startTime = parseInt(booking.time_start.split(':')[0]);
                const endTime = parseInt(booking.time_end.split(':')[0]);
                const roomName = booking.room_id; // Now using roomName

                console.log(`Processing booking: ${JSON.stringify(booking)}`);

                // Get all affected time slots for the booking
                const slots = [];
                for (let time = startTime; time < endTime; time++) {
                    const slot = document.querySelector(
                        `.time-slot[data-room-id="${roomName}"][data-time="${time}"]`
                    );
                    if (slot) slots.push(slot);
                }

                // Merge the cells and style them
                if (slots.length > 0) {
                    const firstSlot = slots[0];
                    const totalRows = slots.length;

                    // Merge cells by setting `gridRow` span
                    firstSlot.style.gridRow = `span ${totalRows}`;
                    firstSlot.classList.add('booked');
                    firstSlot.textContent = booking.booked_by; // Display `booked_by` in the cell
                    firstSlot.style.backgroundColor = 'blue';
                    firstSlot.style.color = 'white';
                    firstSlot.style.textAlign = 'center';

                    // Hide the remaining slots for the booking duration
                    slots.slice(1).forEach(slot => (slot.style.display = 'none'));
                } else {
                    console.warn(`No slots found for booking: ${JSON.stringify(booking)}`);
                }
            });
        } catch (error) {
            console.error('Error loading bookings:', error);
        }
    };

    // Load customers into the dropdown
    const loadCustomers = async () => {
        try {
            const response = await fetch('/api/customers');
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

            const customers = await response.json();
            customers.forEach(customer => {
                const option = document.createElement('option');
                option.value = customer.representative;
                option.textContent = `${customer.representative} (${customer.organisation || 'No Organisation'})`;
                existingCustomerDropdown.appendChild(option);
            });
        } catch (error) {
            console.error('Error loading customers:', error);
        }
    };

    // Load recurrence options into the dropdown
    const loadRecurrences = async () => {
        try {
            const response = await fetch('/api/recurrences');
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

            const recurrences = await response.json();
            recurrences.forEach(recurrence => {
                const option = document.createElement('option');
                option.value = recurrence.recurring;
                option.textContent = recurrence.recurring;
                recurringDropdown.appendChild(option);
            });
        } catch (error) {
            console.error('Error loading recurrences:', error);
        }
    };

    // Initialize data
    loadBookings();
    loadCustomers();
    loadRecurrences();

    // Toggle new customer fields
    if (checkElement(addNewCustomerButton, "Add New Customer button")) {
        addNewCustomerButton.addEventListener('click', () => {
            isAddingNewCustomer = !isAddingNewCustomer;
            newCustomerFields.style.display = isAddingNewCustomer ? 'block' : 'none';
            if (!isAddingNewCustomer) existingCustomerDropdown.value = ''; // Clear existing customer selection
        });
    }

    // Reset modal fields
    const resetModalFields = () => {
        bookingForm.reset();
        existingCustomerDropdown.value = '';
        recurringDropdown.value = '';
        newCustomerFields.style.display = 'none';
        isAddingNewCustomer = false;
    };

    // Close modal
    const closeModal = () => {
        bookingModal.style.display = 'none';
        modalOverlay.style.display = 'none';
        resetModalFields();
    };

    // Open modal for booking
    const openModal = (roomId, startTime, endTime) => {
        const roomNameElement = document.querySelector(`.time-slot[data-room-id="${roomId}"]`);
        if (!roomNameElement) {
            console.error("Room element not found for ID:", roomId);
            return;
        }

        const roomName = roomNameElement.dataset.roomName;

        document.getElementById('bookingRoom').value = roomName;
        document.getElementById('bookingStart').value = startTime.toString().padStart(2, '0') + ':00';
        document.getElementById('bookingEnd').value = endTime.toString().padStart(2, '0') + ':00';
        document.getElementById('bookingDate').value = new Date().toISOString().split('T')[0];

        bookingModal.style.display = 'block';
        modalOverlay.style.display = 'block';
    };

    // Event listener for opening the modal with grid dragging
    let isDragging = false;
    let dragStartTime = null;
    let dragEndTime = null;

    if (checkElement(bookingContainer, "Booking container")) {
        bookingContainer.addEventListener('mousedown', (event) => {
            if (event.target.classList.contains('time-slot')) {
                isDragging = true;
                dragStartTime = parseInt(event.target.dataset.time, 10);
                dragEndTime = dragStartTime; // Default to the same time
            }
        });

        bookingContainer.addEventListener('mouseover', (event) => {
            if (isDragging && event.target.classList.contains('time-slot')) {
                const currentDragTime = parseInt(event.target.dataset.time, 10);
                if (currentDragTime > dragEndTime) {
                    dragEndTime = currentDragTime;
                }
            }
        });

        bookingContainer.addEventListener('mouseup', (event) => {
            if (isDragging) {
                isDragging = false;

                const roomId = event.target.dataset.roomId;
                openModal(roomId, dragStartTime, dragEndTime + 1);
            }
        });
    }

    // Submit booking form
    bookingForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const bookingDetails = {
            room: document.getElementById('bookingRoom').value,
            time_start: document.getElementById('bookingStart').value,
            time_end: document.getElementById('bookingEnd').value,
            date: document.getElementById('bookingDate').value,
            recurring: recurringDropdown?.value || null,
        };

        if (isAddingNewCustomer) {
            const representative = document.getElementById('representative').value;
            if (!representative) {
                alert('Representative is required.');
                return;
            }
            bookingDetails.booked_by = representative;
        } else {
            bookingDetails.booked_by = existingCustomerDropdown.value;
        }

        try {
            const response = await fetch('/api/bookings', {
                method: 'POST',
                body: JSON.stringify(bookingDetails),
                headers: { 'Content-Type': 'application/json' },
            });

            if (!response.ok) throw new Error('Failed to create booking.');

            closeModal();
            alert('Booking created successfully!');
            location.reload();
        } catch (error) {
            console.error('Error creating booking:', error);
            alert('Error: ' + error.message);
        }
    });

    if (checkElement(cancelBookingButton, "Cancel Booking button")) {
        cancelBookingButton.addEventListener('click', closeModal);
    }
});
