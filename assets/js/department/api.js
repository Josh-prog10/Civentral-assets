// Global Data
let departmentsData = [];  
let usersData = [];
var currentUserScope = null;       

// Fetch Departments
async function fetchDepartments() {
  try {
    const response = await fetch('../../api/employee/departments.php');
    const result = await response.json();

    if (result.status === 'success') {
      departmentsData = result.data || [];
      usersData = result.users || [];
      currentUserScope = result.current_user || null;

      populateAdminDropdown();
      renderDepts();
      updateSummary();
    } else {
      showToast(result.message || 'Error loading department data.');
    }
  } catch (err) {
    console.error('Error fetching departments:', err);
    showToast('Network error connecting TO DATABASE.');
  } finally {
    if (typeof hideDeptSkeleton === 'function') {
      hideDeptSkeleton();
    }
  }
}

// Toggle Status
async function handleDeptToggle(deptId, toggleEl) {
  const newStatus = toggleEl.checked ? 'Active' : 'Inactive';
  const dept = departmentsData.find(d => d.department_id === deptId);

  try {
    const response = await fetch('../../api/employee/departments.php', {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ department_id: deptId, status: newStatus })
    });

    const result = await response.json();
    if (result.status === 'success') {
      showToast(`Department "${dept ? dept.department_name : ''}" ${newStatus === 'Active' ? 'activated' : 'deactivated'}.`);
      await fetchDepartments();
    } else {
      showToast(result.message || 'Failed to update department status.');
      toggleEl.checked = !toggleEl.checked; // Revert toggle
    }
  } catch (err) {
    console.error('Error toggling status:', err);
    showToast('Error updating department status.');
    toggleEl.checked = !toggleEl.checked;
  }
}
