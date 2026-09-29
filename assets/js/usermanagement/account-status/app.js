// Account Status Bootstrap Module

window.loadCiventralScript('assets/js/usermanagement/shared/toast.js');
window.loadCiventralScript('assets/js/usermanagement/account-status/api.js');
window.loadCiventralScript('assets/js/usermanagement/account-status/ui.js');
window.loadCiventralScript('assets/js/usermanagement/account-status/filters.js');
window.loadCiventralScript('assets/js/usermanagement/account-status/modal.js');
window.loadCiventralScript('assets/js/usermanagement/account-status/events.js', () => {

    function initAccountStatusModule() {
        if (typeof renderSkeletonTable === 'function') {
            renderSkeletonTable();
        }
        if (typeof fetchAccountStatusUsers === 'function') {
            fetchAccountStatusUsers();
        }

        // Register Global Event Listeners
        const searchInput = document.getElementById('statusSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                if (typeof filterAndSearch === 'function') filterAndSearch();
            });
        }

        // Backdrop click for modals
        document.querySelectorAll('[id$="Modal"]').forEach(modal => {
            modal.addEventListener('mousedown', function(e) {
                if (e.target === this && typeof closeModal === 'function') {
                    closeModal(this.id);
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAccountStatusModule);
    } else {
        initAccountStatusModule();
    }
});
