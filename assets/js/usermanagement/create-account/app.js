// Create Account Bootstrap Module

window.loadCiventralScript('assets/js/usermanagement/shared/toast.js');
window.loadCiventralScript('assets/js/usermanagement/create-account/api.js');
window.loadCiventralScript('assets/js/usermanagement/create-account/ui.js');
window.loadCiventralScript('assets/js/usermanagement/create-account/form.js');
window.loadCiventralScript('assets/js/usermanagement/create-account/modal.js', () => {

    function initCreateAccountModule() {
        if (typeof renderSkeletonOptions === 'function') {
            renderSkeletonOptions();
        }
        if (typeof fetchFormData === 'function') {
            fetchFormData();
        }

        // Attach completion check listeners to all input fields
        const fields = ['firstName', 'lastName', 'email', 'mobileNumber', 'department', 'position', 'role'];
        fields.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', () => {
                    if (typeof checkFormCompletion === 'function') checkFormCompletion();
                });
                el.addEventListener('change', () => {
                    if (typeof checkFormCompletion === 'function') checkFormCompletion();
                });
            }
        });

        const roleSelect = document.getElementById('role');
        if (roleSelect) {
            roleSelect.addEventListener('change', () => {
                if (typeof handleRoleChange === 'function') handleRoleChange();
            });
        }

        const btnGenerateEmpId = document.getElementById('btnGenerateEmpId');
        if (btnGenerateEmpId) {
            btnGenerateEmpId.addEventListener('click', () => {
                if (typeof autoGenerateEmpId === 'function') autoGenerateEmpId();
            });
        }

        const createUserForm = document.getElementById('createUserForm');
        if (createUserForm) {
            createUserForm.addEventListener('submit', (e) => {
                if (typeof handleCreateUser === 'function') handleCreateUser(e);
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCreateAccountModule);
    } else {
        initCreateAccountModule();
    }
});
