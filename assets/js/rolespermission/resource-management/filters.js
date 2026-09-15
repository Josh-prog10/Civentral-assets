// FILTER RESOURCES REAL TIME
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

  filterResources();
}

function filterResources() {
  const searchInput = document.getElementById('resourceSearchInput');
  const parentFilter = document.getElementById('parentModuleFilter');

  const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
  const selectedModule = parentFilter ? parentFilter.value : 'ALL';

  const filtered = systemResources.filter(res => {
    const matchesQuery = res.name.toLowerCase().includes(query) || 
                         res.route.toLowerCase().includes(query) ||
                         res.module.toLowerCase().includes(query) ||
                         (res.desc && res.desc.toLowerCase().includes(query));

    const matchesModule = selectedModule === 'ALL' || 
                          String(res.module_id) === String(selectedModule) || 
                          res.module === selectedModule;

    const rStatus = res.status || 'Active';
    const matchesStatus = currentTabStatus === 'ALL' || rStatus.toLowerCase() === currentTabStatus.toLowerCase();

    return matchesQuery && matchesModule && matchesStatus;
  });

  if (typeof renderResourcesTable === 'function') renderResourcesTable(filtered);
}

window.switchStatusTab = switchStatusTab;
window.filterResources = filterResources;
