// Citizen Directory Bootstrap Module

window.loadCiventralScript('assets/js/citizen/shared/toast.js');
window.loadCiventralScript('assets/js/citizen/citizen-directory/api.js');
window.loadCiventralScript('assets/js/citizen/citizen-directory/ui.js');
window.loadCiventralScript('assets/js/citizen/citizen-directory/filters.js');
window.loadCiventralScript('assets/js/citizen/citizen-directory/modal.js');
window.loadCiventralScript('assets/js/citizen/citizen-directory/events.js', () => {

    function initCitizenDirectoryModule() {
        if (typeof fetchCitizens === 'function') {
            fetchCitizens(); // This will fetch data and then call renderCitizens
        } else if (typeof renderCitizens === 'function') {
            renderCitizens();
        }

        const citizenSearchInput = document.getElementById('citizenSearch');
        if (citizenSearchInput) {
            citizenSearchInput.addEventListener('input', () => {
                if (typeof filterCitizenTable === 'function') filterCitizenTable();
            });
        }

        const statusFilterSelect = document.getElementById('statusFilter');
        if (statusFilterSelect) {
            statusFilterSelect.addEventListener('change', () => {
                if (typeof filterCitizenTable === 'function') filterCitizenTable();
            });
        }

        const barangayFilterSelect = document.getElementById('barangayFilter');
        if (barangayFilterSelect) {
            barangayFilterSelect.addEventListener('change', () => {
                if (typeof filterCitizenTable === 'function') filterCitizenTable();
            });
        }

        const dateFilterInput = document.getElementById('dateFilter');
        if (dateFilterInput) {
            dateFilterInput.addEventListener('change', () => {
                if (typeof filterCitizenTable === 'function') filterCitizenTable();
            });
        }

        const lockForm = document.getElementById('lockForm');
        if (lockForm) {
            lockForm.addEventListener('submit', (e) => {
                if (typeof handleExecuteLock === 'function') handleExecuteLock(e);
            });
        }

        // Backdrop clicks close
        document.querySelectorAll('[id$="Modal"]').forEach(modal => {
            modal.addEventListener('mousedown', function(e) {
                if (e.target === this && typeof closeModal === 'function') {
                    closeModal(this.id);
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCitizenDirectoryModule);
    } else {
        initCitizenDirectoryModule();
    }
});
