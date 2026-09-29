// Module Management Bootstrap

window.loadCiventralScript('assets/js/rolespermission/shared/toast.js');
window.loadCiventralScript('assets/js/rolespermission/module-management/api.js');
window.loadCiventralScript('assets/js/rolespermission/module-management/table.js');
window.loadCiventralScript('assets/js/rolespermission/module-management/filters.js');
window.loadCiventralScript('assets/js/rolespermission/module-management/modal.js');
window.loadCiventralScript('assets/js/rolespermission/module-management/events.js', () => {
  function initModuleManagement() {
    if (typeof fetchModules === 'function') {
      fetchModules();
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initModuleManagement);
  } else {
    initModuleManagement();
  }
});
