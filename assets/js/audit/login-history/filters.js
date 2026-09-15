window.civAudit = window.civAudit || {};
window.civAudit.loginHistory = window.civAudit.loginHistory || {};

window.civAudit.loginHistory.filters = {
  applyFilters() {
    const filterSearch = document.getElementById('filterSearch');
    const filterDate = document.getElementById('filterDate');
    const filterStatus = document.getElementById('filterStatus');
    const filterDepartment = document.getElementById('filterDepartment');

    const searchVal = filterSearch ? filterSearch.value.toLowerCase().trim() : '';
    const dateVal = filterDate ? filterDate.value : '';
    const statusVal = filterStatus ? filterStatus.value : 'All';
    const deptVal = filterDepartment ? filterDepartment.value : 'All';

    const api = window.civAudit.loginHistory.api;
    const logs = api.allLoginLogs || [];

    const filtered = logs.filter(log => {
      const user = log.users || {};
      const roleObj = user.roles || {};
      const posObj = user.positions || {};
      const deptObj = posObj.departments || {};

      const actor = (user.first_name ? `${user.first_name} ${user.last_name}` : (user.email || '')).toLowerCase();
      const email = (user.email || '').toLowerCase();
      const dateValLog = log.login_time ? log.login_time.split('T')[0].split(' ')[0] : '';
      const department = deptObj.department_name || 'Central IT';

      let statusText = 'Successful Login';
      if (log.login_status === 'Failed') {
        if (user.status === 'Locked' || (log.failure_reason && log.failure_reason.toLowerCase().includes('locked'))) {
          statusText = 'Account Locked';
        } else {
          statusText = 'Failed Login';
        }
      } else if (log.logout_time) {
        statusText = 'Logout';
      }

      const matchSearch = !searchVal || actor.includes(searchVal) || email.includes(searchVal);
      const matchDate = !dateVal || dateValLog === dateVal;
      const matchStatus = (statusVal === 'All' || statusText === statusVal);
      const matchDept = (deptVal === 'All' || department === deptVal);

      return matchSearch && matchDate && matchStatus && matchDept;
    });

    if (window.civAudit.loginHistory.ui) {
      window.civAudit.loginHistory.ui.renderPaginatedTable(filtered, 1);
    }
  },

  resetFilters() {
    const filterSearch = document.getElementById('filterSearch');
    const filterDate = document.getElementById('filterDate');
    const filterStatus = document.getElementById('filterStatus');
    const filterDepartment = document.getElementById('filterDepartment');

    if (filterSearch) filterSearch.value = '';
    if (filterDate) filterDate.value = '';
    if (filterStatus) filterStatus.value = 'All';
    if (filterDepartment) filterDepartment.value = 'All';

    this.applyFilters();
    if (window.showToast) {
      window.showToast("Filters Reset", "All authentication filter inputs have been returned to default.");
    }
  }
};

window.applyFilters = function() {
  if (window.civAudit.loginHistory.filters) {
    window.civAudit.loginHistory.filters.applyFilters();
  }
};

window.resetFilters = function() {
  if (window.civAudit.loginHistory.filters) {
    window.civAudit.loginHistory.filters.resetFilters();
  }
};
