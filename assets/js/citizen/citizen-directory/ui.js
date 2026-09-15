// CITIZEN DIRECTORY UI

function updateMetrics(allList = mockCitizens) {
  const metricTotal = document.getElementById('metricTotal');
  const metricActive = document.getElementById('metricActive');
  const metricInactive = document.getElementById('metricInactive');
  const metricLocked = document.getElementById('metricLocked');

  if (metricTotal) metricTotal.innerText = allList.length;
  if (metricActive) metricActive.innerText = allList.filter(c => c.status === 'Active').length;
  if (metricInactive) metricInactive.innerText = allList.filter(c => c.status === 'Inactive' || c.status === 'Pending').length;
  if (metricLocked) metricLocked.innerText = allList.filter(c => c.status === 'Locked').length;
}

// RENDER DATATABLE
function renderCitizens(list = mockCitizens) {
  updateMetrics();

  const citizenTbody = document.getElementById('citizenTableBody');
  if (!citizenTbody) return;
  citizenTbody.innerHTML = '';

  if (list.length === 0) {
    citizenTbody.innerHTML = `
      <tr>
        <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-semibold">
          <i class="fa-solid fa-users-slash text-3xl mb-3 block opacity-60"></i>
          No citizen accounts match the criteria.
        </td>
      </tr>
    `;
    const pagText = document.getElementById('citizenPaginationText');
    if (pagText) pagText.innerText = "Showing 0 to 0 of 0 accounts";
    return;
  }

  list.forEach(cit => {
    let statusClass = '';
    let dotIndicator = '';

    if (cit.status === 'Active') {
      statusClass = 'bg-emerald-50 text-emerald-700 border-emerald-150';
      dotIndicator = '<span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>';
    } else if (cit.status === 'Inactive') {
      statusClass = 'bg-slate-50 text-slate-500 border-slate-200';
      dotIndicator = '<span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>';
    } else if (cit.status === 'Locked') {
      statusClass = 'bg-rose-50 text-rose-700 border-rose-150';
      dotIndicator = '<i class="fa-solid fa-lock text-[9px] shrink-0 text-rose-500"></i>';
    }

    const lockIcon = cit.status === 'Locked' ? 'fa-lock text-rose-600 hover:bg-rose-50' : 'fa-lock-open text-slate-400 hover:text-slate-700 hover:bg-slate-50';
    const lockTitle = cit.status === 'Locked' ? 'Unlock Account' : 'Place Security Lock';
    const rawDate = new Date(cit.regDate);
    const options = { month: 'short', day: 'numeric', year: 'numeric' };
    const formattedRegDate = rawDate.toLocaleDateString('en-US', options);

    const row = `
      <tr class="hover:bg-slate-50/50 transition">
        <!-- Citizen ID -->
        <td class="px-6 py-4 font-mono font-bold text-slate-700 select-all tracking-tight">
          ${cit.id}
        </td>

        <!-- Full Name & Contact -->
        <td class="px-6 py-4">
          <div class="flex flex-col">
            <span class="font-extrabold text-slate-800 tracking-tight leading-snug">${cit.name}</span>
            <span class="text-[10px] text-slate-400 font-medium">${cit.email}</span>
          </div>
        </td>

        <!-- Barangay Residency -->
        <td class="px-6 py-4 font-semibold text-slate-650">
          ${cit.barangay}
        </td>

        <!-- Status Badge -->
        <td class="px-6 py-4">
          <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full border ${statusClass} inline-flex items-center gap-1.5">
            ${dotIndicator}
            <span>${cit.status}</span>
          </span>
        </td>

        <!-- Activity Logs -->
        <td class="px-6 py-4 space-y-1">
          <div class="flex items-center gap-1.5 text-slate-650">
            <span class="text-[9px] font-bold text-slate-400 uppercase">Registered:</span>
            <span class="font-semibold text-[11px]">${formattedRegDate}</span>
          </div>
          <div class="flex items-center gap-1.5 text-slate-650">
            <span class="text-[9px] font-bold text-slate-400 uppercase">Last Active:</span>
            <span class="font-semibold text-[11px]">${cit.lastLogin}</span>
          </div>
        </td>

        <!-- Actions -->
        <td class="px-6 py-4 text-right whitespace-nowrap">
          <div class="inline-flex items-center space-x-3">
            <!-- View Details icon -->
            <button onclick="if(typeof openViewModal === 'function') openViewModal('${cit.id}')" class="text-slate-400 hover:text-[#0f172a] hover:bg-slate-50 p-1.5 rounded-lg border border-transparent hover:border-slate-150 transition cursor-pointer" title="View Portal Profile">
              <i class="fa-solid fa-circle-info text-xs"></i>
            </button>

            ${window.canEditCitizenDirectory ? `
            <!-- Lock/Unlock button -->
            <button onclick="if(typeof openLockModal === 'function') openLockModal('${cit.id}')" class="border p-1.5 rounded-lg transition cursor-pointer flex items-center justify-center ${lockIcon}" title="${lockTitle}">
              <i class="fa-solid ${cit.status === 'Locked' ? 'fa-lock' : 'fa-lock-open'} text-xs"></i>
            </button>

            <!-- Reset password key icon -->
            <button onclick="if(typeof openResetModal === 'function') openResetModal('${cit.id}')" class="text-slate-400 hover:text-[#0f172a] hover:bg-slate-50 p-1.5 rounded-lg border border-transparent hover:border-slate-150 transition cursor-pointer" title="Reset Password Access">
              <i class="fa-solid fa-key text-xs"></i>
            </button>
            ` : ''}
          </div>
        </td>
      </tr>
    `;
    citizenTbody.innerHTML += row;
  });

  const pagText = document.getElementById('citizenPaginationText');
  if (pagText) pagText.innerText = `Showing 1 to ${list.length} of ${list.length} accounts`;

  hideCitizenSkeleton();
}

function hideCitizenSkeleton() {
  const skel = document.getElementById('citizenSkeleton');
  const real = document.getElementById('citizenRealContent');
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
