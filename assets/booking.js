// booking.js
document.addEventListener('DOMContentLoaded', () => {
    // ----------------------------------------------------------------
    // BOOTSTRAP MODAL INSTANCES
    // We'll create them once so we can call show/hide easily.
    // Make sure <script src="...bootstrap.bundle.min.js"></script> is loaded in base.html.twig
    // ----------------------------------------------------------------
    const bookingModalElement = document.getElementById('bookingModal');
    const bookingModalInstance = new bootstrap.Modal(bookingModalElement);

    const editBookingModalElement = document.getElementById('editBookingModal');
    const editBookingModalInstance = new bootstrap.Modal(editBookingModalElement);

    // Elements for creating a new booking
    const bookingContainer = document.getElementById('booking-grid');
    const bookingForm = document.getElementById('bookingForm');
    const cancelBookingButton = document.getElementById('cancelBooking');
    const addNewCustomerButton = document.getElementById('addNewCustomer');
    const newCustomerFields = document.getElementById('newCustomerFields');
    const existingCustomerDropdown = document.getElementById('existingCustomer');
    const addCustomerAjaxButton = document.getElementById('addCustomerAjax');

    // Elements for editing an existing booking (SECOND modal)
    const editBookingForm = document.getElementById('editBookingForm');
    const cancelEditBookingButton = document.getElementById('cancelEditBooking');
    const updateBookingBtn = document.getElementById('updateBookingBtn');
    const deleteBookingBtn = document.getElementById('deleteBookingBtn');

    // Hidden input for booking ID
    const editBookingIdInput = document.getElementById('editBookingId');

    // Fields for editing booking
    const editBookingRoom = document.getElementById('editBookingRoom');
    const editBookingStart = document.getElementById('editBookingStart');
    const editBookingEnd = document.getElementById('editBookingEnd');
    const editBookingDate = document.getElementById('editBookingDate');
    const editOrganisation = document.getElementById('editOrganisation');
    const editRepresentative = document.getElementById('editRepresentative');
    const editPhone = document.getElementById('editPhone');
    const editEmail = document.getElementById('editEmail');

    let isAddingNewCustomer = false; // Track if a new customer is being added

    // For storing bookings in memory
    let currentBookings = [];

    // Helper to check for element existence
    const checkElement = (element, elementName) => {
        if (!element) {
            console.error(`${elementName} not found in the DOM.`);
            return false;
        }
        return true;
    };

    // Grab the page date from URL
    const urlParams = new URLSearchParams(window.location.search);
    const pageDate = urlParams.get('date');

    // Load Bookings
    const loadBookings = async () => {
        try {
            const response = await fetch('/api/bookings');
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

            const allBookings = await response.json();
            // Filter if date is set
            currentBookings = pageDate
                ? allBookings.filter(b => b.date === pageDate)
                : allBookings;

            currentBookings.forEach(booking => {
                const startTime = parseInt(booking.time_start.split(':')[0]);
                const endTime = parseInt(booking.time_end.split(':')[0]);
                const roomName = booking.room_id;

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

                    // Attach booking ID for editing
                    firstSlot.dataset.bookingId = booking.id;

                    slots.slice(1).forEach(slot => (slot.style.display = 'none'));
                }
            });
        } catch (error) {
            console.error('Error loading bookings:', error);
        }
    };

    // Load Customers
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

    // Add new customer via AJAX
    if (checkElement(addCustomerAjaxButton, "Add Customer Ajax button")) {
        addCustomerAjaxButton.addEventListener('click', async () => {
            const organisation = document.getElementById('organisation').value.trim();
            const representative = document.getElementById('representative').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const email = document.getElementById('email').value.trim();

            if (!representative || !phone || !email || !organisation) {
                alert('Please fill in all fields before adding a new customer.');
                return;
            }

            try {
                const response = await fetch('/api/customers', {
                    method: 'POST',
                    body: JSON.stringify({ organisation, representative, phone, email }),
                    headers: { 'Content-Type': 'application/json' },
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Error adding customer:', errorText);
                    throw new Error('Failed to add new customer. See console for details.');
                }

                const result = await response.json();
                console.log('New customer added:', result);

                // Add new option
                const option = document.createElement('option');
                option.value = representative;
                option.textContent = `${representative} (${organisation})`;
                existingCustomerDropdown.appendChild(option);

                existingCustomerDropdown.value = representative;
                newCustomerFields.style.display = 'none';
                isAddingNewCustomer = false;

                alert('New customer added successfully!');
            } catch (error) {
                console.error('Error in addCustomerAjaxButton:', error);
                alert(error.message);
            }
        });
    }

    // ----------------------------------------------------------------
    // DRAG SELECT LOGIC
    // ----------------------------------------------------------------
    let isDragging = false;
    let dragStartTime = null;
    let dragEndTime = null;
    let activeRoomId = null;
    let dragSelectedSlots = [];

    const clearDragHighlights = () => {
        dragSelectedSlots.forEach(slot => slot.classList.remove('drag-select'));
        dragSelectedSlots = [];
    };

    if (checkElement(bookingContainer, "Booking container")) {
        bookingContainer.addEventListener('mousedown', (event) => {
            if (event.target.classList.contains('booked')) {
                // It's a booked slot -> skip highlight drag
                return;
            }
            if (event.target.classList.contains('time-slot')) {
                isDragging = true;
                dragStartTime = parseInt(event.target.dataset.time, 10);
                dragEndTime = dragStartTime;
                activeRoomId = event.target.dataset.roomId;

                clearDragHighlights();
                event.target.classList.add('drag-select');
                dragSelectedSlots.push(event.target);
            }
        });

        bookingContainer.addEventListener('mouseover', (event) => {
            if (
                isDragging &&
                event.target.classList.contains('time-slot') &&
                event.target.dataset.roomId === activeRoomId
            ) {
                const currentDragTime = parseInt(event.target.dataset.time, 10);

                const minTime = Math.min(dragStartTime, currentDragTime);
                const maxTime = Math.max(dragStartTime, currentDragTime);

                clearDragHighlights();
                for (let t = minTime; t <= maxTime; t++) {
                    const slot = document.querySelector(
                        `.time-slot[data-room-id="${activeRoomId}"][data-time="${t}"]`
                    );
                    if (slot) {
                        slot.classList.add('drag-select');
                        dragSelectedSlots.push(slot);
                    }
                }
                dragEndTime = currentDragTime;
            }
        });

        bookingContainer.addEventListener('mouseup', () => {
            if (isDragging) {
                isDragging = false;

                const start = Math.min(dragStartTime, dragEndTime);
                const end = Math.max(dragStartTime, dragEndTime) + 1;
                const roomId = activeRoomId;

                clearDragHighlights();
                openCreateBookingModal(roomId, start, end);

                activeRoomId = null;
                dragStartTime = null;
                dragEndTime = null;
            }
        });

        // 1) If user clicks a booked slot, open the edit modal
        bookingContainer.addEventListener('click', async (event) => {
            if (event.target.classList.contains('booked')) {
                const bookingId = event.target.dataset.bookingId;
                if (!bookingId) {
                    console.error("No booking ID found on this slot.");
                    return;
                }
                await openEditModal(bookingId);
            }
        });
    }

    // ----------------------------------------------------------------
    // CREATE BOOKING - BOOTSTRAP MODAL
    // ----------------------------------------------------------------
    const openCreateBookingModal = (roomId, startTime, endTime) => {
        const roomSlot = document.querySelector(`.time-slot[data-room-id="${roomId}"]`);
        if (!roomSlot) {
            console.error("Room element not found for ID:", roomId);
            return;
        }

        document.getElementById('bookingRoom').value = roomSlot.dataset.roomName;
        document.getElementById('bookingStart').value = String(startTime).padStart(2, '0') + ':00';
        document.getElementById('bookingEnd').value = String(endTime).padStart(2, '0') + ':00';
        document.getElementById('bookingDate').value = pageDate || new Date().toISOString().split('T')[0];

        // Show the Bootstrap modal
        bookingModalInstance.show();
    };

    const closeCreateBookingModal = () => {
        bookingModalInstance.hide();
        bookingForm.reset();
        existingCustomerDropdown.value = '';
        newCustomerFields.style.display = 'none';
        isAddingNewCustomer = false;
    };

    // Create booking form submission
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
            recurring: (document.getElementById('recurring')?.value || 'none').toLowerCase(),
        };

        try {
            const response = await fetch('/api/bookings', {
                method: 'POST',
                body: JSON.stringify(bookingDetails),
                headers: { 'Content-Type': 'application/json' },
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error('Failed to create booking. ' + errorText);
            }

            alert('Booking created successfully!');
            closeCreateBookingModal();
            location.reload(); // Refresh to see new booking
        } catch (error) {
            console.error('Error creating booking:', error);
            alert(`Error: ${error.message}`);
        }
    });

    if (checkElement(cancelBookingButton, "Cancel Booking button")) {
        cancelBookingButton.addEventListener('click', () => {
            closeCreateBookingModal();
        });
    }

    // ----------------------------------------------------------------
    // EDIT BOOKING - BOOTSTRAP MODAL
    // ----------------------------------------------------------------
    const openEditModal = async (bookingId) => {
        try {
            let bookingData = currentBookings.find(b => b.id == bookingId);

            if (!bookingData) {
                // fallback: fetch from server
                const resp = await fetch(`/api/bookings/${bookingId}`);
                if (!resp.ok) throw new Error(`Could not fetch booking ${bookingId}`);
                bookingData = await resp.json();
            }

            editBookingIdInput.value = bookingId;
            editBookingRoom.value = bookingData.room_id; // read-only
            editBookingStart.value = bookingData.time_start;
            editBookingEnd.value = bookingData.time_end;
            editBookingDate.value = bookingData.date;
            editOrganisation.value = bookingData.organisation || '';
            editRepresentative.value = bookingData.booked_by || '';
            editPhone.value = bookingData.phone || '';
            editEmail.value = bookingData.email || '';

            // Show the Bootstrap modal
            editBookingModalInstance.show();
        } catch (err) {
            console.error("Error opening edit modal:", err);
            alert("Could not load booking details. See console.");
        }
    };

    const closeEditModal = () => {
        editBookingModalInstance.hide();
        editBookingForm.reset();
        editBookingIdInput.value = '';
    };

    if (checkElement(cancelEditBookingButton, "Cancel Edit Booking button")) {
        cancelEditBookingButton.addEventListener('click', closeEditModal);
    }

    if (checkElement(updateBookingBtn, "Update Booking button")) {
        updateBookingBtn.addEventListener('click', async (e) => {
            e.preventDefault();

            const bookingId = editBookingIdInput.value;
            if (!bookingId) {
                alert("No booking ID found.");
                return;
            }

            const payload = {
                room_id: editBookingRoom.value,
                time_start: editBookingStart.value,
                time_end: editBookingEnd.value,
                date: editBookingDate.value,
                organisation: editOrganisation.value,
                booked_by: editRepresentative.value,
                phone: editPhone.value,
                email: editEmail.value,
            };

            try {
                const resp = await fetch(`/api/bookings/${bookingId}`, {
                    method: 'PUT', // or PATCH
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                });
                if (!resp.ok) {
                    const errorText = await resp.text();
                    throw new Error("Failed to update booking: " + errorText);
                }
                alert("Booking updated successfully!");
                closeEditModal();
                location.reload();
            } catch (error) {
                console.error("Update booking error:", error);
                alert(error.message);
            }
        });
    }

    if (checkElement(deleteBookingBtn, "Delete Booking button")) {
        deleteBookingBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            const bookingId = editBookingIdInput.value;
            if (!bookingId) {
                alert("No booking ID found.");
                return;
            }
            if (!confirm("Are you sure you want to delete this booking?")) {
                return;
            }

            try {
                const resp = await fetch(`/api/bookings/${bookingId}`, {
                    method: 'DELETE',
                });
                if (!resp.ok) {
                    const errorText = await resp.text();
                    throw new Error("Failed to delete booking: " + errorText);
                }
                alert("Booking deleted successfully.");
                closeEditModal();
                location.reload();
            } catch (error) {
                console.error("Delete booking error:", error);
                alert(error.message);
            }
        });
    }
});
