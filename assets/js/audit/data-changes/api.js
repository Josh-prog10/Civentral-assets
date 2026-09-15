window.civAudit = window.civAudit || {};
window.civAudit.dataChanges = window.civAudit.dataChanges || {};

window.civAudit.dataChanges.api = {
  auditLogsData: [],
  availableModules: [],

  async fetchMutationLogs() {
    // Keep skeleton loading rows intact until data loads

    try {
      const basePath = (typeof window.civentralBasePath !== 'undefined') ? window.civentralBasePath : '../../';
      const response = await fetch(basePath + 'api/employee/audit-logs.php');
      const result = await response.json();

      if (result.status === 'success' && Array.isArray(result.data)) {
        this.auditLogsData = result.data;
        this.availableModules = result.modules || [];

        if (window.civAudit.dataChanges.ui) {
          window.civAudit.dataChanges.ui.populateModuleDropdown();
        }
        if (window.civAudit.dataChanges.filters) {
          window.civAudit.dataChanges.filters.applyFilters();
        } else if (window.civAudit.dataChanges.ui) {
          window.civAudit.dataChanges.ui.renderMutationLogs(this.auditLogsData);
        }
      } else {
        if (window.showToast) {
          window.showToast('Notice', result.message || 'Unable to load audit logs.');
        }
        if (window.civAudit.dataChanges.ui) {
          window.civAudit.dataChanges.ui.renderMutationLogs([]);
        }
      }
    } catch (err) {
      console.error('Error fetching mutation logs:', err);
      if (window.showToast) {
        window.showToast('Error', 'Failed to connect to audit logs database.');
      }
      if (window.civAudit.dataChanges.ui) {
        window.civAudit.dataChanges.ui.renderMutationLogs([]);
      }
    } finally {
      if (typeof hideDataChangesSkeleton === 'function') {
        hideDataChangesSkeleton();
      }
    }
  }
};
