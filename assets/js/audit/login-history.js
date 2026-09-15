if (window.loadCiventralScript) {
  window.loadCiventralScript('assets/js/audit/shared/utils.js')
    .then(() => window.loadCiventralScript('assets/js/audit/shared/toast.js'))
    .then(() => window.loadCiventralScript('assets/js/audit/login-history/api.js'))
    .then(() => window.loadCiventralScript('assets/js/audit/login-history/ui.js'))
    .then(() => window.loadCiventralScript('assets/js/audit/login-history/filters.js'))
    .then(() => window.loadCiventralScript('assets/js/audit/login-history/modal.js'))
    .then(() => window.loadCiventralScript('assets/js/audit/login-history/events.js'))
    .then(() => window.loadCiventralScript('assets/js/audit/login-history/app.js'))
    .catch(err => console.error('Failed to load login-history modules:', err));
} else {
  console.error('Civentral script loader not found.');
}
