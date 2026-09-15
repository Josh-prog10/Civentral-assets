window.civAudit = window.civAudit || {};
window.civAudit.dataChanges = window.civAudit.dataChanges || {};

window.civAudit.dataChanges.filters = {
  applyFilters() {
    const filterSearch = document.getElementById('filterSearch');
    const filterDate = document.getElementById('filterDate');
    const filterModule = document.getElementById('filterModule');

    const searchVal = filterSearch ? filterSearch.value.toLowerCase().trim() : '';
    const dateVal = filterDate ? filterDate.value : '';
    const moduleVal = filterModule ? filterModule.value : 'All';

    const api = window.civAudit.dataChanges.api;

    const filtered = api.auditLogsData.filter(log => {
      const action = (log.action || '').toLowerCase();

      // 1. Exclude non-CRUD authentication, session & view events from Data Changes view
      const isAuthOrViewEvent = 
        action.includes('login') ||
        action.includes('logout') ||
        action.includes('2fa') ||
        action.includes('otp') ||
        action.includes('session') ||
        action.includes('timeout') ||
        action === 'view' ||
        action.startsWith('view ');

      if (isAuthOrViewEvent) {
        return false;
      }

      // 2. Exclude entries where no actual data mutation occurred
      if (log.context_json) {
        try {
          const parsed = typeof log.context_json === 'string' ? JSON.parse(log.context_json) : log.context_json;
          if (parsed.no_change === true || (parsed.changes && Object.keys(parsed.changes).length === 0)) {
            return false;
          }
        } catch (e) {}
      }

      let actorName = 'System';
      if (log.users) {
        actorName = `${log.users.first_name || ''} ${log.users.last_name || ''}`.trim() || log.users.email || 'User';
      }
      
      const recordId = log.target_id || log.session_id || '';
      const desc = log.description || '';
      const rawDate = log.created_at || '';
      
      // Parse date string in Asia/Manila context for filter matching
      let isoDate = rawDate.split(' ')[0] || '';
      if (rawDate) {
        const dt = new Date(rawDate.includes('T') ? rawDate : rawDate.replace(' ', 'T') + '+08:00');
        if (!isNaN(dt.getTime())) {
          isoDate = dt.toLocaleDateString('en-CA', { timeZone: 'Asia/Manila' }); // YYYY-MM-DD
        }
      }

      const modName = (log.modules && log.modules.module_name) ? log.modules.module_name : (log.target_table || 'System');

      const matchSearch = !searchVal || 
                          actorName.toLowerCase().includes(searchVal) || 
                          recordId.toString().toLowerCase().includes(searchVal) || 
                          action.includes(searchVal) || 
                          desc.toLowerCase().includes(searchVal);
      const matchDate = !dateVal || isoDate === dateVal;
      const matchModule = moduleVal === 'All' || modName === moduleVal;

      return matchSearch && matchDate && matchModule;
    });

    if (window.civAudit.dataChanges.ui) {
      window.civAudit.dataChanges.ui.renderMutationLogs(filtered, 1);
    }
  },

  resetFilters() {
    const filterSearch = document.getElementById('filterSearch');
    const filterDate = document.getElementById('filterDate');
    const filterModule = document.getElementById('filterModule');

    if (filterSearch) filterSearch.value = '';
    if (filterDate) filterDate.value = '';
    if (filterModule) filterModule.value = 'All';

    this.applyFilters();
    if (window.showToast) {
      window.showToast("Filters Reset", "Data mutation search parameters cleared.");
    }
  }
};

window.applyFilters = function() {
  if (window.civAudit && window.civAudit.dataChanges && window.civAudit.dataChanges.filters) {
    window.civAudit.dataChanges.filters.applyFilters();
  }
};

window.resetFilters = function() {
  if (window.civAudit && window.civAudit.dataChanges && window.civAudit.dataChanges.filters) {
    window.civAudit.dataChanges.filters.resetFilters();
  }
};
