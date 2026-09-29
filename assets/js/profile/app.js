
(function() {
    
    if (document.getElementById('saveSettingsBtn')) {
        window.loadCiventralScript('assets/js/profile/settings/app.js');
    } 
    else if (document.getElementById('savePasswordBtn')) {
        window.loadCiventralScript('assets/js/profile/change-password/app.js');
    } 
    else if (document.getElementById('profileEmployeeId') || document.getElementById('profileLastLogin')) {
        window.loadCiventralScript('assets/js/profile/profile.js');
    }
})();
