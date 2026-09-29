// ROLES MANAGEMENT UI

// RENDER ROLES TABLE BASED ON DATABASE FIELDS
function renderRoles(dataToRender = systemRoles) {
  const rolesTbody = document.getElementById('rolesTableBody');
  if (!rolesTbody) return;
  rolesTbody.innerHTML = '';
  
  const totalFiltered = dataToRender.length;
  const totalPages = Math.ceil(totalFiltered / pageSize) || 1;
  if (currentPage > totalPages) currentPage = totalPages;
  if (currentPage < 1) currentPage = 1;

  const startIndex = (currentPage - 1) * pageSize;
  const endIndex = Math.min(startIndex + pageSize, totalFiltered);
  const pageData = dataToRender.slice(startIndex, endIndex);

  if (totalFiltered === 0) {
    rolesTbody.innerHTML = `
      <tr>
        <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-semibold">
          <i class="fa-solid fa-folder-open text-3xl mb-3 block opacity-60"></i>
          No roles match your search filter.
        </td>
      </tr>
    `;
  } else {
    const isSuperAdmin = currentUserScope ? !!currentUserScope.is_superadmin : false;
    const grantedActions = currentUserScope ? (currentUserScope.granted_actions || []) : [];
    const canEdit = isSuperAdmin || grantedActions.includes('EDIT');

    pageData.forEach(role => {
      // Status Badge classes
      const rStatus = role.status || 'Active';
      const statusStyles = rStatus === 'Active' 
        ? 'bg-emerald-50 text-emerald-700 border-emerald-200' 
        : (rStatus === 'Archived' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-slate-100 text-slate-600 border-slate-200');
      
      const dotPulse = rStatus === 'Active' 
        ? '<span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>' 
        : (rStatus === 'Archived' ? '<span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>' : '<span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>');

      // Global Access Badge
      const globalAccessBadge = role.is_global_access
        ? `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fa-solid fa-globe text-[9px]"></i> Global Access</span>`
        : `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200"><i class="fa-solid fa-building text-[9px]"></i> Department Scoped</span>`;

      // System Role Protection Badge
      const systemRoleBadge = role.is_system_role
        ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-black bg-amber-50 text-amber-700 border border-amber-200" title="Protected System Role"><i class="fa-solid fa-lock text-[8px]"></i> System Core</span>`
        : `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-600 border border-slate-200">Custom Role</span>`;

      const isChecked = rStatus === 'Active' ? 'checked' : '';

      const tr = document.createElement('tr');
      tr.className = 'hover:bg-slate-50/50 transition';
      tr.innerHTML = `
        <!-- Role Designation & Prefix -->
        <td class="px-6 py-4">
          <div class="flex items-center gap-3">
            <div class="h-9 min-w-[2.25rem] px-2.5 rounded-xl bg-gradient-to-br from-brand-light to-blue-50 border border-brand-border/80 flex items-center justify-center text-brand-dark shrink-0 font-mono font-black text-xs tracking-wider shadow-2xs">
              ${role.role_prefix}
            </div>
            <div class="flex items-center gap-2">
              <span class="font-extrabold text-slate-900 tracking-tight text-xs">${role.role_name}</span>
              ${systemRoleBadge}
            </div>
          </div>
        </td>
        
        <!-- Scope & Access Level -->
        <td class="px-6 py-4">
          <div class="space-y-1 max-w-xs">
            <p class="text-[11px] text-slate-600 font-medium leading-relaxed">${role.description}</p>
            <div class="pt-0.5">
              ${globalAccessBadge}
            </div>
          </div>
        </td>

        <!-- Created At -->
        <td class="px-6 py-4 text-slate-500 font-mono text-[10px] font-bold">
          ${role.created_at}
        </td>

        <!-- Status Pill -->
        <td class="px-6 py-4 text-center">
          <span class="text-[10px] font-black uppercase px-2.5 py-1 rounded-full border ${statusStyles} inline-flex items-center gap-1.5">
            ${dotPulse}
            <span>${rStatus}</span>
          </span>
        </td>

        <!-- Actions -->
        <td class="px-6 py-4 text-right whitespace-nowrap">
          <div class="inline-flex items-center space-x-3">
            ${canEdit ? `
            <!-- iOS Status Switch Toggle -->
            ${(() => {
              const isOwnRole = (
                (window.currentUserRoleId && (role.role_id == window.currentUserRoleId || role.id == window.currentUserRoleId)) ||
                (window.currentUserRoleName && role.role_name && role.role_name.toLowerCase() === window.currentUserRoleName.toLowerCase()) ||
                (currentUserScope && currentUserScope.role_id && (role.role_id == currentUserScope.role_id || role.id == currentUserScope.role_id))
              );
              const isToggleDisabled = role.is_system_role || isOwnRole;
              const toggleTitle = role.is_system_role 
                ? 'System roles cannot be deactivated' 
                : (isOwnRole ? 'You cannot deactivate your own assigned role' : 'Activate/Deactivate toggle');
              
              return `
                <label class="relative inline-flex items-center select-none ${isToggleDisabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'}" title="${toggleTitle}">
                  <input type="checkbox" ${isChecked} ${isToggleDisabled ? 'disabled' : ''} onchange="handleRoleStatusToggle(${role.role_id}, this)" class="sr-only peer">
                  <div class="w-8 h-4.5 bg-slate-200 rounded-full peer peer-focus:ring-2 peer-focus:ring-brand-medium/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-emerald-600"></div>
                </label>
              `;
            })()}

            <!-- Edit button -->
            <button type="button" onclick="if(typeof openEditModal === 'function') openEditModal(${role.role_id})" class="text-slate-400 hover:text-brand-dark hover:bg-slate-100 p-1.5 rounded-lg border border-slate-200 transition cursor-pointer" title="Edit Role Parameters">
              <i class="fa-solid fa-pen-to-square text-xs"></i>
            </button>` : `<span class="text-[10px] text-slate-400 font-bold italic">Read-only</span>`}
          </div>
        </td>
      `;
      rolesTbody.appendChild(tr);
    });
  }

  // Render Pagination Text & Controls
  renderPaginationUI(startIndex, endIndex, totalFiltered, totalPages);
  hideRolesSkeleton();

  if (typeof updateMetrics === 'function') updateMetrics();
}

function hideRolesSkeleton() {
  const skel = document.getElementById('rolesSkeleton');
  const real = document.getElementById('rolesRealContent');
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

function renderPaginationUI(startIndex, endIndex, totalFiltered, totalPages) {
  const paginationEl = document.getElementById('rolesPaginationText');
  const controlsEl = document.getElementById('rolesPaginationControls');

  if (paginationEl) {
    if (totalFiltered === 0) {
      paginationEl.innerText = "Showing 0 to 0 of 0 defined roles";
    } else {
      paginationEl.innerText = `Showing ${startIndex + 1} to ${endIndex} of ${totalFiltered} defined roles`;
    }
  }

  if (!controlsEl) return;
  controlsEl.innerHTML = '';

  if (totalPages <= 1) return;

  // Prev Button
  const prevBtn = document.createElement('button');
  prevBtn.className = `px-3 py-1.5 rounded-lg border text-xs font-bold transition flex items-center justify-center ${currentPage === 1 ? 'border-slate-200 bg-white text-slate-300 cursor-not-allowed' : 'border-slate-200 bg-white hover:bg-slate-50 text-slate-600 cursor-pointer'}`;
  prevBtn.disabled = currentPage === 1;
  prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left text-[9px]"></i>';
  prevBtn.onclick = () => { if (currentPage > 1) { currentPage--; filterRoles(); } };
  controlsEl.appendChild(prevBtn);

  // Page Numbers
  for (let i = 1; i <= totalPages; i++) {
    const pBtn = document.createElement('button');
    if (i === currentPage) {
      pBtn.className = "px-3 py-1.5 rounded-lg bg-[#86B6F6] border border-[#72a6eb] text-slate-900 font-black shadow-2xs text-xs";
    } else {
      pBtn.className = "px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 font-bold transition text-xs cursor-pointer";
    }
    pBtn.innerText = i;
    pBtn.onclick = () => { currentPage = i; filterRoles(); };
    controlsEl.appendChild(pBtn);
  }

  // Next Button
  const nextBtn = document.createElement('button');
  nextBtn.className = `px-3 py-1.5 rounded-lg border text-xs font-bold transition flex items-center justify-center ${currentPage === totalPages ? 'border-slate-200 bg-white text-slate-300 cursor-not-allowed' : 'border-slate-200 bg-white hover:bg-slate-50 text-slate-600 cursor-pointer'}`;
  nextBtn.disabled = currentPage === totalPages;
  nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right text-[9px]"></i>';
  nextBtn.onclick = () => { if (currentPage < totalPages) { currentPage++; filterRoles(); } };
  controlsEl.appendChild(nextBtn);
}

// UPDATE SUMMARY STATISTICS CARDS & TAB COUNTS
function updateMetrics() {
  const total = systemRoles.length;
  const globalCount = systemRoles.filter(r => r.is_global_access).length;
  const activeCount = systemRoles.filter(r => (r.status || 'Active') === 'Active').length;
  const inactiveCount = systemRoles.filter(r => (r.status || '').toLowerCase() === 'inactive').length;
  const archivedCount = systemRoles.filter(r => (r.status || '').toLowerCase() === 'archived').length;
  const systemCount = systemRoles.filter(r => r.is_system_role).length;

  const totalEl = document.getElementById('statTotalRoles');
  const globalEl = document.getElementById('statGlobalRoles');
  const activeEl = document.getElementById('statActiveRoles');
  const systemEl = document.getElementById('statSystemRoles');

  if (totalEl) totalEl.innerText = total;
  if (globalEl) globalEl.innerText = globalCount;
  if (activeEl) activeEl.innerText = activeCount;
  if (systemEl) systemEl.innerText = systemCount;

  // Update tab counts
  const tabActive = document.getElementById('tabCountActive');
  const tabInactive = document.getElementById('tabCountInactive');
  const tabArchived = document.getElementById('tabCountArchived');
  const tabAll = document.getElementById('tabCountAll');

  if (tabActive) tabActive.innerText = activeCount;
  if (tabInactive) tabInactive.innerText = inactiveCount;
  if (tabArchived) tabArchived.innerText = archivedCount;
  if (tabAll) tabAll.innerText = total;
}
