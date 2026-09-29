function openModal(id) {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.classList.remove('opacity-0', 'pointer-events-none');
  modal.classList.add('opacity-100', 'pointer-events-auto');
  modal.querySelector('.transform').classList.remove('scale-95');
  modal.querySelector('.transform').classList.add('scale-100');
}

function closeModal(id) {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.classList.remove('opacity-100', 'pointer-events-auto');
  modal.classList.add('opacity-0', 'pointer-events-none');
  modal.querySelector('.transform').classList.remove('scale-100');
  modal.querySelector('.transform').classList.add('scale-95');
}

// Populate Admin Dropdown
function populateAdminDropdown(selectedUserId = null) {
  const adminSelect = document.getElementById('deptAdmin');
  if (!adminSelect) return;

  adminSelect.innerHTML = '<option value="">Unassigned</option>';
  usersData.forEach(u => {
    const fullName = `${u.first_name} ${u.last_name}`;
    const opt = document.createElement('option');
    opt.value = u.user_id;
    opt.textContent = fullName;
    if (selectedUserId && parseInt(selectedUserId) === u.user_id) {
      opt.selected = true;
    }
    adminSelect.appendChild(opt);
  });
}

// Create Modal
function openCreateModal() {
  const isSuperAdmin = currentUserScope ? !!currentUserScope.is_superadmin : false;
  const grantedActions = currentUserScope ? (currentUserScope.granted_actions || []) : [];
  const canCreate = isSuperAdmin || grantedActions.includes('CREATE');

  if (!canCreate) {
    if (typeof showToast === 'function') showToast('Forbidden. View-only access level cannot create departments.');
    return;
  }

  const deptForm = document.getElementById('deptForm');
  if (deptForm) deptForm.reset();
  const deptIdRef = document.getElementById('deptIdRef');
  if (deptIdRef) deptIdRef.value = '';
  const modalTitle = document.getElementById('deptModalTitle');
  if (modalTitle) modalTitle.innerText = "Create Department";
  const modalIcon = document.getElementById('deptModalIcon');
  if (modalIcon) modalIcon.className = "fa-solid fa-building text-brand-medium";
  if (typeof populateAdminDropdown === 'function') populateAdminDropdown();
  openModal('deptModal');
}

// Edit Modal
function openEditModal(deptId) {
  const isSuperAdmin = currentUserScope ? !!currentUserScope.is_superadmin : false;
  const grantedActions = currentUserScope ? (currentUserScope.granted_actions || []) : [];
  const canEdit = isSuperAdmin || grantedActions.includes('EDIT');

  if (!canEdit) {
    if (typeof showToast === 'function') showToast('Forbidden. View-only access level cannot modify departments.');
    return;
  }

  const dept = departmentsData.find(d => d.department_id === deptId);
  if (!dept) return;

  document.getElementById('deptIdRef').value = dept.department_id;
  document.getElementById('deptName').value = dept.department_name;
  document.getElementById('deptCode').value = dept.department_code;
  document.getElementById('deptDesc').value = dept.description || '';

  populateAdminDropdown(dept.department_head_user_id);

  document.getElementById('deptModalTitle').innerText = "Modify Department Parameters";
  document.getElementById('deptModalIcon').className = "fa-solid fa-pen text-brand-medium";
  openModal('deptModal');
}

// View Details Modal
function openViewModal(deptId) {
  const dept = departmentsData.find(d => d.department_id === deptId);
  if (!dept) return;

  const adminObj = dept.users || null;
  const adminName = adminObj ? `${adminObj.first_name} ${adminObj.last_name}` : 'Unassigned';

  document.getElementById('viewDeptCodeBadge').innerText = dept.department_code;
  document.getElementById('viewDeptName').innerText = dept.department_name;
  document.getElementById('viewDeptDesc').innerText = dept.description || "No description provided.";
  document.getElementById('viewDeptStaffCount').innerText = `Admin: ${adminName}`;

  const scopesContainer = document.getElementById('viewDeptScopesContainer');
  scopesContainer.innerHTML = `<span class="bg-slate-150 text-slate-700 px-2.5 py-1 rounded-lg text-[10px] font-black border border-slate-200/60 font-mono tracking-tight">${dept.department_code.toLowerCase()}_core</span>`;

  openModal('viewDeptModal');
}
