// assets/report.js
document.addEventListener("DOMContentLoaded", () => {
    const startEl = document.getElementById("startDate");
    const endEl = document.getElementById("endDate");
    const form = document.getElementById("rangeForm");
    const tbody = document.querySelector("#reportTable tbody");
    const rangeSummary = document.getElementById("rangeSummary");
    const countSummary = document.getElementById("countSummary");
    const printBtn = document.getElementById("printBtn");

    const pad = (n) => String(n).padStart(2, "0");
    const ymd = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

    // Default to "today"
    const today = new Date();
    if (startEl) startEl.value = ymd(today);
    if (endEl) endEl.value = ymd(today);

    // Presets
    document.querySelectorAll(".preset").forEach((btn) => {
        btn.addEventListener("click", () => {
            const range = btn.dataset.range;
            const s = new Date();
            const e = new Date();
            switch (range) {
                case "day":
                    // today
                    break;
                case "week":
                    e.setDate(e.getDate() + 6);
                    break;
                case "2weeks":
                    e.setDate(e.getDate() + 13);
                    break;
                case "month":
                    e.setDate(e.getDate() + 29);
                    break;
            }
            startEl.value = ymd(s);
            endEl.value = ymd(e);
        });
    });

    form.addEventListener("submit", async (ev) => {
        ev.preventDefault();
        const start = startEl.value;
        const end = endEl.value;

        if (!start || !end) {
            alert("Please choose both start and end dates.");
            return;
        }
        if (end < start) {
            alert("End date must be on or after start date.");
            return;
        }

        rangeSummary.textContent = `Bookings from ${start} to ${end}`;
        tbody.innerHTML = "";
        countSummary.textContent = "Loading…";

        try {
            // Correct route path and proper template literals
            const res = await fetch(
                `/api/bookings/range?start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}`
            );
            if (!res.ok) {
                const t = await res.text();
                throw new Error(`HTTP ${res.status}: ${t}`);
            }
            const rows = await res.json();

            // Render rows
            for (const r of rows) {
                const tr = document.createElement("tr");

                const tdDate = document.createElement("td");
                tdDate.textContent = r.date;

                const tdStart = document.createElement("td");
                tdStart.textContent = r.time_start;

                const tdEnd = document.createElement("td");
                tdEnd.textContent = r.time_end;

                const tdRoom = document.createElement("td");
                tdRoom.textContent = r.room;

                const tdBy = document.createElement("td");
                tdBy.textContent = r.booked_by;

                const tdCost = document.createElement("td");
                tdCost.textContent =
                    r.cost === null || r.cost === undefined || r.cost === ""
                        ? ""
                        : `£${Number(r.cost).toFixed(2)}`;

                tr.append(tdDate, tdStart, tdEnd, tdRoom, tdBy, tdCost);
                tbody.appendChild(tr);
            }

            countSummary.textContent = `${rows.length} booking${rows.length === 1 ? "" : "s"}`;
        } catch (err) {
            console.error(err);
            countSummary.textContent = "Failed to load.";
            alert(err.message);
        }
    });

    // Print
    printBtn.addEventListener("click", () => window.print());
});
