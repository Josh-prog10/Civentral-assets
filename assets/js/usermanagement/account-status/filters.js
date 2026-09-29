// ACCOUNT STATUS FILTERS

// FILTER & SEARCH LOGIC COMBINATION
function filterAndSearch() {
  const searchInput = document.getElementById('statusSearchInput');
  if (!searchInput) return;
  const query = searchInput.value.toLowerCase().trim();
  const activeTab = window.activeStatusTab;

  const filtered = accountUsers.filter(user => {
    const matchesSearch = user.name.toLowerCase().includes(query) ||
                          user.email.toLowerCase().includes(query) ||
                          user.id.toLowerCase().includes(query) ||
                          user.dept.toLowerCase().includes(query) ||
                          user.position.toLowerCase().includes(query);
    
    let matchesTab = true;
    if (activeTab !== 'all') {
      if (activeTab === 'Deactivated') {
        matchesTab = user.status === 'Deactivated' || user.status === 'Inactive';
      } else {
        matchesTab = user.status === activeTab;
      }
    }

    return matchesSearch && matchesTab;
  });

  if (typeof renderTable === 'function') renderTable(filtered);
}

// TAB SWITCH EVENT HANDLER
function switchTab(btn, tabType) {
  document.querySelectorAll('.status-tab').forEach(tab => {
    tab.className = "status-tab border-b-2 border-transparent hover:text-slate-800 pb-3 px-1 flex items-center gap-2 cursor-pointer transition";
  });

  btn.className = "status-tab border-b-2 border-brand-dark text-brand-dark pb-3 px-1 flex items-center gap-2 cursor-pointer transition";
  window.activeStatusTab = tabType;
  
  filterAndSearch();
}
