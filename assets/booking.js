document.addEventListener('DOMContentLoaded', () => {
    // Elements for creating a new booking
    const bookingContainer = document.getElementById('booking-grid');
    const bookingModal = document.getElementById('bookingModal');
    const modalOverlay = document.getElementById('modalOverlay');
    const bookingForm = document.getElementById('bookingForm');
    const cancelBookingButton = document.getElementById('cancelBooking');
    const addNewCustomerButton = document.getElementById('addNewCustomer');
    const newCustomerFields = document.getElementById('newCustomerFields');
    const existingCustomerDropdown = document.getElementById('existingCustomer');
    const addCustomerAjaxButton = document.getElementById('addCustomerAjax');

    // Elements for editing an existing booking (SECOND modal)
    const editBookingOverlay = document.getElementById('editBookingOverlay'); // NEW
    const editBookingModal = document.getElementById('editBookingModal');     // NEW
    const editBookingForm = document.getElementById('editBookingForm');       // NEW
    const cancelEditBookingButton = document.getElementById('cancelEditBooking'); // NEW
    const updateBookingBtn = document.getElementById('updateBookingBtn');     // NEW
    const deleteBookingBtn = document.getElementById('deleteBookingBtn');     // NEW

    // Hidden input for booking ID
    const editBookingIdInput = document.getElementById('editBookingId');      // NEW

    // Fields for editing booking
    const editBookingRoom = document.getElementById('editBookingRoom');       // NEW
    const editBookingStart = document.getElementById('editBookingStart');     // NEW
    const editBookingEnd = document.getElementById('editBookingEnd');         // NEW
    const editBookingDate = document.getElementById('editBookingDate');       // NEW
    const editOrganisation = document.getElementById('editOrganisation');     // NEW
    const editRepresentative = document.getElementById('editRepresentative'); // NEW
    const editPhone = document.getElementById('editPhone');                   // NEW
    const editEmail = document.getElementById('editEmail');                   // NEW

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

    // We'll keep an in-memory store of bookings for quick access
    let currentBookings = []; // NEW

    // Filter logic: Only display bookings for 'pageDate'
    const loadBookings = async () => {
        try {
            const response = await fetch('/api/bookings');
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

            const allBookings = await response.json();
            // If 'pageDate' is set in the URL, we only load that day's bookings
            currentBookings = pageDate
                ? allBookings.filter(b => b.date === pageDate)
                : allBookings;

            currentBookings.forEach(booking => {
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

                    // Store booking.id (or some unique ID) on the slot for easy reference
                    firstSlot.dataset.bookingId = booking.id; // NEW

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

    // "Add" button for new customer creation via AJAX
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

                const result = await response.json();
                console.log('New customer added:', result);

                // Add new option to the existing customer dropdown
                const option = document.createElement('option');
                option.value = representative;
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

    // Close modal for creation
    const closeModal = () => {
        bookingModal.style.display = 'none';
        modalOverlay.style.display = 'none';
        resetModalFields();
    };

    // Open modal for creation
    const openModal = (roomId, startTime, endTime) => {
        const roomNameElement = document.querySelector(`.time-slot[data-room-id="${roomId}"]`);
        if (!roomNameElement) {
            console.error("Room element not found for ID:", roomId);
            return;
        }

        const roomName = roomNameElement.dataset.roomName;

        document.getElementById('bookingRoom').value = roomName;
        document.getElementById('bookingStart').value = String(startTime).padStart(2, '0') + ':00';
        document.getElementById('bookingEnd').value = String(endTime).padStart(2, '0') + ':00';
        document.getElementById('bookingDate').value = pageDate || new Date().toISOString().split('T')[0];

        // Show the overlay + modal
        modalOverlay.style.display = 'block';
        bookingModal.style.display = 'block';
    };

    // ----------------------------------------------------------------
    // NEW: EDIT-BOOKING (SECOND MODAL) LOGIC
    // ----------------------------------------------------------------

    // 1) Open the second modal when clicking on an already-booked slot
    bookingContainer.addEventListener('click', async (event) => {
        if (event.target.classList.contains('booked')) {
            const bookingId = event.target.dataset.bookingId;
            if (!bookingId) {
                console.error("No booking ID found on this slot.");
                return;
            }
            // show the Edit Booking modal
            await openEditModal(bookingId);
        }
    });

    // 2) openEditModal function
    const openEditModal = async (bookingId) => {
        try {
            // Option A: We could fetch booking details from the server:
            // e.g., GET /api/bookings/:id
            // For simplicity, we'll see if we already have it in currentBookings
            let bookingData = currentBookings.find(b => b.id == bookingId);

            if (!bookingData) {
                // fallback: fetch from server
                const resp = await fetch(`/api/bookings/${bookingId}`);
                if (!resp.ok) throw new Error(`Could not fetch booking ${bookingId}`);
                bookingData = await resp.json();
            }

            // Now populate the form
            editBookingIdInput.value = bookingId;
            editBookingRoom.value = bookingData.room_id; // read-only
            editBookingStart.value = bookingData.time_start;
            editBookingEnd.value = bookingData.time_end;
            editBookingDate.value = bookingData.date;

            // If bookingData has attached customer data:
            // e.g. bookingData.customer = { organisation, representative, phone, email }
            // you might need to fetch that from a different endpoint or from the same booking object
            // Example:
            editOrganisation.value = bookingData.organisation || '';
            editRepresentative.value = bookingData.booked_by || '';
            editPhone.value = bookingData.phone || '';
            editEmail.value = bookingData.email || '';

            // Show overlay + modal
            editBookingOverlay.style.display = 'block';
            editBookingModal.style.display = 'block';

        } catch (err) {
            console.error("Error opening edit modal:", err);
            alert("Could not load booking details. See console.");
        }
    };

    // 3) Cancel button for the edit modal
    const closeEditModal = () => {
        editBookingOverlay.style.display = 'none';
        editBookingModal.style.display = 'none';
        editBookingForm.reset();
        // Clear hidden ID
        editBookingIdInput.value = '';
    };

    if (checkElement(cancelEditBookingButton, "Cancel Edit Booking button")) {
        cancelEditBookingButton.addEventListener('click', closeEditModal);
    }

    // 4) Update the booking
    if (checkElement(updateBookingBtn, "Update Booking button")) {
        updateBookingBtn.addEventListener('click', async (e) => {
            e.preventDefault(); // don't let form submission reload page

            const bookingId = editBookingIdInput.value;
            if (!bookingId) {
                alert("No booking ID found.");
                return;
            }

            // Gather updated details
            const payload = {
                room_id: editBookingRoom.value,  // might remain read-only
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
                    method: 'PUT', // or PATCH, depending on your API
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

    // 5) Delete booking
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

    // -------------------------------------------------------------
    // DRAGGING LOGIC for creating new booking
    // -------------------------------------------------------------
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

    // Submit booking form (create)
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

        console.log('Payload being sent (create booking):', bookingDetails);

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
