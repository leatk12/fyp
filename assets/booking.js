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

    // The "Add" button inside new customer fields
    const addCustomerAjaxButton = document.getElementById('addCustomerAjax');

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

    // Grab the page date from URL (e.g., ?date=2025-01-26)
    const urlParams = new URLSearchParams(window.location.search);
    const pageDate = urlParams.get('date');

    // Filter logic: Only display bookings for 'pageDate'
    const loadBookings = async () => {
        try {
            const response = await fetch('/api/bookings');
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

            const allBookings = await response.json();
            // If 'pageDate' is set in the URL, we only load that day's bookings
            const bookings = pageDate
                ? allBookings.filter(b => b.date === pageDate)
                : allBookings;

            bookings.forEach(booking => {
                const startTime = parseInt(booking.time_start.split(':')[0]);
                const endTime = parseInt(booking.time_end.split(':')[0]);
                const roomName = booking.room_id;

                console.log(`Processing booking: ${JSON.stringify(booking)}`);

                const slots = [];
                for (let time = startTime; time < endTime; time++) {
                    const slot = document.querySelector(
                        `.time-slot[data-room-id="${roomName}"][data-time="${time}"]`
                    );
                    if (slot) slots.push(slot);
                }

                if (slots.length > 0) {
                    const firstSlot = slots[0];
                    const totalRows = slots.length;

                    firstSlot.style.gridRow = `span ${totalRows}`;
                    firstSlot.classList.add('booked');
                    firstSlot.textContent = booking.booked_by;
                    firstSlot.style.backgroundColor = 'blue';
                    firstSlot.style.color = 'white';
                    firstSlot.style.textAlign = 'center';

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

    // Initialize data
    loadBookings();
    loadCustomers();

    // Toggle new customer fields
    if (checkElement(addNewCustomerButton, "Add New Customer button")) {
        addNewCustomerButton.addEventListener('click', () => {
            isAddingNewCustomer = !isAddingNewCustomer;
            newCustomerFields.style.display = isAddingNewCustomer ? 'block' : 'none';
            if (!isAddingNewCustomer) {
                existingCustomerDropdown.value = '';
            }
        });
    }

    // ***** NEW: Add an event listener for the "Add" button that does an AJAX call *****
    if (checkElement(addCustomerAjaxButton, "Add Customer Ajax button")) {
        addCustomerAjaxButton.addEventListener('click', async () => {
            // Gather the new customer fields
            const organisation = document.getElementById('organisation').value.trim();
            const representative = document.getElementById('representative').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const email = document.getElementById('email').value.trim();

            if (!representative || !phone || !email || !organisation) {
                alert('Please fill in all fields before adding a new customer.');
                return;
            }

            const newCustomerDetails = {
                organisation,
                representative,
                phone,
                email
            };

            try {
                const response = await fetch('/api/customers', {
                    method: 'POST',
                    body: JSON.stringify(newCustomerDetails),
                    headers: { 'Content-Type': 'application/json' },
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Error adding customer:', errorText);
                    throw new Error('Failed to add new customer. See console for details.');
                }

                // If successful, parse the result & update the dropdown
                const result = await response.json();
                console.log('New customer added:', result);

                // Add new option to the existing customer dropdown
                const option = document.createElement('option');
                option.value = representative; // or the ID if you prefer
                option.textContent = `${representative} (${organisation})`;
                existingCustomerDropdown.appendChild(option);

                // Select the newly added customer in the dropdown
                existingCustomerDropdown.value = representative;

                // Hide the new customer fields
                newCustomerFields.style.display = 'none';
                isAddingNewCustomer = false;

                alert('New customer added successfully!');
            } catch (error) {
                console.error('Error in addCustomerAjaxButton:', error);
                alert(error.message);
            }
        });
    }

    // Reset modal fields
    const resetModalFields = () => {
        bookingForm.reset();
        existingCustomerDropdown.value = '';
        newCustomerFields.style.display = 'none';
        isAddingNewCustomer = false;
    };

    // Close modal
    const closeModal = () => {
        bookingModal.style.display = 'none';
        modalOverlay.style.display = 'none';
        resetModalFields();
    };

    // Open modal
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
        document.getElementById('bookingDate').value = pageDate || new Date().toISOString().split('T')[0];

        bookingModal.style.display = 'block';
        modalOverlay.style.display = 'block';
    };

    let isDragging = false;
    let dragStartTime = null;
    let dragEndTime = null;

    if (checkElement(bookingContainer, "Booking container")) {
        bookingContainer.addEventListener('mousedown', (event) => {
            if (event.target.classList.contains('time-slot')) {
                isDragging = true;
                dragStartTime = parseInt(event.target.dataset.time, 10);
                dragEndTime = dragStartTime;
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
            booked_by: isAddingNewCustomer
                ? document.getElementById('representative').value
                : existingCustomerDropdown.value,
            // We keep 'recurring' if you want to handle recurrence
            recurring: document.getElementById('recurring')?.value?.toLowerCase() || 'none',
        };

        console.log('Payload being sent:', bookingDetails);

        try {
            const response = await fetch('/api/bookings', {
                method: 'POST',
                body: JSON.stringify(bookingDetails),
                headers: { 'Content-Type': 'application/json' },
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Error response:', errorText);
                throw new Error('Failed to create booking. See console for details.');
            }

            alert('Booking created successfully!');
            closeModal();
            location.reload();
        } catch (error) {
            console.error('Error creating booking:', error);
            alert(`Error: ${error.message}`);
        }
    });

    if (checkElement(cancelBookingButton, "Cancel Booking button")) {
        cancelBookingButton.addEventListener('click', closeModal);
    }
});
