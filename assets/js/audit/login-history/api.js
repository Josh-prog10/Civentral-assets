window.civAudit = window.civAudit || {};
window.civAudit.loginHistory = window.civAudit.loginHistory || {};

window.civAudit.loginHistory.api = {
  allLoginLogs: [],

  async fetchLoginHistory() {
    // Keep skeleton loading rows intact until data loads

    try {
      const basePath = (typeof window.civentralBasePath !== 'undefined') ? window.civentralBasePath : '../../';
      const res = await fetch(basePath + 'api/employee/login-history.php');
      const json = await res.json();
      if (json.status === 'success') {
        if (json.metrics) {
          const sucEl = document.getElementById('successfulCount');
          const failEl = document.getElementById('failedCount');
          const actEl = document.getElementById('activeCount');
          const lockEl = document.getElementById('lockCount');
          if (sucEl) sucEl.innerText = json.metrics.successfulCount || 0;
          if (failEl) failEl.innerText = json.metrics.failedCount || 0;
          if (actEl) actEl.innerText = json.metrics.activeCount || 0;
          if (lockEl) lockEl.innerText = json.metrics.lockCount || 0;
        }
        if (json.departments && window.civAudit.loginHistory.ui) {
          window.civAudit.loginHistory.ui.populateDepartments(json.departments);
        }
        if (json.data) {
          this.allLoginLogs = json.data || [];
          if (window.civAudit.loginHistory.filters) {
            window.civAudit.loginHistory.filters.applyFilters();
          }
        }
      } else {
        if (window.showToast) {
          window.showToast('Notice', json.message || 'Unable to load login history logs.');
        }
        if (window.civAudit.loginHistory.ui) {
          window.civAudit.loginHistory.ui.renderPaginatedTable([], 1);
        }
      }
    } catch (err) {
      console.error('Error fetching login history:', err);
      if (window.showToast) {
        window.showToast('Error', 'Failed to connect to authentication audit server.');
      }
      if (window.civAudit.loginHistory.ui) {
        window.civAudit.loginHistory.ui.renderPaginatedTable([], 1);
      }
    } finally {
      if (typeof hideLoginHistorySkeleton === 'function') {
        hideLoginHistorySkeleton();
      }
    }
  }
};
