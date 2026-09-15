// CITIZEN ACCOUNT UI

// RENDER CONTROL TABLE
function renderControlTable(list = ctrlCitizens) {
  const ctrlTbody = document.getElementById('controlTableBody');
  if (!ctrlTbody) return;
  ctrlTbody.innerHTML = '';

  if (list.length === 0) {
    ctrlTbody.innerHTML = `
      <tr>
        <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-semibold">
          <i class="fa-solid fa-shield-halved text-3xl mb-3 block opacity-60"></i>
          No records matched the filter criteria.
        </td>
      </tr>
    `;
    const pagText = document.getElementById('ctrlPaginationText');
    if (pagText) pagText.innerText = "Showing 0 to 0 of 0 accounts";
    return;
  }

  list.forEach(cit => {
    // Badges
    let badgeClass = '';
    let iconIndicator = '';

    if (cit.status === 'Active') {
      badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-150';
      iconIndicator = '<span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>';
    } else if (cit.status === 'Inactive') {
      badgeClass = 'bg-slate-50 text-slate-500 border-slate-200';
      iconIndicator = '<span class="h-1.5 w-1.5 rounded-full bg-slate-400 shrink-0"></span>';
    } else if (cit.status === 'Locked') {
      badgeClass = 'bg-rose-50 text-rose-700 border-rose-150';
      iconIndicator = '<i class="fa-solid fa-lock text-[9px] text-rose-500 shrink-0"></i>';
    } else if (cit.status === 'Suspended') {
      badgeClass = 'bg-amber-50 text-amber-700 border-amber-150';
      iconIndicator = '<i class="fa-solid fa-triangle-exclamation text-[9px] text-amber-600 shrink-0"></i>';
    }

    // Violation logs styling
    const violationClass = cit.flagged ? 'text-rose-600 font-bold' : 'text-slate-500 font-medium';

    const row = `
      <tr class="hover:bg-slate-50/50 transition">
        <!-- Citizen ID -->
        <td class="px-6 py-4 font-mono font-bold text-slate-700 tracking-tight select-all">
          ${cit.id}
        </td>

        <!-- Citizen User -->
        <td class="px-6 py-4">
          <div class="flex flex-col">
            <span class="font-extrabold text-slate-800 tracking-tight leading-snug">${cit.name}</span>
            <span class="text-[10px] text-slate-400 font-medium">${cit.email}</span>
          </div>
        </td>

        <!-- Security Status -->
        <td class="px-6 py-4">
          <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full border ${badgeClass} inline-flex items-center gap-1.5">
            ${iconIndicator}
            <span>${cit.status}</span>
          </span>
        </td>

        <!-- Violations logs -->
        <td class="px-6 py-4">
          <span class="${violationClass}">${cit.violations}</span>
        </td>

        <!-- State Controller Actions -->
        <td class="px-6 py-4 text-right whitespace-nowrap">
          <div class="inline-flex items-center space-x-2.5">
            
            ${window.canEditCitizenAccounts ? `
            <!-- Quick Action Selector Dropdown -->
            <select onchange="if(typeof triggerStateChange === 'function') triggerStateChange('${cit.id}', this)" class="border border-slate-200 rounded-lg px-2 py-1 text-[11px] bg-slate-50 hover:bg-white text-slate-650 font-bold focus:outline-none transition cursor-pointer select-none">
              <option value="">Choose Action</option>
              <option value="Activate">Activate</option>
              <option value="Deactivate">Deactivate</option>
              <option value="Lock">Lock Account</option>
              <option value="Unlock">Unlock Account</option>
              <option value="Suspend">Suspend Profile</option>
              <option value="Restore">Restore Defaults</option>
            </select>
            ` : ''}

            <!-- Security Audit Timeline icon -->
            <button onclick="if(typeof openTimelineModal === 'function') openTimelineModal('${cit.id}')" class="text-slate-400 hover:text-[#0f172a] hover:bg-slate-50 p-1.5 rounded-lg border border-transparent hover:border-slate-150 transition cursor-pointer" title="View Security Audits">
              <i class="fa-solid fa-clock-rotate-left text-xs"></i>
            </button>

          </div>
        </td>
      </tr>
    `;
    ctrlTbody.innerHTML += row;
  });

  const pagText = document.getElementById('ctrlPaginationText');
  if (pagText) pagText.innerText = `Showing 1 to ${list.length} of ${list.length} accounts`;

  hideCitizenAccountSkeleton();
}

function hideCitizenAccountSkeleton() {
  const skel = document.getElementById('citizenAccountSkeleton');
  const real = document.getElementById('citizenAccountRealContent');
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

// UPDATE STATUS RIBBON STATISTICS
function updateControlStats() {
  const activeCount = ctrlCitizens.filter(c => c.status === 'Active').length;
  const inactiveCount = ctrlCitizens.filter(c => c.status === 'Inactive').length;
  const lockedCount = ctrlCitizens.filter(c => c.status === 'Locked').length;
  const suspendedCount = ctrlCitizens.filter(c => c.status === 'Suspended').length;

  const stActive = document.getElementById('statActiveCount');
  const stInactive = document.getElementById('statInactiveCount');
  const stLocked = document.getElementById('statLockedCount');
  const stSuspended = document.getElementById('statSuspendedCount');

  if (stActive) stActive.innerText = `${activeCount} Accounts`;
  if (stInactive) stInactive.innerText = `${inactiveCount} Accounts`;
  if (stLocked) stLocked.innerText = `${lockedCount} Accounts`;
  if (stSuspended) stSuspended.innerText = `${suspendedCount} Accounts`;
}
