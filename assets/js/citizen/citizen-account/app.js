// Citizen Account Bootstrap Module

window.loadCiventralScript('assets/js/citizen/shared/toast.js');
window.loadCiventralScript('assets/js/citizen/citizen-account/api.js');
window.loadCiventralScript('assets/js/citizen/citizen-account/ui.js');
window.loadCiventralScript('assets/js/citizen/citizen-account/filters.js');
window.loadCiventralScript('assets/js/citizen/citizen-account/modal.js');
window.loadCiventralScript('assets/js/citizen/citizen-account/events.js', () => {

    function initCitizenAccountModule() {
        if (typeof fetchControlCitizens === 'function') {
            fetchControlCitizens();
        } else if (typeof renderControlTable === 'function') {
            renderControlTable();
        }
        if (typeof updateControlStats === 'function') {
            updateControlStats();
        }

        const searchInput = document.getElementById('ctrlSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                if (typeof filterControlTable === 'function') filterControlTable();
            });
        }

        const statusFilter = document.getElementById('ctrlStatusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', () => {
                if (typeof filterControlTable === 'function') filterControlTable();
            });
        }

        const flaggedFilter = document.getElementById('flaggedFilter');
        if (flaggedFilter) {
            flaggedFilter.addEventListener('change', () => {
                if (typeof filterControlTable === 'function') filterControlTable();
            });
        }

        const stateForm = document.getElementById('stateForm');
        if (stateForm) {
            stateForm.addEventListener('submit', (e) => {
                if (typeof handleConfirmStateChange === 'function') handleConfirmStateChange(e);
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
        document.addEventListener('DOMContentLoaded', initCitizenAccountModule);
    } else {
        initCitizenAccountModule();
    }
});
