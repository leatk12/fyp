document.addEventListener('DOMContentLoaded', () => {
    // This field corresponds to the name of the form input "isExpenseOrRevenue"
    const isExpRevField = document.querySelector('#transaction_isExpenseOrRevenue');
    if (!isExpRevField) return; // not on the new transaction page

    const addLinesBtn = document.getElementById('addLinesBtn');
    const linesModalEl = document.getElementById('linesModal');
    const linesModal = new bootstrap.Modal(linesModalEl);
    const linesContainer = document.getElementById('linesContainer');
    const addLineBtn = document.getElementById('addLineBtn');

    // The "amount" input
    const amountField = document.getElementById('transaction_amount');

    // We read these global vars from the Twig script tags
    const expenseUrl = window.createExpenseUrl;
    const revenueUrl = window.createRevenueUrl;
    const draftUuid = window.draftUuid;
    const suppliers = window.suppliers || [];

    let linesTotal = 0; // Running total of line amounts

    // Show the "Add Lines" button only if isExpenseOrRevenue == 'EXPENSE' or 'REVENUE'
    function updateAddLinesButton() {
        const val = isExpRevField.value;
        if (val === 'EXPENSE' || val === 'REVENUE') {
            addLinesBtn.style.display = 'inline-block';
        } else {
            addLinesBtn.style.display = 'none';
        }
    }
    updateAddLinesButton();
    isExpRevField.addEventListener('change', updateAddLinesButton);

    // When user clicks "Add Lines", open modal (and clear old lines in the modal)
    addLinesBtn.addEventListener('click', () => {
        linesContainer.innerHTML = "";
        linesModal.show();
    });

    // "Add Another Line" => create fields for either expense or revenue
    addLineBtn.addEventListener('click', () => {
        const lineDiv = document.createElement('div');
        lineDiv.classList.add('border', 'p-2', 'mb-2');

        const val = isExpRevField.value; // "EXPENSE" or "REVENUE"

        if (val === 'EXPENSE') {
            // Include a supplier <select> + description + amount
            lineDiv.innerHTML = `
                <label>Supplier</label>
                <select class="form-control lineSupplier mb-2"></select>

                <label>Description</label>
                <input type="text" class="form-control lineDesc mb-2" placeholder="Description"/>

                <label>Amount</label>
                <input type="number" step="0.01" class="form-control lineAmt mb-2" placeholder="Amount"/>

                <button type="button" class="btn btn-sm btn-success commitLineBtn">Save this Expense</button>
            `;

            // Populate the supplier dropdown
            const supplierSelect = lineDiv.querySelector('.lineSupplier');
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Select a supplier...';
            supplierSelect.appendChild(defaultOption);

            suppliers.forEach(sup => {
                const opt = document.createElement('option');
                opt.value = sup.id;
                // Use the 'organisation' field as requested
                opt.textContent = sup.organisation || 'No Organisation';
                supplierSelect.appendChild(opt);
            });
        } else {
            // REVENUE
            // Use a unique ID for the checkbox to ensure uniqueness if multiple lines are added.
            const uniqueId = 'spendingConstraints-' + Date.now();
            lineDiv.innerHTML = `
                <label>Source</label>
                <input type="text" class="form-control lineSource mb-2" placeholder="Revenue source"/>

                <label>Amount</label>
                <input type="number" step="0.01" class="form-control lineAmt mb-2" placeholder="Amount"/>

                <div class="form-check mb-2">
                    <input class="form-check-input lineSpendingConstraints" type="checkbox" value="1" id="${uniqueId}">
                    <label class="form-check-label" for="${uniqueId}">Spending Constraints</label>
                </div>
                <div class="actual-constraints-container mb-2" style="display: none;">
                    <label>Actual Constraints</label>
                    <input type="text" class="form-control lineActualConstraints" placeholder="Actual Constraints"/>
                </div>

                <button type="button" class="btn btn-sm btn-success commitLineBtn">Save this Revenue</button>
            `;

            // Add event listener on the checkbox to toggle the actual constraints field visibility
            const checkbox = lineDiv.querySelector('.lineSpendingConstraints');
            const actualContainer = lineDiv.querySelector('.actual-constraints-container');
            checkbox.addEventListener('change', function() {
                actualContainer.style.display = this.checked ? 'block' : 'none';
            });
        }

        linesContainer.appendChild(lineDiv);
    });

    // When user clicks "Save this Expense/Revenue", do an AJAX call
    linesContainer.addEventListener('click', async (event) => {
        if (!event.target.classList.contains('commitLineBtn')) return;

        const parent = event.target.closest('div');
        const val = isExpRevField.value; // "EXPENSE" or "REVENUE"
        let amtVal = 0;

        if (val === 'EXPENSE') {
            // Gather data including supplier
            const supplierId = parent.querySelector('.lineSupplier')?.value || null;
            const desc = parent.querySelector('.lineDesc')?.value || '';
            amtVal = parseFloat(parent.querySelector('.lineAmt')?.value || '0');

            const payload = {
                draftUuid: draftUuid,
                supplierId: supplierId,
                description: desc,
                amount: amtVal
            };

            try {
                const resp = await fetch(expenseUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                });
                const result = await resp.json();
                if (result.error) {
                    alert('Error: ' + result.error);
                } else {
                    parent.innerHTML += `<div class="text-success">Expense saved (ID: ${result.id})</div>`;
                }
            } catch (e) {
                console.error(e);
                alert('Failed to create expense line.');
            }
        } else if (val === 'REVENUE') {
            // Gather data for revenue
            const source = parent.querySelector('.lineSource')?.value || '';
            amtVal = parseFloat(parent.querySelector('.lineAmt')?.value || '0');
            const spendingConstraints = parent.querySelector('.lineSpendingConstraints')?.checked || false;
            let actualConstraints = '';
            if (spendingConstraints) {
                actualConstraints = parent.querySelector('.lineActualConstraints')?.value || '';
            }

            const payload = {
                draftUuid: draftUuid,
                source: source,
                amount: amtVal,
                spending_constraints: spendingConstraints,
                actual_constraints: actualConstraints
            };

            try {
                const resp = await fetch(revenueUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                });
                const result = await resp.json();
                if (result.error) {
                    alert('Error: ' + result.error);
                } else {
                    parent.innerHTML += `<div class="text-success">Revenue saved (ID: ${result.id})</div>`;
                }
            } catch (e) {
                console.error(e);
                alert('Failed to create revenue line.');
            }
        }

        // Add this line’s amount to the linesTotal and update the transaction_amount
        if (!isNaN(amtVal) && amountField) {
            linesTotal += amtVal;
            amountField.value = linesTotal.toFixed(2);
        }
    });
});
