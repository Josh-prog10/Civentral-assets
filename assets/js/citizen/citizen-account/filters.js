// CITIZEN ACCOUNT FILTERS

// MULTI-FILTER COMPILER
function filterControlTable() {
  const searchInput = document.getElementById('ctrlSearchInput');
  const statusFilter = document.getElementById('ctrlStatusFilter');
  const flaggedFilter = document.getElementById('flaggedFilter');

  const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
  const statusVal = statusFilter ? statusFilter.value : 'All';
  const showFlagged = flaggedFilter ? flaggedFilter.checked : false;

  const filtered = ctrlCitizens.filter(cit => {
    const matchesSearch = cit.id.toLowerCase().includes(query) ||
                          cit.name.toLowerCase().includes(query) ||
                          cit.email.toLowerCase().includes(query);

    let matchesStatus = true;
    if (statusVal !== 'All') {
      matchesStatus = cit.status === statusVal;
    }

    let matchesFlagged = true;
    if (showFlagged) {
      matchesFlagged = cit.flagged === true;
    }

    return matchesSearch && matchesStatus && matchesFlagged;
  });

  if (typeof renderControlTable === 'function') renderControlTable(filtered);
}

// CLEAR FILTER PARAMS
function resetControlFilters() {
  const searchInput = document.getElementById('ctrlSearchInput');
  const statusFilter = document.getElementById('ctrlStatusFilter');
  const flaggedFilter = document.getElementById('flaggedFilter');

  if (searchInput) searchInput.value = '';
  if (statusFilter) statusFilter.value = 'All';
  if (flaggedFilter) flaggedFilter.checked = false;

  filterControlTable();
  if (typeof showToast === 'function') showToast("Filter parameters cleared.");
}
