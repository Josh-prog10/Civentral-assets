// ACCESS CONTROL EVENTS

// SELECT A ROLE
function selectRole(roleId) {
  window.selectedScopeRoleId = roleId;
  window.currentScopes = JSON.parse(JSON.stringify(window.savedScopes));

  const roleObj = scopeRoles.find(r => r.role_id === roleId);
  const selectedTitle = document.getElementById('scopeSelectedRoleTitle');
  if (selectedTitle) {
    selectedTitle.innerText = `Access Boundaries for: ${roleObj ? roleObj.role_name : 'Role'}`;
  }

  if (typeof renderRoles === 'function') renderRoles();
  if (typeof renderDeptCards === 'function') renderDeptCards();
  if (typeof syncToggles === 'function') syncToggles();
  if (typeof syncTokenPreview === 'function') syncTokenPreview();
}

// TOGGLE INDIVIDUAL DEPARTMENT CARD
function toggleDeptCard(deptId) {
  const roleId = window.selectedScopeRoleId;
  const current = window.currentScopes[roleId];
  if (!current) return;

  const idx = current.departmentIds.indexOf(deptId);

  if (idx !== -1) {
    if (current.departmentIds.length === 1 && current.deptLockin) {
      if (typeof showToast === 'function') showToast("Boundary rules require at least one department clearance while lockin mode is enabled.", true);
      return;
    }
    current.departmentIds.splice(idx, 1);
  } else {
    if (current.deptLockin) {
      current.departmentIds = [deptId];
    } else {
      current.departmentIds.push(deptId);
    }
  }

  if (typeof renderDeptCards === 'function') renderDeptCards();
  if (typeof syncTokenPreview === 'function') syncTokenPreview();
}

// HANDLE GLOBAL ACCESS SWITCH
function handleGlobalToggle(switchEl) {
  const roleId = window.selectedScopeRoleId;
  const current = window.currentScopes[roleId];
  if (!current) return;

  if (switchEl.checked) {
    current.globalMode = true;
    current.deptLockin = false;
    current.departmentIds = departmentsList.map(d => d.department_id);
  } else {
    current.globalMode = false;
    current.departmentIds = departmentsList.length > 0 ? [departmentsList[0].department_id] : [];
  }

  if (typeof renderDeptCards === 'function') renderDeptCards();
  if (typeof syncToggles === 'function') syncToggles();
  if (typeof syncTokenPreview === 'function') syncTokenPreview();
}

// HANDLE DEPARTMENT LOCKIN SWITCH
function handleLockinToggle(switchEl) {
  const roleId = window.selectedScopeRoleId;
  const current = window.currentScopes[roleId];
  if (!current) return;

  if (switchEl.checked) {
    current.deptLockin = true;
    current.globalMode = false;
    if (current.departmentIds.length > 0) {
      current.departmentIds = [current.departmentIds[0]];
    } else if (departmentsList.length > 0) {
      current.departmentIds = [departmentsList[0].department_id];
    }
  } else {
    current.deptLockin = false;
  }

  if (typeof renderDeptCards === 'function') renderDeptCards();
  if (typeof syncToggles === 'function') syncToggles();
  if (typeof syncTokenPreview === 'function') syncTokenPreview();
}

// DISCARD SCOPE CHANGES
function discardScopeChanges() {
  window.currentScopes = JSON.parse(JSON.stringify(window.savedScopes));
  if (typeof renderDeptCards === 'function') renderDeptCards();
  if (typeof syncToggles === 'function') syncToggles();
  if (typeof syncTokenPreview === 'function') syncTokenPreview();
  if (typeof showToast === 'function') showToast("Access boundary rules discarded back to saved state.");
}
