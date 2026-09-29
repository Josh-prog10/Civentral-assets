// ROLES MANAGEMENT FILTERS
var currentTabStatus = 'Active';
var currentPage = 1;
var pageSize = 10;

function switchStatusTab(targetStatus) {
  currentTabStatus = targetStatus;
  currentPage = 1;

  document.querySelectorAll('.status-tab-btn').forEach(btn => {
    const tabVal = btn.getAttribute('data-status-tab');
    if (tabVal === targetStatus) {
      btn.className = "status-tab-btn px-4 py-2.5 rounded-t-xl text-xs font-black transition-all cursor-pointer bg-white text-[#176B87] border-t-2 border-[#86B6F6] border-x border-slate-200/80 shadow-2xs";
    } else {
      btn.className = "status-tab-btn px-4 py-2.5 rounded-t-xl text-xs font-bold transition-all cursor-pointer text-slate-500 hover:text-slate-800 hover:bg-slate-100/60";
    }
  });

  filterRoles();
}

// FILTER ROLES IN REAL TIME
function filterRoles() {
  const searchInput = document.getElementById('roleSearchInput');
  const globalAccessFilter = document.getElementById('globalAccessFilterSelect');

  const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
  const globalVal = globalAccessFilter ? globalAccessFilter.value : 'ALL';

  const filtered = systemRoles.filter(role => {
    const matchesQuery = !query || 
                         (role.role_name || '').toLowerCase().includes(query) || 
                         (role.role_prefix || '').toLowerCase().includes(query) || 
                         (role.description || '').toLowerCase().includes(query) ||
                         (query === 'system' && role.is_system_role);

    const matchesGlobal = globalVal === 'ALL' || 
                          (globalVal === 'GLOBAL' && role.is_global_access) || 
                          (globalVal === 'DEPARTMENT' && !role.is_global_access);

    const rStatus = role.status || 'Active';
    const matchesStatus = currentTabStatus === 'ALL' || rStatus.toLowerCase() === currentTabStatus.toLowerCase();

    return matchesQuery && matchesGlobal && matchesStatus;
  });

  if (typeof renderRoles === 'function') renderRoles(filtered);
}

// INTERACTIVE METRIC CARD FILTER & NAVIGATION
function filterByCard(type) {
  const globalSelect = document.getElementById('globalAccessFilterSelect');
  const searchInputEl = document.getElementById('roleSearchInput');

  // Clear card highlight rings
  document.querySelectorAll('.role-metric-card').forEach(card => {
    card.classList.remove('ring-2', 'ring-cyan-500', 'ring-blue-500', 'ring-emerald-500', 'ring-amber-500', 'ring-brand-medium');
  });

  if (type === 'ALL') {
    if (globalSelect) globalSelect.value = 'ALL';
    if (searchInputEl) searchInputEl.value = '';
    switchStatusTab('ALL');
    const card = document.getElementById('cardTotalRoles');
    if (card) card.classList.add('ring-2', 'ring-cyan-500');
  } else if (type === 'GLOBAL') {
    if (globalSelect) globalSelect.value = 'GLOBAL';
    if (searchInputEl) searchInputEl.value = '';
    switchStatusTab('ALL');
    const card = document.getElementById('cardGlobalRoles');
    if (card) card.classList.add('ring-2', 'ring-blue-500');
  } else if (type === 'ACTIVE') {
    if (globalSelect) globalSelect.value = 'ALL';
    if (searchInputEl) searchInputEl.value = '';
    switchStatusTab('Active');
    const card = document.getElementById('cardActiveRoles');
    if (card) card.classList.add('ring-2', 'ring-emerald-500');
  } else if (type === 'SYSTEM') {
    if (globalSelect) globalSelect.value = 'ALL';
    if (searchInputEl) searchInputEl.value = 'system';
    switchStatusTab('ALL');
    const card = document.getElementById('cardSystemRoles');
    if (card) card.classList.add('ring-2', 'ring-amber-500');
  }

  // Scroll to table workspace smoothly
  const targetTable = document.getElementById('roleSearchInput');
  if (targetTable) {
    targetTable.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
}
