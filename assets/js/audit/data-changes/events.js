window.civAudit = window.civAudit || {};
window.civAudit.dataChanges = window.civAudit.dataChanges || {};

window.civAudit.dataChanges.events = {

  toggleExportDropdown(event) {
    if (event) event.stopPropagation();
    const menu = document.getElementById('exportDropdownMenu');
    if (!menu) return;
    if (menu.classList.contains('hidden')) {
      menu.classList.remove('hidden');
      setTimeout(() => {
        menu.classList.remove('scale-95', 'opacity-0');
        menu.classList.add('scale-100', 'opacity-100');
      }, 10);
    } else {
      this.hideExportDropdown();
    }
  },

  hideExportDropdown() {
    const menu = document.getElementById('exportDropdownMenu');
    if (menu) {
      menu.classList.remove('scale-100', 'opacity-100');
      menu.classList.add('scale-95', 'opacity-0');
      setTimeout(() => menu.classList.add('hidden'), 150);
    }
  },

  _getRows() {
    const ui  = window.civAudit.dataChanges.ui;
    const api = window.civAudit.dataChanges.api;
    const logs = (ui && Array.isArray(ui.currentFilteredLogs) && ui.currentFilteredLogs.length > 0)
      ? ui.currentFilteredLogs
      : (api ? api.auditLogsData : []);

    return logs.map(log => {
      const actor = log.users
        ? (`${log.users.first_name || ''} ${log.users.last_name || ''}`.trim() || log.users.email || 'User')
        : 'System';
      const recordId = log.target_id || log.session_id || '—';
      const modName  = (log.modules && log.modules.module_name) ? log.modules.module_name : (log.target_table || 'System');
      const action   = log.action || 'Data Update';

      let oldVal = '—', newVal = '—';
      try {
        const cd = log.context_json ? (typeof log.context_json === 'string' ? JSON.parse(log.context_json) : log.context_json) : null;
        if (cd && cd.old_data) oldVal = typeof cd.old_data === 'object' ? JSON.stringify(cd.old_data) : cd.old_data;
        if (cd && cd.new_data) newVal = typeof cd.new_data === 'object' ? JSON.stringify(cd.new_data) : cd.new_data;
      } catch(e) {
        oldVal = log.target_table || '—';
        newVal = log.status || 'Success';
      }

      return [
        `#MUT-${log.audit_id || log.id || ''}`,
        log.created_at || '—',
        actor,
        modName,
        recordId,
        action,
        oldVal,
        newVal,
        log.description || '—'
      ];
    });
  },

  exportLogs(type, event) {
    if (event) event.preventDefault();
    this.hideExportDropdown();

    const headers = ['Change ID','Date Time','Actor','Module','Record ID','Action','Old Value','New Value','Description'];
    const rows    = this._getRows();
    const exp     = window.CivAuditExport;
    if (!exp) { alert('Export utility not loaded.'); return; }

    if (type === 'PDF') {
      exp.printTable('Data Mutation & Records Audit', headers, rows);
    } else if (type === 'Excel') {
      exp.downloadExcel('Civentral_Data_Mutations', 'Data Mutation Logs', headers, rows);
    } else {
      exp.downloadCSV('Civentral_Data_Mutations', headers, rows);
    }

    if (window.showToast) {
      window.showToast('Export Success', `Exported ${rows.length} mutation logs as ${type}.`);
    }
  },

  printData() {
    const headers = ['Change ID','Date Time','Actor','Module','Record ID','Action','Old Value','New Value','Description'];
    const rows    = this._getRows();
    if (window.CivAuditExport) {
      window.CivAuditExport.printTable('Data Mutation & Records Audit', headers, rows);
    }
  },

  registerListeners() {
    document.addEventListener('click', (event) => {
      const container = document.getElementById('exportDropdownContainer');
      if (container && !container.contains(event.target)) {
        this.hideExportDropdown();
      }

      const modal = document.getElementById('mutationDetailsModal');
      const card  = document.getElementById('modalCard');
      if (modal && !modal.classList.contains('hidden') && card && !card.contains(event.target)) {
        const row = event.target.closest('#mutationTableBody tr');
        if (!row && window.civAudit.dataChanges.modal) {
          window.civAudit.dataChanges.modal.closeMutationModal();
        }
      }
    });

    window.toggleExportDropdown = (e) => this.toggleExportDropdown(e);
    window.exportLogs            = (type, e) => this.exportLogs(type, e);
    window.printData             = () => this.printData();
    window.openMutationModal     = (row) => window.civAudit.dataChanges.modal.openMutationModal(row);
    window.closeMutationModal    = () => window.civAudit.dataChanges.modal.closeMutationModal();
    window.applyFilters          = () => window.civAudit.dataChanges.filters.applyFilters();
    window.resetFilters          = () => window.civAudit.dataChanges.filters.resetFilters();
    window.refreshLogs           = () => window.civAudit.dataChanges.api.fetchMutationLogs();
  }
};
