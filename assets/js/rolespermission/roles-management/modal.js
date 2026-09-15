// MODAL CONTROL HELPERS
function openModal(id) {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.classList.remove('opacity-0', 'pointer-events-none');
  modal.classList.add('opacity-100', 'pointer-events-auto');
  const innerCard = modal.querySelector('.transform');
  if (innerCard) {
    innerCard.classList.remove('scale-95');
    innerCard.classList.add('scale-100');
  }
}

function closeModal(id) {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.classList.remove('opacity-100', 'pointer-events-auto');
  modal.classList.add('opacity-0', 'pointer-events-none');
  const innerCard = modal.querySelector('.transform');
  if (innerCard) {
    innerCard.classList.remove('scale-100');
    innerCard.classList.add('scale-95');
  }
}

// CREATE / EDIT ROLE MODAL HANDLERS
function openCreateModal() {
  const isSuperAdmin = currentUserScope ? !!currentUserScope.is_superadmin : false;
  const grantedActions = currentUserScope ? (currentUserScope.granted_actions || []) : [];
  const canCreate = isSuperAdmin || grantedActions.includes('CREATE');

  if (!canCreate) {
    if (typeof showToast === 'function') showToast('Forbidden. View-only access level cannot create system roles.', true);
    return;
  }

  document.getElementById('roleForm').reset();
  document.getElementById('roleIdRef').value = '';
  document.getElementById('rolePrefix').dataset.manual = 'false';

  const statusEl = document.getElementById('roleStatus');
  if (statusEl) {
    statusEl.disabled = false;
    statusEl.title = '';
  }

  document.getElementById('roleModalTitle').innerText = "Create System Role";
  document.getElementById('roleModalIcon').className = "fa-solid fa-user-shield text-brand-medium";
  
  openModal('roleModal');
}

function openEditModal(roleId) {
  const isSuperAdmin = currentUserScope ? !!currentUserScope.is_superadmin : false;
  const grantedActions = currentUserScope ? (currentUserScope.granted_actions || []) : [];
  const canEdit = isSuperAdmin || grantedActions.includes('EDIT');

  if (!canEdit) {
    if (typeof showToast === 'function') showToast('Forbidden. View-only access level cannot modify system roles.', true);
    return;
  }

  const role = systemRoles.find(r => r.role_id === roleId || r.id === roleId);
  if (!role) return;

  document.getElementById('roleIdRef').value = role.role_id;
  document.getElementById('roleName').value = role.role_name;
  document.getElementById('rolePrefix').value = role.role_prefix;
  document.getElementById('rolePrefix').dataset.manual = 'true';
  
  const globalChk = document.getElementById('roleIsGlobalAccess');
  if (globalChk && globalChk.type === 'checkbox') {
    globalChk.checked = !!role.is_global_access;
  }

  document.getElementById('roleDesc').value = role.description || '';

  const isOwnRole = (
    (window.currentUserRoleId && (role.role_id == window.currentUserRoleId || role.id == window.currentUserRoleId)) ||
    (window.currentUserRoleName && role.role_name && role.role_name.toLowerCase() === window.currentUserRoleName.toLowerCase()) ||
    (currentUserScope && currentUserScope.role_id && (role.role_id == currentUserScope.role_id || role.id == currentUserScope.role_id))
  );

  const statusEl = document.getElementById('roleStatus');
  if (statusEl) {
    statusEl.value = role.status || 'Active';
    if (isOwnRole || role.is_system_role) {
      statusEl.disabled = true;
      statusEl.title = isOwnRole ? "You cannot change the status of your own role." : "System roles status cannot be changed.";
    } else {
      statusEl.disabled = false;
      statusEl.title = "";
    }
  }

  document.getElementById('roleModalTitle').innerText = `Edit Role: ${role.role_name}`;
  document.getElementById('roleModalIcon').className = "fa-solid fa-pen-to-square text-brand-medium";

  openModal('roleModal');
}
