// Render Table Rows
function renderDepts(deptsList = departmentsData) {
  if (!deptsTbody) return;
  deptsTbody.innerHTML = '';

  if (deptsList.length === 0) {
    deptsTbody.innerHTML = `
      <tr>
        <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-semibold">
          <i class="fa-solid fa-building-circle-exclamation text-3xl mb-3 block opacity-60"></i>
          No departments matched your query.
        </td>
      </tr>
    `;
    document.getElementById('deptsPaginationText').innerText = "Showing 0 to 0 of 0 departments";
    return;
  }

  const isSuperAdmin = currentUserScope ? !!currentUserScope.is_superadmin : false;
  const grantedActions = currentUserScope ? (currentUserScope.granted_actions || []) : [];
  const canEdit = isSuperAdmin || grantedActions.includes('EDIT');

  deptsList.forEach(dept => {
    const isActive = dept.status === 'Active';
    const statusClass = isActive ? 'bg-emerald-50 text-emerald-700 border-emerald-150' : 'bg-slate-50 text-slate-500 border-slate-200';
    const dotPulse = isActive
      ? '<span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>'
      : '<span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>';

    // Resolve admin name from joined users object
    const adminObj = dept.users || null;
    const adminName = adminObj ? `${adminObj.first_name} ${adminObj.last_name}` : null;
    const hasAdmin = !!adminName;

    const adminDisplay = hasAdmin
      ? `<span class="font-extrabold text-slate-700">${adminName}</span>`
      : `<span class="bg-amber-50 text-amber-700 px-2 py-0.5 rounded text-[10px] font-black border border-amber-100/60 inline-flex items-center gap-1"><i class="fa-solid fa-user-slash text-[9px]"></i> Unassigned</span>`;

    const isChecked = isActive ? 'checked' : '';

    const warningBanner = !hasAdmin ? `
      <tr class="bg-amber-50/40 border-b border-slate-200/50">
        <td colspan="5" class="px-6 py-2">
          <div class="flex items-center gap-2 text-[10px] text-amber-800 font-bold">
            <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
            <span>Attention required: This department currently has no assigned Department Head. System routing may default to global admin.</span>
          </div>
        </td>
      </tr>
    ` : '';

    const row = `
      <tr class="hover:bg-slate-50/50 transition">
        <!-- Department Name & Code -->
        <td class="px-6 py-4.5">
          <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600 shrink-0 font-bold text-xs">
              <i class="fa-solid fa-building"></i>
            </div>
            <div>
              <span class="font-black text-slate-900 tracking-tight text-xs block">${dept.department_name}</span>
              <span class="text-[10px] text-slate-400 font-mono font-bold uppercase tracking-wider">${dept.department_code}</span>
            </div>
          </div>
        </td>

        <!-- Department Head -->
        <td class="px-6 py-4.5 text-xs">
          ${adminDisplay}
        </td>

        <!-- Integration Tag -->
        <td class="px-6 py-4.5">
          <span class="bg-slate-50 border border-slate-200/50 rounded-lg px-2 py-0.5 text-[9px] text-slate-450 font-bold font-mono tracking-tight">${dept.department_code.toLowerCase()}_core</span>
        </td>

        <!-- Status -->
        <td class="px-6 py-4.5">
          <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full border ${statusClass} inline-flex items-center gap-1.5">
            ${dotPulse}
            <span>${dept.status}</span>
          </span>
        </td>

        <!-- Actions -->
        <td class="px-6 py-4.5 text-right whitespace-nowrap">
          <div class="inline-flex items-center space-x-3">
            ${canEdit ? `
            <!-- Toggle Status Switch -->
            <label class="relative inline-flex items-center cursor-pointer select-none" title="Activate/Deactivate department">
              <input type="checkbox" ${isChecked} onchange="handleDeptToggle(${dept.department_id}, this)" class="sr-only peer">
              <div class="w-8 h-4.5 bg-slate-200 rounded-full peer peer-focus:ring-2 peer-focus:ring-brand-medium/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-emerald-500"></div>
            </label>` : ''}

            <!-- View details button -->
            <button onclick="openViewModal(${dept.department_id})" class="text-slate-400 hover:text-[#0f172a] hover:bg-slate-50 p-1.5 rounded-lg border border-transparent hover:border-slate-150 transition cursor-pointer" title="View Summary Details">
              <i class="fa-solid fa-circle-info text-xs"></i>
            </button>

            ${canEdit ? `
            <!-- Edit button -->
            <button onclick="openEditModal(${dept.department_id})" class="text-slate-400 hover:text-[#0f172a] hover:bg-slate-50 p-1.5 rounded-lg border border-transparent hover:border-slate-150 transition cursor-pointer" title="Edit Parameters">
              <i class="fa-solid fa-pen text-xs"></i>
            </button>` : ''}
          </div>
        </td>
      </tr>
      ${warningBanner}
    `;
    deptsTbody.innerHTML += row;
  });

  document.getElementById('deptsPaginationText').innerText = `Showing 1 to ${deptsList.length} of ${departmentsData.length} departments`;
  hideDeptSkeleton();
}

function hideDeptSkeleton() {
  const skel = document.getElementById('deptSkeleton');
  const real = document.getElementById('deptRealContent');
  if (!skel || !real) return;

  real.classList.remove('hidden');
  requestAnimationFrame(() => {
    skel.classList.add('opacity-0', 'pointer-events-none');
    real.classList.remove('opacity-0', 'translate-y-2');
    real.classList.add('opacity-100', 'translate-y-0');
    setTimeout(() => {
      skel.classList.add('hidden');
    }, 500);
  });
}

// Update Statistics Cards
function updateSummary() {
  const total = departmentsData.length;
  const assignedAdmins = departmentsData.filter(d => d.department_head_user_id).length;

  document.getElementById('statTotalDepts').innerText = `${total} Defined`;
  document.getElementById('statAssignedAdmins').innerText = `${assignedAdmins} Assigned`;
}
