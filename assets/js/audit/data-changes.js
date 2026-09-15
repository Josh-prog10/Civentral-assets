if (window.loadCiventralScript) {
  window.loadCiventralScript('assets/js/audit/shared/utils.js')
    .then(() => window.loadCiventralScript('assets/js/audit/shared/toast.js'))
    .then(() => window.loadCiventralScript('assets/js/audit/data-changes/api.js'))
    .then(() => window.loadCiventralScript('assets/js/audit/data-changes/ui.js'))
    .then(() => window.loadCiventralScript('assets/js/audit/data-changes/filters.js'))
    .then(() => window.loadCiventralScript('assets/js/audit/data-changes/modal.js'))
    .then(() => window.loadCiventralScript('assets/js/audit/data-changes/events.js'))
    .then(() => window.loadCiventralScript('assets/js/audit/data-changes/app.js'))
    .catch(err => console.error('Failed to load data-changes modules:', err));
} else {
  console.error('Civentral script loader not found.');
}
