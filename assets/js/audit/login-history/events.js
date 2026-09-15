window.civAudit = window.civAudit || {};
window.civAudit.loginHistory = window.civAudit.loginHistory || {};

window.civAudit.loginHistory.events = {

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
    const ui  = window.civAudit.loginHistory.ui;
    const api = window.civAudit.loginHistory.api;

    const logs = (ui && ui.filteredLoginLogs && ui.filteredLoginLogs.length > 0)
      ? ui.filteredLoginLogs
      : (api && api.allLoginLogs ? api.allLoginLogs : []);

    return logs.map(log => {
      const user    = log.users    || {};
      const roleObj = user.roles   || {};
      const posObj  = user.positions || {};
      const deptObj = posObj.departments || {};

      const actor      = user.first_name ? `${user.first_name} ${user.last_name}`.trim() : (user.email || 'Unknown');
      const email      = user.email || 'N/A';
      const role       = roleObj.role_name || 'Staff';
      const department = deptObj.department_name || '—';
      const ip         = log.ip_address || '—';
      const device     = log.browser || '—';
      const failure    = log.failure_reason || '—';

      let status = 'Successful Login';
      if (log.login_status === 'Failed') {
        status = (user.status === 'Locked' || (log.failure_reason && log.failure_reason.toLowerCase().includes('locked')))
          ? 'Account Locked' : 'Failed Login';
      } else if (log.logout_time) {
        status = 'Logged Out';
      }

      // Format timestamps nicely
      const fmt = (ts) => {
        if (!ts) return '—';
        try {
          return new Date(ts.includes('T') ? ts : ts.replace(' ','T') + '+08:00')
            .toLocaleString('en-US', { timeZone: 'Asia/Manila', dateStyle: 'medium', timeStyle: 'short' });
        } catch(e) { return ts; }
      };

      return [
        `#LOG-${log.login_id}`,
        fmt(log.login_time),
        fmt(log.logout_time),
        actor,
        email,
        role,
        department,
        status,
        ip,
        device,
        failure
      ];
    });
  },

  exportLogs(type, event) {
    if (event) event.preventDefault();
    this.hideExportDropdown();

    const headers = ['Login ID','Login Time','Logout Time','Actor','Email','Role','Department','Status','IP Address','Browser/Device','Failure Reason'];
    const rows    = this._getRows();
    const exp     = window.CivAuditExport;
    if (!exp) { alert('Export utility not loaded.'); return; }

    if (type === 'PDF') {
      exp.printTable('Login History & Authentication Audit', headers, rows);
    } else if (type === 'Excel') {
      exp.downloadExcel('Civentral_Login_History', 'Login History Audit', headers, rows);
    } else {
      exp.downloadCSV('Civentral_Login_History', headers, rows);
    }

    if (window.showToast) {
      window.showToast('Export Success', `Exported ${rows.length} login records as ${type}.`);
    }
  },

  printData() {
    const headers = ['Login ID','Login Time','Logout Time','Actor','Email','Role','Department','Status','IP Address','Browser/Device','Failure Reason'];
    const rows    = this._getRows();
    if (window.CivAuditExport) {
      window.CivAuditExport.printTable('Login History & Authentication Audit', headers, rows);
    }
  },

  bindEvents() {
    document.addEventListener('click', (event) => {
      const container = document.getElementById('exportDropdownContainer');
      if (container && !container.contains(event.target)) this.hideExportDropdown();

      const modal = document.getElementById('sessionInspectorModal');
      const card  = document.getElementById('modalCard');
      if (modal && !modal.classList.contains('hidden') && card && !card.contains(event.target)) {
        const row = event.target.closest('#loginTableBody tr');
        if (!row && window.civAudit.loginHistory.modal) {
          window.civAudit.loginHistory.modal.closeSessionModal();
        }
      }
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && window.civAudit.loginHistory.modal) {
        window.civAudit.loginHistory.modal.closeSessionModal();
      }
    });

    window.toggleExportDropdown = (e) => this.toggleExportDropdown(e);
    window.exportLogs            = (type, e) => this.exportLogs(type, e);
    window.printData             = () => this.printData();
    window.refreshLogs           = () => window.civAudit.loginHistory.api.fetchLoginHistory();
  }
};
