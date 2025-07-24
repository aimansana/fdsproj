document.addEventListener("DOMContentLoaded", () => {
    // ----------------- Tab Switching -----------------
    const tabs = document.querySelectorAll('a[data-target]');
    const sections = document.querySelectorAll('section');

    function switchTab(targetId) {
        sections.forEach(section => {
            section.style.display = section.id === targetId ? 'block' : 'none';
        });
        tabs.forEach(tab => tab.classList.toggle('active', tab.getAttribute('data-target') === targetId));
    }

    // Handle tab switching and URL hash update
    tabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = tab.getAttribute('data-target');
            switchTab(targetId);
            window.location.hash = targetId; // Update URL
        });
    });

    // Set default tab based on URL hash or first tab
    const initialTab = window.location.hash.substring(1) || tabs[0].getAttribute('data-target');
    switchTab(initialTab);

    // Handle browser back/forward button navigation
    window.addEventListener('hashchange', () => {
        const targetId = window.location.hash.substring(1);
        if (targetId) {
            switchTab(targetId);
        }
    });
});
