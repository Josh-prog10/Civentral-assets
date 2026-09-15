window.civAudit = window.civAudit || {};
window.civAudit.userActivities = window.civAudit.userActivities || {};

window.civAudit.userActivities.filters = {
  applyFilters() {
    const filterDate = document.getElementById('filterDate');
    const filterSearch = document.getElementById('filterSearch');
    const filterDepartment = document.getElementById('filterDepartment');
    const filterModule = document.getElementById('filterModule');
    const filterAction = document.getElementById('filterAction');
    const filterStatus = document.getElementById('filterStatus');

    const dateVal = filterDate ? filterDate.value : '';
    const searchVal = filterSearch ? filterSearch.value.toLowerCase().trim() : '';
    const deptVal = filterDepartment ? filterDepartment.value : 'All';
    const moduleVal = filterModule ? filterModule.value : 'All';
    const actionVal = filterAction ? filterAction.value : 'All';
    const statusVal = filterStatus ? filterStatus.value : 'All';

    const api = window.civAudit.userActivities.api;

    const filtered = api.auditLogsData.filter(item => {
      const matchDate = !dateVal || item.date === dateVal;
      const matchSearch = !searchVal || 
                          item.actor.toLowerCase().includes(searchVal) || 
                          item.id.toLowerCase().includes(searchVal) || 
                          item.desc.toLowerCase().includes(searchVal) ||
                          item.ip.toLowerCase().includes(searchVal);
      const matchDept = (deptVal === 'All' || item.department === deptVal);
      const matchModule = (moduleVal === 'All' || item.module === moduleVal);
      const matchAction = (actionVal === 'All' || item.action === actionVal);
      const matchStatus = (statusVal === 'All' || item.status === statusVal);

      return matchDate && matchSearch && matchDept && matchModule && matchAction && matchStatus;
    });

    if (window.civAudit.userActivities.ui) {
      window.civAudit.userActivities.ui.renderTableRows(filtered, 1);
    }
  },

  resetFilters() {
    const filterDate = document.getElementById('filterDate');
    const filterSearch = document.getElementById('filterSearch');
    const filterDepartment = document.getElementById('filterDepartment');
    const filterModule = document.getElementById('filterModule');
    const filterAction = document.getElementById('filterAction');
    const filterStatus = document.getElementById('filterStatus');

    if (filterDate) filterDate.value = '';
    if (filterSearch) filterSearch.value = '';
    if (filterDepartment) filterDepartment.value = 'All';
    if (filterModule) filterModule.value = 'All';
    if (filterAction) filterAction.value = 'All';
    if (filterStatus) filterStatus.value = 'All';
    
    this.applyFilters();
    
    if (window.showToast) {
      window.showToast("Filters Reset", "All filter inputs have been returned to their default values.");
    }
  }
};

window.applyFilters = function() {
  if (window.civAudit && window.civAudit.userActivities && window.civAudit.userActivities.filters) {
    window.civAudit.userActivities.filters.applyFilters();
  }
};

window.resetFilters = function() {
  if (window.civAudit && window.civAudit.userActivities && window.civAudit.userActivities.filters) {
    window.civAudit.userActivities.filters.resetFilters();
  }
};
