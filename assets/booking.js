// booking.js

// Execute after the DOM has fully loaded
document.addEventListener('DOMContentLoaded', () => {

    // Helper: local YYYY-MM-DD (no UTC conversion)
    const getLocalYmd = (d = new Date()) => {
        const pad = n => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
    };

    // Modal instances for creating and editing bookings
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

    // For editing existing booking
    const editBookingForm = document.getElementById('editBookingForm');
    const cancelEditBookingButton = document.getElementById('cancelEditBooking');
    const updateBookingBtn = document.getElementById('updateBookingBtn');
    const deleteBookingBtn = document.getElementById('deleteBookingBtn');
    const editBookingIdInput = document.getElementById('editBookingId');
    const editBookingRoom = document.getElementById('editBookingRoom');
    const editBookingStart = document.getElementById('editBookingStart');
    const editBookingEnd = document.getElementById('editBookingEnd');
    const editBookingDate = document.getElementById('editBookingDate');
    const editOrganisation = document.getElementById('editOrganisation');
    const editRepresentative = document.getElementById('editRepresentative');
    const editPhone = document.getElementById('editPhone');
    const editEmail = document.getElementById('editEmail');

    // Recurrence handling elements
    const recurringSelect = document.getElementById('recurring');
    const recurrenceCountGroup = document.getElementById('recurrenceCountGroup');
    const recurrenceCountInput = document.getElementById('recurrenceCount');

    // Customer Type selector (for new customer)
    const customerTypeSelect = document.getElementById('customerType');

    // Live cost display (create modal)
    const bookingCostDisplay = document.getElementById('bookingCostDisplay');

    // State variables
    let isAddingNewCustomer = false;
    let currentBookings = [];

    // Pricing caches
    let roomsByName = {}; // { [roomName]: { costIndividual, costPrivate, costCommunity } }

    // Utility function to verify element's existence
    const checkElement = (element, name) => {
        if (!element) {
            console.error(`Missing element: ${name}`);
            return false;
        }
        return true;
    };

    // Retrieve `?date=` query parameter from URL
    const urlParams = new URLSearchParams(window.location.search);
    const pageDate = urlParams.get('date');

    // ---------------------------------------
    // Load and display Bookings in the grid
    // ---------------------------------------
    const loadBookings = async () => {
        try {
            const response = await fetch('/api/bookings');
            if (!response.ok) throw new Error(`HTTP status: ${response.status}`);
            const allBookings = await response.json();

            // If ?date= is set, filter
            currentBookings = pageDate
                ? allBookings.filter(b => b.date === pageDate)
                : allBookings;

            // Map bookings onto the booking grid
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

                    firstSlot.dataset.bookingId = booking.id;
                    slots.slice(1).forEach(s => (s.style.display = 'none'));
                }
            });
        } catch (err) {
            console.error('Error loading bookings:', err);
        }
    };

    // ---------------------------------------
    // Load Customer list for the dropdown menu
    // ---------------------------------------
    const loadCustomers = async () => {
        try {
            const response = await fetch('/api/customers');
            if (!response.ok) throw new Error(`HTTP status: ${response.status}`);
            const customers = await response.json();

            customers.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.representative;
                opt.textContent = `${c.representative} (${c.organisation || 'No Org'})`;
                opt.dataset.type = (c.type || 'individual').toLowerCase();
                existingCustomerDropdown.appendChild(opt);
            });
        } catch (err) {
            console.error('Error loading customers:', err);
        }
    };

    // ---------------------------------------
    // Load Rooms + pricing (hourly) for cost calc
    // ---------------------------------------
    const loadRooms = async () => {
        try {
            const resp = await fetch('/api/rooms');
            if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
            const rooms = await resp.json();
            roomsByName = {};
            rooms.forEach(r => {
                roomsByName[r.room_name] = {
                    costIndividual: Number(r.costIndividual || 0),
                    costPrivate: Number(r.costPrivate || 0),
                    costCommunity: Number(r.costCommunity || 0),
                };
            });
        } catch (e) {
            console.error('Error loading rooms/pricing:', e);
        }
    };

    // Initialise the page
    loadBookings();
    loadCustomers();
    loadRooms();

    // ---------------------------------------
    // Show/hide new customer fields
    // ---------------------------------------
    if (checkElement(addNewCustomerButton, "Add New Customer Button")) {
        addNewCustomerButton.addEventListener('click', () => {
            isAddingNewCustomer = !isAddingNewCustomer;
            newCustomerFields.style.display = isAddingNewCustomer ? 'block' : 'none';
            if (!isAddingNewCustomer) {
                existingCustomerDropdown.value = '';
            }
            renderBookingCost();
        });
    }

    // ---------------------------------------
    // Handle adding a new customer via AJAX
    // ---------------------------------------
    if (checkElement(addCustomerAjaxButton, "Add Customer AJAX Button")) {
        addCustomerAjaxButton.addEventListener('click', async () => {
            const organisation = document.getElementById('organisation').value.trim();
            const representative = document.getElementById('representative').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const email = document.getElementById('email').value.trim();

            // Read new "customerType" field from the select
            const customerType = customerTypeSelect.value;

            if (!organisation || !representative || !phone || !email) {
                alert("Please fill all fields for new customer.");
                return;
            }

            try {
                const resp = await fetch('/api/customers', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        organisation,
                        representative,
                        phone,
                        email,
                        type: customerType
                    }),
                });
                if (!resp.ok) {
                    const t = await resp.text();
                    throw new Error(`Add customer failed: ${t}`);
                }

                const result = await resp.json();
                console.log('Customer added:', result);

                // Add new option to the dropdown
                const opt = document.createElement('option');
                opt.value = representative;
                opt.textContent = `${representative} (${organisation})`;
                opt.dataset.type = (customerType || 'individual').toLowerCase();
                existingCustomerDropdown.appendChild(opt);

                existingCustomerDropdown.value = representative;
                newCustomerFields.style.display = 'none';
                isAddingNewCustomer = false;

                alert("New customer added successfully!");
                renderBookingCost();
            } catch (err) {
                console.error("Error adding customer:", err);
                alert(err.message);
            }
        });
    }

    // ---------------------------------------
    // Drag select for booking
    // ---------------------------------------
    let isDragging = false;
    let dragStartTime = null;
    let dragEndTime = null;
    let activeRoomId = null;
    let dragSelectedSlots = [];

    function clearDragHighlights() {
        dragSelectedSlots.forEach(s => s.classList.remove('drag-select'));
        dragSelectedSlots = [];
    }

    if (checkElement(bookingContainer, "bookingContainer")) {
        bookingContainer.addEventListener('mousedown', (ev) => {
            if (ev.target.classList.contains('booked')) return;
            if (ev.target.classList.contains('time-slot')) {
                isDragging = true;
                dragStartTime = parseInt(ev.target.dataset.time, 10);
                dragEndTime = dragStartTime;
                activeRoomId = ev.target.dataset.roomId;

                clearDragHighlights();
                ev.target.classList.add('drag-select');
                dragSelectedSlots.push(ev.target);
            }
        });

        bookingContainer.addEventListener('mouseover', (ev) => {
            if (
                isDragging &&
                ev.target.classList.contains('time-slot') &&
                ev.target.dataset.roomId === activeRoomId
            ) {
                const currentTime = parseInt(ev.target.dataset.time, 10);
                const minTime = Math.min(dragStartTime, currentTime);
                const maxTime = Math.max(dragStartTime, currentTime);

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
                dragEndTime = currentTime;
            }
        });

        bookingContainer.addEventListener('mouseup', () => {
            if (isDragging) {
                isDragging = false;

                const start = Math.min(dragStartTime, dragEndTime);
                const end = Math.max(dragStartTime, dragEndTime) + 1;
                openCreateBookingModal(activeRoomId, start, end);

                clearDragHighlights();
                activeRoomId = null;
                dragStartTime = null;
                dragEndTime = null;
            }
        });

        // Open the edit modal when a booked slot is clicked.
        bookingContainer.addEventListener('click', async (ev) => {
            if (ev.target.classList.contains('booked')) {
                const bookingId = ev.target.dataset.bookingId;
                if (!bookingId) return;
                await openEditModal(bookingId);
            }
        });
    }

    // ----------------------------------------------------------------
    // Helpers for cost calculation (create modal)
    // ----------------------------------------------------------------
    function currentCustomerType() {
        if (isAddingNewCustomer) {
            return (customerTypeSelect?.value || 'individual').toLowerCase();
        }
        const sel = existingCustomerDropdown.options[existingCustomerDropdown.selectedIndex];
        return (sel?.dataset?.type || 'individual').toLowerCase();
    }

    function currentDurationHours() {
        const s = document.getElementById('bookingStart').value; // "HH:MM"
        const e = document.getElementById('bookingEnd').value;
        if (!s || !e) return 0;
        const [sh] = s.split(':').map(Number);
        const [eh] = e.split(':').map(Number);
        return Math.max(0, eh - sh); // whole hours
    }

    function currentHourly(roomName, custType) {
        const r = roomsByName[roomName];
        if (!r) return 0;
        switch (custType) {
            case 'private':   return r.costPrivate;
            case 'community': return r.costCommunity;
            default:          return r.costIndividual;
        }
    }

    function renderBookingCost() {
        if (!bookingCostDisplay) return;
        const roomName = document.getElementById('bookingRoom').value;
        const type     = currentCustomerType();
        const hours    = currentDurationHours();
        const hourly   = currentHourly(roomName, type);
        const total    = (hourly * hours) || 0;
        bookingCostDisplay.textContent = `£${Number(total).toFixed(2)}`;
    }

    // ----------------------------------------------------------------
    // CREATE BOOKING - SHOW/HIDE MODAL
    // ----------------------------------------------------------------
    function openCreateBookingModal(roomId, startTime, endTime) {
        const slot = document.querySelector(`.time-slot[data-room-id="${roomId}"]`);
        if (!slot) {
            console.error("No slot found for room:", roomId);
            return;
        }

        document.getElementById('bookingRoom').value = slot.dataset.roomName;
        document.getElementById('bookingStart').value = String(startTime).padStart(2, '0') + ':00';
        document.getElementById('bookingEnd').value = String(endTime).padStart(2, '0') + ':00';
        document.getElementById('bookingDate').value =
            pageDate || getLocalYmd();

        bookingModalInstance.show();
        renderBookingCost();
    }

    function closeCreateBookingModal() {
        bookingModalInstance.hide();
        bookingForm.reset();
        existingCustomerDropdown.value = '';
        newCustomerFields.style.display = 'none';
        isAddingNewCustomer = false;
        recurrenceCountGroup.style.display = 'none';
        if (bookingCostDisplay) bookingCostDisplay.textContent = '£0.00';
    }

    if (checkElement(cancelBookingButton, "Cancel Booking button")) {
        cancelBookingButton.addEventListener('click', () => {
            closeCreateBookingModal();
        });
    }

    // Recompute cost on field changes
    const bookingStartEl = document.getElementById('bookingStart');
    const bookingEndEl   = document.getElementById('bookingEnd');
    if (existingCustomerDropdown) {
        existingCustomerDropdown.addEventListener('change', renderBookingCost);
    }
    if (customerTypeSelect) {
        customerTypeSelect.addEventListener('change', renderBookingCost);
    }
    if (bookingStartEl) bookingStartEl.addEventListener('change', renderBookingCost);
    if (bookingEndEl)   bookingEndEl.addEventListener('change', renderBookingCost);

    // ----------------------------------------------------------------
    // SHOW/HIDE the "Number of Occurrences" field
    // ----------------------------------------------------------------
    if (recurringSelect) {
        recurringSelect.addEventListener('change', () => {
            const val = recurringSelect.value;
            if (val === 'none') {
                recurrenceCountGroup.style.display = 'none';
            } else {
                recurrenceCountGroup.style.display = 'block';
            }
        });
    }

    // ----------------------------------------------------------------
    // CREATE BOOKING - SUBMIT (including conflict detection handling)
    // ----------------------------------------------------------------
    bookingForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const bookingDetails = {
            room: document.getElementById('bookingRoom').value,
            time_start: document.getElementById('bookingStart').value,
            time_end: document.getElementById('bookingEnd').value,
            date: document.getElementById('bookingDate').value,
            booked_by: isAddingNewCustomer
                ? document.getElementById('representative').value
                : existingCustomerDropdown.value,
            recurring: (recurringSelect?.value || 'none').toLowerCase(),
            recurrence_count: parseInt(recurrenceCountInput.value, 10) || 1
        };

        try {
            const response = await fetch('/api/bookings', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(bookingDetails),
            });

            if (response.status === 409) {
                // Handle conflict warning response.
                const conflictData = await response.json();
                let message = "The following recurring booking occurrences have conflicts:\n";
                conflictData.conflicts.forEach(conflict => {
                    message += `Date: ${conflict.date}, Room: ${conflict.room}, Time: ${conflict.start} - ${conflict.end}\n`;
                });
                message += "\nTo omit these occurrences, click OK. To fix them, please select replacement rooms and re-submit.";
                if (confirm(message)) {
                    // User confirms to omit conflicting occurrences.
                    bookingDetails.force = true;
                    const retryResponse = await fetch('/api/bookings', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(bookingDetails),
                    });
                    if (!retryResponse.ok) {
                        const errText = await retryResponse.text();
                        throw new Error(`Failed to create booking on retry. ${errText}`);
                    }
                    alert("Booking created successfully (conflicting occurrences omitted)!");
                    closeCreateBookingModal();
                    location.reload();
                } else {
                    alert("Please adjust the conflicting occurrences (e.g. choose replacement rooms) and try again.");
                }
                return;
            }

            if (!response.ok) {
                const errText = await response.text();
                throw new Error(`Failed to create booking. ${errText}`);
            }

            alert("Booking created successfully!");
            closeCreateBookingModal();
            location.reload();
        } catch (err) {
            console.error("Error creating booking:", err);
            alert(err.message);
        }
    });

    // ----------------------------------------------------------------
    // EDIT BOOKING
    // ----------------------------------------------------------------
    async function openEditModal(bookingId) {
        try {
            let bookingData = currentBookings.find(b => b.id == bookingId);
            if (!bookingData) {
                const resp = await fetch(`/api/bookings/${bookingId}`);
                if (!resp.ok) throw new Error("Could not fetch booking details");
                bookingData = await resp.json();
            }

            editBookingIdInput.value = bookingId;
            editBookingRoom.value = bookingData.room_id;
            editBookingStart.value = bookingData.time_start;
            editBookingEnd.value = bookingData.time_end;
            editBookingDate.value = bookingData.date;
            editOrganisation.value = bookingData.organisation || '';
            editRepresentative.value = bookingData.booked_by || '';
            editPhone.value = bookingData.phone || '';
            editEmail.value = bookingData.email || '';

            editBookingModalInstance.show();
        } catch (err) {
            console.error("Error opening edit modal:", err);
            alert(err.message);
        }
    }

    function closeEditModal() {
        editBookingModalInstance.hide();
        editBookingForm.reset();
        editBookingIdInput.value = '';
    }

    if (cancelEditBookingButton) {
        cancelEditBookingButton.addEventListener('click', closeEditModal);
    }

    if (updateBookingBtn) {
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
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                });
                if (!resp.ok) {
                    const t = await resp.text();
                    throw new Error(`Failed to update booking: ${t}`);
                }
                alert("Booking updated successfully!");
                closeEditModal();
                location.reload();
            } catch (err) {
                console.error("Update booking error:", err);
                alert(err.message);
            }
        });
    }

    if (deleteBookingBtn) {
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
                    const t = await resp.text();
                    throw new Error(`Failed to delete booking: ${t}`);
                }
                alert("Booking deleted successfully.");
                closeEditModal();
                location.reload();
            } catch (err) {
                console.error("Delete booking error:", err);
                alert(err.message);
            }
        });
    }
});
