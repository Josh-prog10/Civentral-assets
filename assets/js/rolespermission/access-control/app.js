// Access Control Bootstrap
// Loads dependencies and initializes event listeners

window.loadCiventralScript('assets/js/rolespermission/shared/toast.js');
window.loadCiventralScript('assets/js/rolespermission/access-control/api.js');
window.loadCiventralScript('assets/js/rolespermission/access-control/ui.js');
window.loadCiventralScript('assets/js/rolespermission/access-control/events.js', () => {

    function initAccessControlModule() {
        // Fetch initial data
        if (typeof fetchAccessControlData === 'function') {
            fetchAccessControlData();
        }

        // Register Global Event Listeners
        const switchGlobal = document.getElementById('toggleGlobalAccess');
        if (switchGlobal) {
            switchGlobal.addEventListener('change', (e) => {
                if (typeof handleGlobalToggle === 'function') handleGlobalToggle(e.target);
            });
        }

        const switchLockin = document.getElementById('toggleDeptLockin');
        if (switchLockin) {
            switchLockin.addEventListener('change', (e) => {
                if (typeof handleLockinToggle === 'function') handleLockinToggle(e.target);
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAccessControlModule);
    } else {
        initAccessControlModule();
    }
});
