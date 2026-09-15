// Permissions Bootstrap
// Loads dependencies and initializes event listeners

window.loadCiventralScript('assets/js/rolespermission/shared/toast.js');
window.loadCiventralScript('assets/js/rolespermission/permissions/api.js');
window.loadCiventralScript('assets/js/rolespermission/permissions/ui.js');
window.loadCiventralScript('assets/js/rolespermission/permissions/events.js');
window.loadCiventralScript('assets/js/rolespermission/permissions/state.js', () => {

    function initPermissionsModule() {
        // Fetch initial data
        if (typeof fetchPermissionsData === 'function') {
            fetchPermissionsData();
        }

        // Register Global Event Listeners
        const searchInput = document.getElementById('roleSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                if (typeof renderRoleSelector === 'function') renderRoleSelector(e.target.value);
            });
        }

        const moduleSearchInput = document.getElementById('moduleSearchInput');
        if (moduleSearchInput) {
            moduleSearchInput.addEventListener('input', () => {
                if (typeof renderAccordions === 'function') renderAccordions();
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPermissionsModule);
    } else {
        initPermissionsModule();
    }
});
