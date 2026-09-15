// Roles Management Bootstrap
// Loads dependencies and initializes event listeners

window.loadCiventralScript('assets/js/rolespermission/shared/toast.js');
window.loadCiventralScript('assets/js/rolespermission/roles-management/api.js');
window.loadCiventralScript('assets/js/rolespermission/roles-management/ui.js');
window.loadCiventralScript('assets/js/rolespermission/roles-management/filters.js');
window.loadCiventralScript('assets/js/rolespermission/roles-management/modal.js');
window.loadCiventralScript('assets/js/rolespermission/roles-management/form.js', () => {

    function initRolesManagementModule() {
        // Fetch initial data
        if (typeof fetchRoles === 'function') {
            fetchRoles();
        }

        // Register Global Event Listeners
        const searchInput = document.getElementById('roleSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                if (typeof filterRoles === 'function') filterRoles();
            });
        }

        const globalAccessFilterSelect = document.getElementById('globalAccessFilterSelect');
        if (globalAccessFilterSelect) {
            globalAccessFilterSelect.addEventListener('change', () => {
                if (typeof filterRoles === 'function') filterRoles();
            });
        }

        const statusFilterSelect = document.getElementById('statusFilterSelect');
        if (statusFilterSelect) {
            statusFilterSelect.addEventListener('change', () => {
                if (typeof filterRoles === 'function') filterRoles();
            });
        }

        const roleNameInput = document.getElementById('roleName');
        if (roleNameInput) {
            roleNameInput.addEventListener('input', (e) => {
                if (typeof autoGenerateRolePrefix === 'function') autoGenerateRolePrefix(e.target.value);
            });
        }

        const rolePrefixInput = document.getElementById('rolePrefix');
        if (rolePrefixInput) {
            rolePrefixInput.addEventListener('input', () => {
                rolePrefixInput.dataset.manual = 'true';
            });
        }

        const roleForm = document.getElementById('roleForm');
        if (roleForm) {
            roleForm.addEventListener('submit', (e) => {
                if (typeof handleSaveRole === 'function') handleSaveRole(e);
            });
        }

        // BACKDROP CLICKS & ESCAPE KEY DISMISS
        document.querySelectorAll('[id$="Modal"]').forEach(modal => {
            modal.addEventListener('mousedown', function(e) {
                if (e.target === this && typeof closeModal === 'function') {
                    closeModal(this.id);
                }
            });
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && typeof closeModal === 'function') {
                closeModal('roleModal');
                closeModal('statusConfirmModal');
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRolesManagementModule);
    } else {
        initRolesManagementModule();
    }
});
