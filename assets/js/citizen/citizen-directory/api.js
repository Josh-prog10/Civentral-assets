// CITIZEN DIRECTORY API

var mockCitizens = [];

// STATE MANAGEMENT
window.pendingCitizenId = null;

async function fetchCitizens() {
  try {
    // We use absolute path to ensure it always hits the correct endpoint
    const response = await fetch('../../api/citizen/get-directory.php');
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }
    const result = await response.json();
    if (result.status === 'success') {
      mockCitizens = result.data;
      if (typeof renderCitizens === 'function') {
        renderCitizens(mockCitizens);
      }
    } else {
      if (typeof showToast === 'function') showToast(result.message || 'Failed to fetch citizens.', true);
    }
  } catch (error) {
    console.error('Error fetching citizens:', error);
    if (typeof showToast === 'function') showToast('An error occurred while fetching citizens.', true);
  } finally {
    if (typeof hideCitizenSkeleton === 'function') {
      hideCitizenSkeleton();
    }
  }
}

function exportCitizensCsv() {
  const citizenSearchInput = document.getElementById('citizenSearch');
  const statusFilterSelect = document.getElementById('statusFilter');
  const barangayFilterSelect = document.getElementById('barangayFilter');
  const dateFilterInput = document.getElementById('dateFilter');

  const query = citizenSearchInput ? citizenSearchInput.value.toLowerCase().trim() : '';
  const filterStatus = statusFilterSelect ? statusFilterSelect.value : 'All';
  const filterBarangay = barangayFilterSelect ? barangayFilterSelect.value : 'All';
  const filterDate = dateFilterInput ? dateFilterInput.value : '';

  let csvContent = "data:text/csv;charset=utf-8,";
  csvContent += "Citizen ID,Full Name,Email Address,Barangay,Account Status,Reg Date,Last Login\r\n";

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

  filtered.forEach(cit => {
    const row = `"${cit.id}","${cit.name}","${cit.email}","${cit.barangay}","${cit.status}","${cit.regDate}","${cit.lastLogin}"`;
    csvContent += row + "\r\n";
  });

  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", `CIVENTRAL_Citizen_Directory_Export_${new Date().toISOString().split('T')[0]}.csv`);
  document.body.appendChild(link);
  
  link.click();
  document.body.removeChild(link);

  if (typeof showToast === 'function') showToast(`Successfully compiled and exported ${filtered.length} citizen profiles.`);
}
