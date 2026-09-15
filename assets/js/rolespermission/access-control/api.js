// ACCESS CONTROL API

var scopeRoles = [];         // Live roles FROM DATABASE
var departmentsList = [];    // Live departments FROM DATABASE
var roleDeptAccess = [];     // Live role_department_access rows FROM DATABASE

window.selectedScopeRoleId = null;
window.savedScopes = {};    // role_id -> { globalMode, deptLockin, departmentIds: [] }
window.currentScopes = {};  // Working state

// FETCH ALL DATA FROM Database API
async function fetchAccessControlData() {
  try {
    const response = await fetch('../../api/employee/access-control.php');
    const result = await response.json();

    if (result.status === 'success') {
      scopeRoles = result.roles || [];
      departmentsList = result.departments || [];
      roleDeptAccess = result.role_department_access || [];

      // Build scopes map: role_id -> { globalMode, deptLockin, departmentIds }
      const scopesMap = {};
      scopeRoles.forEach(role => {
        const grantedDeptIds = roleDeptAccess
          .filter(rda => rda.role_id === role.role_id)
          .map(rda => rda.department_id);

        scopesMap[role.role_id] = {
          globalMode: !!role.is_global_access,
          deptLockin: !role.is_global_access && grantedDeptIds.length === 1,
          departmentIds: grantedDeptIds
        };
      });

      window.savedScopes = JSON.parse(JSON.stringify(scopesMap));
      window.currentScopes = JSON.parse(JSON.stringify(scopesMap));

      if (scopeRoles.length > 0 && !window.selectedScopeRoleId) {
        window.selectedScopeRoleId = scopeRoles[0].role_id;
      }

      if (typeof renderRoles === 'function') renderRoles();
      if (typeof renderDeptCards === 'function') renderDeptCards();
      if (typeof syncToggles === 'function') syncToggles();
      if (typeof syncTokenPreview === 'function') syncTokenPreview();
    } else {
      if (typeof showToast === 'function') showToast(result.message || 'Error loading access control data.');
    }
  } catch (err) {
    console.error('Error fetching access control data:', err);
    if (typeof showToast === 'function') showToast('Network error connecting TO DATABASE.', true);
  }
}

// SAVE BOUNDARY RULES TO DATABASE
async function saveScopeChanges() {
  const roleId = window.selectedScopeRoleId;
  if (!roleId) return;

  const current = window.currentScopes[roleId];
  if (!current) return;

  const payload = {
    role_id: roleId,
    is_global_access: current.globalMode,
    is_dept_lockin: current.deptLockin,
    department_ids: current.globalMode
      ? departmentsList.map(d => d.department_id)
      : current.departmentIds
  };

  try {
    const response = await fetch('../../api/employee/access-control.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const result = await response.json();
    if (result.status === 'success') {
      window.savedScopes = JSON.parse(JSON.stringify(window.currentScopes));
      if (typeof renderRoles === 'function') renderRoles(); // Refresh subtitle text
      if (typeof showToast === 'function') showToast(result.message || `Access boundary saved successfully.`);
    } else {
      if (typeof showToast === 'function') showToast(result.message || 'Failed to save access boundary.', true);
    }
  } catch (err) {
    console.error('Error saving scope changes:', err);
    if (typeof showToast === 'function') showToast('Failed to save boundary rules TO DATABASE.', true);
  }
}
