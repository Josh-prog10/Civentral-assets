// ROLES DATASTORE API
var systemRoles = [];
var currentUserScope = null;
window.pendingActionRoleId = null;

// FETCH ROLES FROM Database REST ENDPOINT
async function fetchRoles() {
  try {
    const response = await fetch('../../api/employee/roles.php');
    const result = await response.json();
    if (result.status === 'success' && Array.isArray(result.data)) {
      systemRoles = result.data;
      currentUserScope = result.current_user || null;
      if (typeof filterRoles === 'function') {
        filterRoles();
      }
    } else {
      console.warn('Roles fetch notice:', result.message);
    }
  } catch (err) {
    console.error('Error fetching roles FROM DATABASE:', err);
    if (typeof showToast === 'function') showToast('Network error connecting to Database.', true);
  } finally {
    if (typeof hideRolesSkeleton === 'function') {
      hideRolesSkeleton();
    }
  }
}

// DEACTIVATE ROLE STATUS TOGGLE CONFIRMATION
async function handleRoleStatusToggle(roleId, toggleInput) {
  const role = systemRoles.find(r => r.role_id === roleId || r.id === roleId);
  if (!role) return;

  const isOwnRole = (
    (window.currentUserRoleId && (role.role_id == window.currentUserRoleId || role.id == window.currentUserRoleId)) ||
    (window.currentUserRoleName && role.role_name && role.role_name.toLowerCase() === window.currentUserRoleName.toLowerCase()) ||
    (currentUserScope && currentUserScope.role_id && (role.role_id == currentUserScope.role_id || role.id == currentUserScope.role_id))
  );

  if (isOwnRole) {
    toggleInput.checked = true;
    if (typeof showToast === 'function') showToast("Operation denied: You cannot deactivate your own assigned role.", true);
    return;
  }

  if (role.is_system_role) {
    toggleInput.checked = true;
    if (typeof showToast === 'function') showToast("Protected system roles cannot be deactivated.", true);
    return;
  }

  if (toggleInput.checked) {
    toggleInput.checked = false;
    await toggleRoleStatusInDb(roleId, 'Active');
  } else {
    toggleInput.checked = true;
    window.pendingActionRoleId = roleId;
    const msgEl = document.getElementById('statusConfirmMessage');
    if (msgEl) {
      msgEl.innerText = `Are you sure you want to deactivate the "${role.role_name}" role? Users assigned to this designation will lose active access scopes.`;
    }
    if (typeof openModal === 'function') openModal('statusConfirmModal');
  }
}

async function toggleRoleStatusInDb(roleId, newStatus) {
  try {
    const response = await fetch('../../api/employee/roles.php', {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ role_id: roleId, status: newStatus })
    });
    const result = await response.json();
    if (result.status === 'success') {
      if (typeof showToast === 'function') showToast(`Status updated to ${newStatus} for role.`);
      await fetchRoles();
      if (typeof switchStatusTab === 'function') switchStatusTab(newStatus);
    } else {
      if (typeof showToast === 'function') showToast(result.message || 'Failed to update status.', true);
    }
  } catch (err) {
    console.error('Update status error:', err);
    if (typeof showToast === 'function') showToast('Error connecting to Database.', true);
  }
}

async function executeStatusDeactivate() {
  if (window.pendingActionRoleId) {
    if (typeof closeModal === 'function') closeModal('statusConfirmModal');
    await toggleRoleStatusInDb(window.pendingActionRoleId, 'Inactive');
    window.pendingActionRoleId = null;
  }
}
