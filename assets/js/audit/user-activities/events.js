window.civAudit = window.civAudit || {};
window.civAudit.userActivities = window.civAudit.userActivities || {};

window.civAudit.userActivities.events = {

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
    const ui  = window.civAudit.userActivities.ui;
    const api = window.civAudit.userActivities.api;

    const filterDate   = document.getElementById('filterDate');
    const filterSearch = document.getElementById('filterSearch');
    const filterDept   = document.getElementById('filterDepartment');
    const filterModule = document.getElementById('filterModule');
    const filterAction = document.getElementById('filterAction');
    const filterStatus = document.getElementById('filterStatus');

    const dateVal   = filterDate   ? filterDate.value : '';
    const searchVal = filterSearch ? filterSearch.value.toLowerCase().trim() : '';
    const deptVal   = filterDept   ? filterDept.value : 'All';
    const moduleVal = filterModule ? filterModule.value : 'All';
    const actionVal = filterAction ? filterAction.value : 'All';
    const statusVal = filterStatus ? filterStatus.value : 'All';

    const logs = (ui && Array.isArray(ui.currentFilteredLogs) && ui.currentFilteredLogs.length > 0)
      ? ui.currentFilteredLogs
      : (api ? api.auditLogsData.filter(item => {
          const matchDate   = !dateVal   || item.date === dateVal;
          const matchSearch = !searchVal || item.actor.toLowerCase().includes(searchVal) || item.id.toLowerCase().includes(searchVal) || item.desc.toLowerCase().includes(searchVal);
          const matchDept   = deptVal   === 'All' || item.department === deptVal;
          const matchModule = moduleVal === 'All' || item.module     === moduleVal;
          const matchAction = actionVal === 'All' || item.action     === actionVal;
          const matchStatus = statusVal === 'All' || item.status     === statusVal;
          return matchDate && matchSearch && matchDept && matchModule && matchAction && matchStatus;
        }) : []);

    return logs.map(log => [
      log.id         || '—',
      log.dateTimeStr|| '—',
      log.actor      || '—',
      log.role       || '—',
      log.department || '—',
      log.module     || '—',
      log.action     || '—',
      log.desc       || '—',
      log.ip         || '—',
      log.status     || '—'
    ]);
  },

  exportLogs(type, event) {
    if (event) event.preventDefault();
    this.hideExportDropdown();

    const headers = ['Activity ID','Date Time','Actor','Role','Department','Module','Action','Description','IP Address','Status'];
    const rows    = this._getRows();
    const exp     = window.CivAuditExport;
    if (!exp) { alert('Export utility not loaded.'); return; }

    if (type === 'PDF') {
      exp.printTable('User Activities Audit System', headers, rows);
    } else if (type === 'Excel') {
      exp.downloadExcel('Civentral_Audit_Logs', 'User Activities Audit', headers, rows);
    } else {
      exp.downloadCSV('Civentral_Audit_Logs', headers, rows);
    }

    if (window.showToast) {
      window.showToast('Export Success', `Exported ${rows.length} audit logs as ${type}.`);
    }
  },

  printData() {
    const headers = ['Activity ID','Date Time','Actor','Role','Department','Module','Action','Description','IP Address','Status'];
    const rows    = this._getRows();
    if (window.CivAuditExport) {
      window.CivAuditExport.printTable('User Activities Audit System', headers, rows);
    }
  },

  copyModalPayload() {
    const payloadText = document.getElementById('modalPayloadText');
    if (!payloadText) return;
    navigator.clipboard.writeText(payloadText.textContent).then(() => {
      if (window.showToast) window.showToast('Payload Copied', 'JSON payload details copied to clipboard.');
    }).catch(err => console.error('Copy failed:', err));
  },

  registerListeners() {
    document.addEventListener('click', (event) => {
      const container = document.getElementById('exportDropdownContainer');
      if (container && !container.contains(event.target)) this.hideExportDropdown();

      const modal = document.getElementById('logDetailsModal');
      const card  = document.getElementById('modalCard');
      if (modal && !modal.classList.contains('hidden') && card && !card.contains(event.target)) {
        const row = event.target.closest('#auditTableBody tr');
        if (!row && window.civAudit.userActivities.modal) {
          window.civAudit.userActivities.modal.closeLogDetailsModal();
        }
      }
    });

    window.toggleExportDropdown  = (e) => this.toggleExportDropdown(e);
    window.exportLogs             = (type, e) => this.exportLogs(type, e);
    window.mockExport             = (type, e) => this.exportLogs(type, e);
    window.printData              = () => this.printData();
    window.copyModalPayload       = () => this.copyModalPayload();
    window.openLogDetailsModal    = (row) => window.civAudit.userActivities.modal.openLogDetailsModal(row);
    window.closeLogDetailsModal   = () => window.civAudit.userActivities.modal.closeLogDetailsModal();
    window.applyFilters           = () => window.civAudit.userActivities.filters.applyFilters();
    window.resetFilters           = () => window.civAudit.userActivities.filters.resetFilters();
    window.refreshLogs            = () => window.civAudit.userActivities.api.fetchAuditLogs();
  }
};
