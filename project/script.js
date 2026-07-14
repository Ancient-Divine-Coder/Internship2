// ---------- Countdown ----------
const eventDate = new Date("2026-07-20T09:00:00").getTime();

function updateCountdown() {
    const now = Date.now();
    const diff = eventDate - now;

    if (diff <= 0) {
        document.getElementById("countdown").innerHTML = "<div class='num'>Event is LIVE</div>";
        return;
    }

    const d = Math.floor(diff / (1000 * 60 * 60 * 24));
    const h = Math.floor((diff / (1000 * 60 * 60)) % 24);
    const m = Math.floor((diff / (1000 * 60)) % 60);
    const s = Math.floor((diff / 1000) % 60);

    document.getElementById("days").textContent = String(d).padStart(2, "0");
    document.getElementById("hours").textContent = String(h).padStart(2, "0");
    document.getElementById("mins").textContent = String(m).padStart(2, "0");
    document.getElementById("secs").textContent = String(s).padStart(2, "0");
}
setInterval(updateCountdown, 1000);
updateCountdown();

// ---------- Register Button Navigation ----------
const registerButtons = document.querySelectorAll(".register-btn");
registerButtons.forEach(button => {
    button.addEventListener("click", function(e) {
        e.preventDefault();
        const eventId = this.getAttribute("data-event-id");

        // Scroll to the registration form
        const formCard = document.getElementById("regFormCard");
        formCard.scrollIntoView({ behavior: "smooth", block: "start" });

        // Set the event in the select dropdown by matching its value (the DB id)
        const eventSelect = document.getElementById("event_id");
        if (eventSelect) {
            eventSelect.value = eventId;
        }

        // Focus on the form for accessibility
        setTimeout(() => {
            document.getElementById("name").focus();
        }, 500);
    });
});

// ---------- Registration form ----------
const form = document.getElementById("regForm");
const msg = document.getElementById("formMsg");
const btn = document.getElementById("submitBtn");

form.addEventListener("submit", async function (e) {
    e.preventDefault();
    btn.disabled = true;
    msg.textContent = "";
    msg.className = "form-msg";

    const data = new FormData(form);

    try {
        const res = await fetch("register.php", { method: "POST", body: data });
        const result = await res.json();

        msg.textContent = result.message;
        msg.classList.add(result.success ? "ok" : "err");

        if (result.success) {
            form.reset();
            const eventId = data.get("event_id");

            // Update the small summary card gauge text
            const remainingEl = document.querySelector(`.event-card[data-id="${eventId}"] .left`);
            if (remainingEl) remainingEl.textContent = result.remaining + " left";

            // Update the detailed card's available-count
            const detailedEl = document.querySelector(`.available-count[data-id="${eventId}"]`);
            if (detailedEl) detailedEl.textContent = result.remaining > 0 ? result.remaining : "Full";

            // Update Celebrity Night's seats-available span, if this was that event
            const celebEl = document.querySelector(`.seats-available[data-id="${eventId}"]`);
            if (celebEl) celebEl.textContent = result.remaining > 0 ? result.remaining + " Remaining" : "Full";
        }
    } catch (err) {
        msg.textContent = "Something went wrong. Try again.";
        msg.classList.add("err");
    }

    btn.disabled = false;
});