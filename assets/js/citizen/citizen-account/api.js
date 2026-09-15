// CITIZEN ACCOUNT API

var ctrlCitizens = [];

async function fetchControlCitizens() {
  try {
    const response = await fetch('../../api/citizen/get-accounts.php');
    if (!response.ok) {
      throw new Error('Failed to reach get-accounts endpoint');
    }

    const result = await response.json();
    if (result.status === 'success') {
      ctrlCitizens = result.data;
      if (typeof renderControlTable === 'function') {
        renderControlTable(ctrlCitizens);
      }
      if (typeof updateControlStats === 'function') {
        updateControlStats();
      }
    } else {
      if (typeof showToast === 'function') showToast(result.message || 'Failed to load account records.', true);
    }
  } catch (error) {
    console.error('Error fetching citizen accounts:', error);
    if (typeof showToast === 'function') showToast('An error occurred while loading account records.', true);
  } finally {
    if (typeof hideCitizenAccountSkeleton === 'function') {
      hideCitizenAccountSkeleton();
    }
  }
}

// CSV EXPORT LOG TIMELINE
function exportControlCsv() {
  const searchInput = document.getElementById('ctrlSearchInput');
  const statusFilter = document.getElementById('ctrlStatusFilter');
  const flaggedFilter = document.getElementById('flaggedFilter');

  const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
  const statusVal = statusFilter ? statusFilter.value : 'All';
  const showFlagged = flaggedFilter ? flaggedFilter.checked : false;

  let csvContent = "data:text/csv;charset=utf-8,";
  csvContent += "Citizen ID,Full Name,Email Address,Current Security Status,Violations Log\r\n";

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

  filtered.forEach(cit => {
    const row = `"${cit.id}","${cit.name}","${cit.email}","${cit.status}","${cit.violations}"`;
    csvContent += row + "\r\n";
  });

  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", `CIVENTRAL_Security_Audit_Logs_${new Date().toISOString().split('T')[0]}.csv`);
  document.body.appendChild(link);
  
  link.click();
  document.body.removeChild(link);

  if (typeof showToast === 'function') showToast(`Successfully compiled and exported ${filtered.length} security records.`);
}
