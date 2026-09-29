function initUserActivitiesModule() {
  if (window.civAudit && window.civAudit.userActivities) {
    if (window.civAudit.userActivities.events) {
      window.civAudit.userActivities.events.registerListeners();
    }
    if (window.civAudit.userActivities.api) {
      window.civAudit.userActivities.api.fetchAuditLogs();
    }
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initUserActivitiesModule);
} else {
  initUserActivitiesModule();
}
