// Settings Sub-module Bootstrap
// Responsible for loading all settings-related scripts and initializing them

window.loadCiventralScript('assets/js/profile/shared/toast.js');
window.loadCiventralScript('assets/js/profile/shared/profile-api.js');
window.loadCiventralScript('assets/js/profile/settings/api.js');
window.loadCiventralScript('assets/js/profile/settings/ui.js');
window.loadCiventralScript('assets/js/profile/settings/avatar.js');
window.loadCiventralScript('assets/js/profile/settings/theme.js', () => {

    function initSettingsModule() {
        // Guard clause: Only execute if we are on the settings page
        if (!document.getElementById('saveSettingsBtn')) return;

        // Initialize UI Module
        if (typeof fetchProfileData === 'function') fetchProfileData();

        // Initialize Theme Module
        if (typeof syncThemeCardsUI === 'function') syncThemeCardsUI();

        // Register Events
        window.addEventListener('civentralThemeChanged', syncThemeCardsUI);
        
        // Note: Avatar module relies on HTML events, no programmatic init needed
    }

    // Wait for DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSettingsModule);
    } else {
        initSettingsModule();
    }
});
