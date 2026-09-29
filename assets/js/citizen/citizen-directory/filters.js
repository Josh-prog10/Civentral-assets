// CITIZEN DIRECTORY FILTERS

// DYNAMIC ADVANCED FILTER CONTROLLER
function filterCitizenTable() {
  const citizenSearchInput = document.getElementById('citizenSearch');
  const statusFilterSelect = document.getElementById('statusFilter');
  const barangayFilterSelect = document.getElementById('barangayFilter');
  const dateFilterInput = document.getElementById('dateFilter');

  const query = citizenSearchInput ? citizenSearchInput.value.toLowerCase().trim() : '';
  const filterStatus = statusFilterSelect ? statusFilterSelect.value : 'All';
  const filterBarangay = barangayFilterSelect ? barangayFilterSelect.value : 'All';
  const filterDate = dateFilterInput ? dateFilterInput.value : '';

  const filtered = mockCitizens.filter(cit => {
    const matchesSearch = cit.id.toLowerCase().includes(query) ||
                          cit.name.toLowerCase().includes(query) ||
                          cit.email.toLowerCase().includes(query);
    
    let matchesStatus = true;
    if (filterStatus !== 'All') {
      matchesStatus = cit.status === filterStatus;
    }

    let matchesBarangay = true;
    if (filterBarangay !== 'All') {
      matchesBarangay = cit.barangay === filterBarangay;
    }

    let matchesDate = true;
    if (filterDate !== '') {
      matchesDate = cit.regDate === filterDate;
    }

    return matchesSearch && matchesStatus && matchesBarangay && matchesDate;
  });

  if (typeof renderCitizens === 'function') renderCitizens(filtered);
}

function resetFilters() {
  const citizenSearchInput = document.getElementById('citizenSearch');
  const statusFilterSelect = document.getElementById('statusFilter');
  const barangayFilterSelect = document.getElementById('barangayFilter');
  const dateFilterInput = document.getElementById('dateFilter');

  if (citizenSearchInput) citizenSearchInput.value = '';
  if (statusFilterSelect) statusFilterSelect.value = 'All';
  if (barangayFilterSelect) barangayFilterSelect.value = 'All';
  if (dateFilterInput) dateFilterInput.value = '';
  
  filterCitizenTable();
  if (typeof showToast === 'function') showToast("Filter parameters cleared.");
}
