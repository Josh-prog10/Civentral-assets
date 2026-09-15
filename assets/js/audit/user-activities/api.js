window.civAudit = window.civAudit || {};
window.civAudit.userActivities = window.civAudit.userActivities || {};

window.civAudit.userActivities.api = {
  auditLogsData: [],
  availableDepartments: [],
  availableModules: [],

  async fetchAuditLogs() {
    // Keep skeleton loading rows intact until data loads

    try {
      const basePath = (typeof window.civentralBasePath !== 'undefined') ? window.civentralBasePath : '../../';
      const response = await fetch(basePath + 'api/employee/audit-logs.php');
      const result = await response.json();

      if (result.status === 'success' && Array.isArray(result.data)) {
        this.availableDepartments = result.departments || [];
        this.availableModules = result.modules || [];
        
        if (window.civAudit.userActivities.ui) {
          window.civAudit.userActivities.ui.populateFilterDropdowns();
        }

        this.auditLogsData = result.data.map(log => {
          const userObj = log.users || {};
          const firstName = userObj.first_name || '';
          const lastName = userObj.last_name || '';
          const actorName = `${firstName} ${lastName}`.trim() || userObj.email || 'System / Guest';
          
          const roleObj = userObj.roles || {};
          const roleName = roleObj.role_name || 'Staff';
          const rolePrefix = roleObj.role_prefix || 'STF';

          let roleColor = 'gray';
          if (roleName.includes('Super') || rolePrefix === 'SADM') {
            roleColor = 'purple';
          } else if (roleName.includes('Admin') || rolePrefix.includes('ADM')) {
            roleColor = 'blue';
          }

          const deptName = log.departments?.department_name || 'General Services';
          const moduleName = log.modules?.module_name || 'System Portal';

          const rawDate = log.created_at || '';
          let datePart = 'N/A', timePart = '', dateTimeStr = 'N/A', dateISO = '';
          if (rawDate) {
            dateISO = rawDate.split(' ')[0] || '';
            const dtObj = new Date(rawDate.includes('T') ? rawDate : rawDate.replace(' ', 'T') + '+08:00');
            if (!isNaN(dtObj.getTime())) {
              datePart = dtObj.toLocaleDateString('en-US', { timeZone: 'Asia/Manila', month: 'short', day: 'numeric', year: 'numeric' });
              timePart = dtObj.toLocaleTimeString('en-US', { timeZone: 'Asia/Manila', hour: '2-digit', minute: '2-digit', hour12: true });
              dateTimeStr = `${datePart} at ${timePart}`;
            } else {
              datePart = rawDate.split(' ')[0] || '';
              timePart = rawDate.split(' ')[1] || '';
              dateTimeStr = rawDate;
            }
          }

          let payloadObj = log.context_json;
          if (typeof payloadObj === 'string') {
            try { payloadObj = JSON.parse(payloadObj); } catch(e) {}
          }
          if (!payloadObj) {
            payloadObj = {
              actor: { id: log.actor_user_id, email: userObj.email || null },
              target: { table: log.target_table || 'audit_logs', id: log.target_id || log.audit_id },
              network: { ip: log.ip_address || '127.0.0.1', method: log.request_method || 'POST', uri: log.request_uri || '' }
            };
          }

          return {
            id: `#ACT-${log.audit_id}`,
            audit_id: log.audit_id,
            date: dateISO,
            datePart: datePart,
            timePart: timePart,
            dateTimeStr: dateTimeStr,
            actor: actorName,
            role: roleName,
            roleColor: roleColor,
            department: deptName,
            module: moduleName,
            action: log.action || 'View',
            desc: log.description || 'Operational action logged in system audit trail.',
            ip: log.ip_address || '127.0.0.1',
            device: `${log.browser || 'Browser'} - ${log.operating_system || 'OS'}`,
            status: log.status || 'Success',
            payloadStr: JSON.stringify(payloadObj)
          };
        });

        if (window.civAudit.userActivities.filters) {
          window.civAudit.userActivities.filters.applyFilters();
        }
      } else {
        if (tbody) {
          tbody.innerHTML = `
            <tr>
              <td colspan="7" class="py-12 text-center text-slate-400">
                <i class="fa-solid fa-folder-open text-3xl mb-2"></i>
                <p class="text-xs font-bold">No audit log records available</p>
              </td>
            </tr>
          `;
        }
      }
    } catch (err) {
      console.error('Error fetching audit logs:', err);
      if (tbody) {
        tbody.innerHTML = `
          <tr>
            <td colspan="7" class="py-12 text-center text-rose-500 font-bold text-xs">
              Failed to connect to audit logs endpoint.
            </td>
          </tr>
        `;
      }
    } finally {
      if (typeof hideUserActivitiesSkeleton === 'function') {
        hideUserActivitiesSkeleton();
      }
    }
  }
};
