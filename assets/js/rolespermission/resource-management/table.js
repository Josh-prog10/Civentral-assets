// RENDER RESOURCES DATATABLE
function renderResourcesTable(dataToRender = systemResources) {
  const tableBody = document.getElementById('resourceTableBody');
  const emptyState = document.getElementById('emptyTableState');
  if (!tableBody) return;

  tableBody.innerHTML = '';

  const totalFiltered = dataToRender.length;
  const totalPages = Math.ceil(totalFiltered / pageSize) || 1;
  if (currentPage > totalPages) currentPage = totalPages;
  if (currentPage < 1) currentPage = 1;

  const startIndex = (currentPage - 1) * pageSize;
  const endIndex = Math.min(startIndex + pageSize, totalFiltered);
  const pageData = dataToRender.slice(startIndex, endIndex);

  if (totalFiltered === 0) {
    if (emptyState) emptyState.classList.remove('hidden');
    renderPaginationUI(0, 0, 0, 1);
    updateResourceMetrics();
    return;
  } else {
    if (emptyState) emptyState.classList.add('hidden');
  }

  // Badge Color Map for Parent Modules
  const moduleBadgeMap = {
    "User Management": "bg-purple-50 text-purple-700 border-purple-200",
    "Citizen Management": "bg-indigo-50 text-indigo-700 border-indigo-200",
    "Education & Scholarship": "bg-blue-50 text-blue-700 border-blue-200",
    "Health Services": "bg-rose-50 text-rose-700 border-rose-200",
    "BPLO Licensing & Permits": "bg-teal-50 text-teal-700 border-teal-200",
    "DRRM Dispatch & Emergency": "bg-amber-50 text-amber-700 border-amber-200",
    "Reports & Analytics": "bg-emerald-50 text-emerald-700 border-emerald-200",
    "System Settings": "bg-slate-100 text-slate-700 border-slate-200",
    "Legacy Cashiering": "bg-slate-100 text-slate-600 border-slate-200",
    "Archived Portal Gateway": "bg-amber-50 text-amber-700 border-amber-200"
  };

  const isSuperAdmin = currentUserScope ? !!currentUserScope.is_superadmin : false;
  const grantedActions = currentUserScope ? (currentUserScope.granted_actions || []) : [];
  const canEdit = isSuperAdmin || grantedActions.includes('EDIT');

  pageData.forEach(res => {
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-slate-50/60 transition group';

    const badgeStyle = moduleBadgeMap[res.module] || 'bg-slate-100 text-slate-700 border-slate-200';

    let statusBadgeHtml = '';
    const rStatus = res.status || 'Active';
    if (rStatus === 'Active') {
      statusBadgeHtml = `
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
          <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
          Active
        </span>
      `;
    } else if (rStatus === 'Inactive') {
      statusBadgeHtml = `
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-slate-100 text-slate-600 border border-slate-200">
          <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
          Inactive
        </span>
      `;
    } else {
      statusBadgeHtml = `
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-50 text-amber-700 border border-amber-200">
          <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
          Archived
        </span>
      `;
    }

    const isChecked = rStatus === 'Active';
    const isArchived = rStatus === 'Archived';

    tr.innerHTML = `
      <td class="px-6 py-4">
        <div class="space-y-0.5">
          <span class="font-extrabold text-slate-900 tracking-tight block text-xs">${res.name}</span>
          <code class="text-[10px] font-mono text-slate-500 bg-slate-50 px-1.5 py-0.5 rounded border border-slate-200/60 block max-w-xs truncate" title="${res.route}">${res.route}</code>
        </div>
      </td>

      <td class="px-6 py-4">
        <span class="inline-block px-2.5 py-1 rounded-lg text-[10px] font-bold border ${badgeStyle}">
          ${res.module}
        </span>
      </td>

      <td class="px-6 py-4 max-w-xs">
        <p class="text-xs text-slate-600 font-medium leading-relaxed">${res.desc || '<span class="text-slate-400 italic">No description</span>'}</p>
      </td>

      <td class="px-6 py-4 text-center">
        ${statusBadgeHtml}
      </td>

      <td class="px-6 py-4 text-slate-500 font-mono text-[10px] font-bold">
        ${res.created_at}
      </td>

      <td class="px-6 py-4 text-slate-500 font-mono text-[10px] font-bold">
        ${res.updated_at}
      </td>

      <td class="px-6 py-4 text-right">
        <div class="flex items-center justify-end gap-3">
          ${canEdit ? `
          <!-- iOS Toggle Switch -->
          <label class="relative inline-flex items-center cursor-pointer ${isArchived ? 'opacity-50 pointer-events-none' : ''}" title="Activate/Deactivate Resource">
            <input 
              type="checkbox" 
              ${isChecked ? 'checked' : ''} 
              onchange="toggleResourceStatus(${res.id})" 
              class="sr-only peer"
            >
            <div class="w-8 h-4.5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-emerald-600"></div>
          </label>

          <!-- Edit Button -->
          <button 
            type="button" 
            onclick="openEditResourceModal(${res.id})" 
            class="h-8 w-8 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-600 hover:text-brand-dark flex items-center justify-center transition cursor-pointer"
            title="Edit Resource"
          >
            <i class="fa-solid fa-pen-to-square text-xs"></i>
          </button>

          <!-- Archive Button -->
          <button 
            type="button" 
            onclick="openArchiveResourceModal(${res.id})" 
            class="h-8 w-8 rounded-lg border border-slate-200 hover:bg-amber-50 text-slate-400 hover:text-amber-600 flex items-center justify-center transition cursor-pointer ${isArchived ? 'opacity-40 cursor-not-allowed' : ''}"
            ${isArchived ? 'disabled' : ''}
            title="Archive Resource"
          >
            <i class="fa-solid fa-box-archive text-xs"></i>
          </button>` : `<span class="text-[10px] text-slate-400 font-bold italic">Read-only</span>`}
        </div>
      </td>
    `;

    tableBody.appendChild(tr);
  });

  renderPaginationUI(startIndex, endIndex, totalFiltered, totalPages);
  updateResourceMetrics();
}

function renderPaginationUI(startIndex, endIndex, totalFiltered, totalPages) {
  const paginationEl = document.getElementById('paginationText');
  const controlsEl = document.getElementById('paginationControls');

  if (paginationEl) {
    if (totalFiltered === 0) {
      paginationEl.innerText = "Showing 0 to 0 of 0 resources";
    } else {
      paginationEl.innerText = `Showing ${startIndex + 1} to ${endIndex} of ${totalFiltered} resources`;
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
  prevBtn.onclick = () => { if (currentPage > 1) { currentPage--; filterResources(); } };
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
    pBtn.onclick = () => { currentPage = i; filterResources(); };
    controlsEl.appendChild(pBtn);
  }

  // Next Button
  const nextBtn = document.createElement('button');
  nextBtn.className = `px-3 py-1.5 rounded-lg border text-xs font-bold transition flex items-center justify-center ${currentPage === totalPages ? 'border-slate-200 bg-white text-slate-300 cursor-not-allowed' : 'border-slate-200 bg-white hover:bg-slate-50 text-slate-600 cursor-pointer'}`;
  nextBtn.disabled = currentPage === totalPages;
  nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right text-[9px]"></i>';
  nextBtn.onclick = () => { if (currentPage < totalPages) { currentPage++; filterResources(); } };
  controlsEl.appendChild(nextBtn);
}

// UPDATE METRIC CARDS AND TAB COUNTS
function updateResourceMetrics() {
  const total = systemResources.length;
  const activeCount = systemResources.filter(r => (r.status || 'Active') === 'Active').length;
  const inactiveCount = systemResources.filter(r => (r.status || '').toLowerCase() === 'inactive').length;
  const archivedCount = systemResources.filter(r => (r.status || '').toLowerCase() === 'archived').length;

  const totalEl = document.getElementById('metricTotalResources');
  const activeEl = document.getElementById('metricActiveResources');
  const inactiveEl = document.getElementById('metricInactiveResources');

  if (totalEl) totalEl.textContent = total;
  if (activeEl) activeEl.textContent = activeCount;
  if (inactiveEl) inactiveEl.textContent = inactiveCount + archivedCount;

  // Update tab counts
  const tabActive = document.getElementById('tabCountActive');
  const tabInactive = document.getElementById('tabCountInactive');
  const tabArchived = document.getElementById('tabCountArchived');
  const tabAll = document.getElementById('tabCountAll');

  if (tabActive) tabActive.innerText = activeCount;
  if (tabInactive) tabInactive.innerText = inactiveCount;
  if (tabArchived) tabArchived.innerText = archivedCount;
  if (tabAll) tabAll.innerText = total;

  hideResourceSkeleton();
}

function hideResourceSkeleton() {
  const skel = document.getElementById('resourceSkeleton');
  const real = document.getElementById('resourceRealContent');
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
