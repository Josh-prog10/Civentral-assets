// RESOURCE MANAGEMENT BOOTSTRAP

window.loadCiventralScript('assets/js/rolespermission/shared/toast.js');
window.loadCiventralScript('assets/js/rolespermission/resource-management/api.js');
window.loadCiventralScript('assets/js/rolespermission/resource-management/table.js');
window.loadCiventralScript('assets/js/rolespermission/resource-management/filters.js');
window.loadCiventralScript('assets/js/rolespermission/resource-management/modal.js');
window.loadCiventralScript('assets/js/rolespermission/resource-management/events.js', () => {
  function initResourceManagement() {
    if (typeof fetchResources === 'function') {
      fetchResources();
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initResourceManagement);
  } else {
    initResourceManagement();
  }
});
