// Department Module Bootstrap
window.loadCiventralScript('assets/js/department/utils.js');
window.loadCiventralScript('assets/js/department/api.js');
window.loadCiventralScript('assets/js/department/table.js');
window.loadCiventralScript('assets/js/department/modal.js');
window.loadCiventralScript('assets/js/department/form.js');
window.loadCiventralScript('assets/js/department/filters.js');
window.loadCiventralScript('assets/js/department/events.js', () => {
    // Initialization executes after all department scripts have loaded
    // Only execute if we are actually on a page with the department table
    if (document.getElementById('deptsTableBody') && typeof fetchDepartments === 'function') {
        fetchDepartments();
    }
});
