// CREATE ACCOUNT FORM

// CHECK IF ALL REQUIRED FIELDS ARE FILLED TO ENABLE & GENERATE EMPLOYEE ID
function checkFormCompletion() {
  const roleSelect = document.getElementById('role');
  const deptSelect = document.getElementById('department');
  const posInput = document.getElementById('position');
  const empIdInput = document.getElementById('empId');
  const btnGenerateEmpId = document.getElementById('btnGenerateEmpId');

  const fName = document.getElementById('firstName') ? document.getElementById('firstName').value.trim() : '';
  const lName = document.getElementById('lastName') ? document.getElementById('lastName').value.trim() : '';
  const email = document.getElementById('email') ? document.getElementById('email').value.trim() : '';
  const mobile = document.getElementById('mobileNumber') ? document.getElementById('mobileNumber').value.trim() : '';
  const dept = deptSelect ? deptSelect.value : '';
  const pos = posInput ? posInput.value.trim() : '';
  const role = roleSelect ? roleSelect.value : '';

  const isComplete = fName !== '' && lName !== '' && email !== '' && mobile !== '' && dept !== '' && pos !== '' && role !== '';

  if (btnGenerateEmpId) {
    if (isComplete) {
      btnGenerateEmpId.disabled = false;
      btnGenerateEmpId.classList.remove('opacity-40', 'cursor-not-allowed', 'bg-slate-50', 'text-slate-400');
      btnGenerateEmpId.classList.add('opacity-100', 'cursor-pointer', 'bg-brand-light', 'text-brand-dark', 'hover:bg-brand-medium', 'hover:text-white');
      
      // Auto generate if not generated yet
      if (empIdInput && (!empIdInput.value || empIdInput.value.includes('Auto-generated'))) {
        if (typeof autoGenerateEmpId === 'function') autoGenerateEmpId();
      }
    } else {
      btnGenerateEmpId.disabled = true;
      btnGenerateEmpId.classList.remove('opacity-100', 'cursor-pointer', 'bg-brand-light', 'text-brand-dark', 'hover:bg-brand-medium', 'hover:text-white');
      btnGenerateEmpId.classList.add('opacity-40', 'cursor-not-allowed', 'bg-slate-50', 'text-slate-400');
      if (empIdInput) {
        empIdInput.value = '';
      }
    }
  }
}

// DYNAMIC ROLE ALERT MESSAGES & TRIGGER COMPLETION CHECK
function handleRoleChange() {
  const roleSelect = document.getElementById('role');
  const selectedRoleOpt = roleSelect ? roleSelect.options[roleSelect.selectedIndex] : null;
  const roleName = selectedRoleOpt ? (selectedRoleOpt.dataset.name || '') : '';
  const alertBox = document.getElementById('roleAlertBox');
  const alertText = alertBox ? alertBox.querySelector('p.leading-relaxed') : null;
  const alertTitle = alertBox ? alertBox.querySelector('p.font-bold') : null;

  if (alertBox && alertTitle && alertText) {
    if (roleName.includes('Super') || roleName.includes('Superadmin')) {
      alertBox.className = "bg-blue-50 border border-blue-200 text-blue-800 rounded-2xl p-4 flex items-start gap-3 shadow-xs transition duration-300";
      alertTitle.innerText = "System Role Scope Clearance Notice";
      alertText.innerText = "As a Superadmin, you can register users for all departments. Department Admins will be locked to their specific department scope.";
    } else if (roleName.includes('Admin')) {
      alertBox.className = "bg-amber-50 border border-amber-250 text-amber-850 rounded-2xl p-4 flex items-start gap-3 shadow-xs transition duration-300";
      alertTitle.innerText = "Department Administrator Scope Alert";
      alertText.innerText = "Assigning Department Admin grants administrative credentials. Access rights will be restricted to the designated department scope only.";
    } else {
      alertBox.className = "bg-slate-50 border border-slate-200 text-slate-700 rounded-2xl p-4 flex items-start gap-3 shadow-xs transition duration-300";
      alertTitle.innerText = "General Staff Account Profile";
      alertText.innerText = "Standard staff accounts operate with transactional module permissions depending on position designations and custom access overrides.";
    }
  }

  checkFormCompletion();
}

function resetCreateForm() {
  if (typeof closeSuccessModal === 'function') closeSuccessModal();
  const form = document.getElementById('createUserForm');
  if (form) form.reset();
  checkFormCompletion();
}
