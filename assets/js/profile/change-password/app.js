// Change Password Sub-module Bootstrap
// Responsible for loading all change-password scripts and initializing them

window.loadCiventralScript('assets/js/profile/shared/toast.js');
window.loadCiventralScript('assets/js/profile/shared/profile-api.js');
window.loadCiventralScript('assets/js/profile/change-password/api.js');
window.loadCiventralScript('assets/js/profile/change-password/validator.js');
window.loadCiventralScript('assets/js/profile/change-password/strength.js');
window.loadCiventralScript('assets/js/profile/change-password/visibility.js', () => {

    function initChangePasswordModule() {
        // Guard clause: Only execute if we are on the change password page
        const saveBtn = document.getElementById('savePasswordBtn');
        if (!saveBtn) return;

        // Initialize API/UI (fetch logged in user for security card)
        if (typeof fetchUserAccountProfile === 'function') {
            fetchUserAccountProfile();
        }

        // Initialize Password Strength
        const newPwInput = document.getElementById('newPassword');
        if (newPwInput && typeof checkPasswordStrength === 'function') {
            newPwInput.oninput = checkPasswordStrength;
        }

        // Initialize Validator (Confirm Match)
        const confirmPwInput = document.getElementById('confirmPassword');
        if (confirmPwInput && typeof checkConfirmMatch === 'function') {
            confirmPwInput.oninput = checkConfirmMatch;
        }

        // Register Button Events
        saveBtn.onclick = (e) => {
            if (e) e.preventDefault();
            if (typeof savePassword === 'function') savePassword();
        };

        // Register Visibility Toggle Events
        const currentEye = document.getElementById('eyeCurrentPassword');
        if (currentEye && currentEye.parentElement) {
            currentEye.parentElement.onclick = () => {
                if (typeof toggleVisibility === 'function') toggleVisibility('currentPassword', 'eyeCurrentPassword');
            };
        }

        const newEye = document.getElementById('eyeNewPassword');
        if (newEye && newEye.parentElement) {
            newEye.parentElement.onclick = () => {
                if (typeof toggleVisibility === 'function') toggleVisibility('newPassword', 'eyeNewPassword');
            };
        }

        const confirmEye = document.getElementById('eyeConfirmPassword');
        if (confirmEye && confirmEye.parentElement) {
            confirmEye.parentElement.onclick = () => {
                if (typeof toggleVisibility === 'function') toggleVisibility('confirmPassword', 'eyeConfirmPassword');
            };
        }
    }

    // Wait for DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initChangePasswordModule);
    } else {
        initChangePasswordModule();
    }
});
