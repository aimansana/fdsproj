document.addEventListener("DOMContentLoaded", function () {
    // Confirm before submitting stock allocation
    const form = document.querySelector("form");
    if (form) {
        form.addEventListener("submit", function (event) {
            const inputs = document.querySelectorAll("input[type='number']");
            let total = 0;

            inputs.forEach(input => {
                total += parseInt(input.value) || 0;
            });

            if (total === 0) {
                event.preventDefault();
                alert("Please allocate stock before submitting!");
            } else {
                return confirm("Are you sure you want to allocate stock?");
            }
        });
    }

    // Add table row hover effect
    const rows = document.querySelectorAll("table tr");
    rows.forEach(row => {
        row.addEventListener("mouseover", function () {
            this.style.backgroundColor = "#c5a880";
            this.style.color = "white";
        });
        row.addEventListener("mouseout", function () {
            this.style.backgroundColor = "";
            this.style.color = "";
        });
    });
});
