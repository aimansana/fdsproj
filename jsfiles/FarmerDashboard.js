document.addEventListener('DOMContentLoaded', function () {
    showSection('profile'); // Default section on load
});

// Function to switch between sections dynamically
function showSection(sectionId) {
    let sections = document.querySelectorAll('.section');
    sections.forEach(section => section.classList.remove('active'));

    document.getElementById(sectionId).classList.add('active');
}
// Function to open the modal
function openModal(requestID = '', amount = '') {
    document.getElementById('paymentModal').style.display = 'block';
    document.getElementById('requestID').value = requestID;
    document.getElementById('amount').value = amount;
}

// Function to close the modal
function closeModal() {
    document.getElementById('paymentModal').style.display = 'none';
}

// Close modal when clicking outside the content
window.onclick = function(event) {
    let modal = document.getElementById('paymentModal');
    if (event.target === modal) {
        closeModal();
    }
}
