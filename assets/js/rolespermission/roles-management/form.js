// ROLES MANAGEMENT FORM

// AUTO-GENERATE PREFIX FROM ROLE NAME
function autoGenerateRolePrefix(nameVal) {
  const prefixInput = document.getElementById('rolePrefix');
  if (!prefixInput || prefixInput.dataset.manual === 'true') return;

  const words = nameVal.trim().split(/\s+/).filter(Boolean);
  if (words.length === 0) {
    prefixInput.value = '';
    return;
  }
  if (words.length === 1) {
    prefixInput.value = words[0].substring(0, 4).toUpperCase();
  } else {
    prefixInput.value = words.map(w => w[0]).join('').substring(0, 4).toUpperCase();
  }
}

async function handleSaveRole(e) {
  e.preventDefault();
  
  const idRef = document.getElementById('roleIdRef').value;
  const name = document.getElementById('roleName').value.trim();
  const prefix = document.getElementById('rolePrefix').value.trim().toUpperCase();
  const isGlobal = document.getElementById('roleIsGlobalAccess').checked;
  const desc = document.getElementById('roleDesc').value.trim();
  const status = document.getElementById('roleStatus').value;

  const payload = {
    role_name: name,
    role_prefix: prefix,
    is_global_access: isGlobal,
    description: desc,
    status: status
  };

  if (idRef !== '') {
    const targetRoleId = parseInt(idRef);
    const role = systemRoles.find(r => r.role_id === targetRoleId || r.id === targetRoleId);
    const isOwnRole = (
      (window.currentUserRoleId && targetRoleId == window.currentUserRoleId) ||
      (role && window.currentUserRoleName && role.role_name && role.role_name.toLowerCase() === window.currentUserRoleName.toLowerCase()) ||
      (window.currentUserRoleName && name.toLowerCase() === window.currentUserRoleName.toLowerCase())
    );
    if (isOwnRole && ['inactive', 'deactivated', 'archived'].includes(status.toLowerCase())) {
      if (typeof showToast === 'function') showToast("Forbidden. You cannot set your own assigned role to inactive or archived.", true);
      return;
    }
    payload.role_id = targetRoleId;
  }

  const method = idRef === '' ? 'POST' : 'PUT';

  try {
    const response = await fetch('../../api/employee/roles.php', {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await response.json();

    if (result.status === 'success') {
      if (typeof showToast === 'function') showToast(result.message || 'System role saved successfully.');
      if (typeof closeModal === 'function') closeModal('roleModal');
      if (typeof fetchRoles === 'function') await fetchRoles();
    } else {
      if (typeof showToast === 'function') showToast(result.message || 'Error saving role.', true);
    }
  } catch (err) {
    console.error('Save role error:', err);
    if (typeof showToast === 'function') showToast('Failed to save role TO DATABASE.', true);
  }
}
