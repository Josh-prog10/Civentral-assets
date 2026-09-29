if (window.loadCiventralScript) {
  window.loadCiventralScript('assets/js/audit/shared/utils.js')
    .then(() => window.loadCiventralScript('assets/js/audit/shared/toast.js'))
    .then(() => window.loadCiventralScript('assets/js/audit/user-activities/api.js'))
    .then(() => window.loadCiventralScript('assets/js/audit/user-activities/ui.js'))
    .then(() => window.loadCiventralScript('assets/js/audit/user-activities/filters.js'))
    .then(() => window.loadCiventralScript('assets/js/audit/user-activities/modal.js'))
    .then(() => window.loadCiventralScript('assets/js/audit/user-activities/events.js'))
    .then(() => window.loadCiventralScript('assets/js/audit/user-activities/app.js'))
    .catch(err => console.error('Failed to load user-activities modules:', err));
} else {
  console.error('Civentral script loader not found.');
}
