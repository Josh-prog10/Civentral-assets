// USER DIRECTORY FILTERS

// FILTER & SEARCH HANDLER
function filterAndSearch() {
  const searchInput = document.getElementById('searchInput');
  const roleFilter = document.getElementById('roleFilter');
  const deptFilter = document.getElementById('deptFilter');

  const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
  const roleVal = roleFilter ? roleFilter.value : '';
  const deptVal = deptFilter ? deptFilter.value : '';

  const filtered = systemUsers.filter(user => {
    const fullName = (typeof getUserFullName === 'function' ? getUserFullName(user) : '').toLowerCase();
    const email = (user.email || '').toLowerCase();
    const empId = (user.employee_id || '').toLowerCase();
    const posName = (user.positions ? user.positions.position_name : '').toLowerCase();
    const userStatus = (user.status || '').toLowerCase();
    const userRoleName = (user.roles ? user.roles.role_name : '').toLowerCase();

    const matchQuery = !query || 
                       fullName.includes(query) || 
                       email.includes(query) || 
                       empId.includes(query) ||
                       posName.includes(query) ||
                       userStatus.includes(query) ||
                       userRoleName.includes(query);

    const userRole = user.roles ? user.roles.role_name : '';
    const matchRole = roleVal === '' || userRole === roleVal;

    const userDept = (user.positions && user.positions.departments) ? user.positions.departments.department_name : '';
    const matchDept = deptVal === '' || userDept === deptVal;

    return matchQuery && matchRole && matchDept;
  });

  if (typeof renderTable === 'function') renderTable(filtered);
}

// INTERACTIVE USER METRIC CARDS FILTER & FILE REDIRECT HANDLER
function filterUserCard(type) {
  const searchInputEl = document.getElementById('searchInput');
  const roleFilterEl = document.getElementById('roleFilter');
  const deptFilterEl = document.getElementById('deptFilter');

  // Reset highlight ring on user metric cards
  document.querySelectorAll('.user-metric-card').forEach(card => {
    card.classList.remove('ring-2', 'ring-cyan-500', 'ring-emerald-500', 'ring-rose-500', 'ring-blue-500');
  });

  if (type === 'ALL') {
    if (searchInputEl) searchInputEl.value = '';
    if (roleFilterEl) roleFilterEl.value = '';
    if (deptFilterEl) deptFilterEl.value = '';
    const card = document.getElementById('cardTotalUsers');
    if (card) card.classList.add('ring-2', 'ring-cyan-500');
    filterAndSearch();
  } else if (type === 'ACTIVE') {
    if (searchInputEl) searchInputEl.value = 'Active';
    if (roleFilterEl) roleFilterEl.value = '';
    if (deptFilterEl) deptFilterEl.value = '';
    const card = document.getElementById('cardActiveUsers');
    if (card) card.classList.add('ring-2', 'ring-emerald-500');
    filterAndSearch();
  } else if (type === 'DEACTIVATED') {
    // Redirect to account-status.php file for status suspension management
    window.location.href = 'account-status.php';
    return;
  } else if (type === 'DEPTS') {
    // Redirect to department management directory file
    window.location.href = '../department/departments.php';
    return;
  }

  // Scroll smoothly to search/table area
  if (searchInputEl) {
    searchInputEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
}
