window.civAudit = window.civAudit || {};
window.civAudit.loginHistory = window.civAudit.loginHistory || {};

function initLoginHistoryModule() {
  if (window.civAudit && window.civAudit.loginHistory) {
    if (window.civAudit.loginHistory.events) {
      window.civAudit.loginHistory.events.bindEvents();
    }
    if (window.civAudit.loginHistory.api) {
      window.civAudit.loginHistory.api.fetchLoginHistory();
    }
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initLoginHistoryModule);
} else {
  initLoginHistoryModule();
}
