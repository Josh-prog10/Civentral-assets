// Handle Form Save
async function handleSaveDept(e) {
  e.preventDefault();

  const idRef = document.getElementById('deptIdRef').value;
  const name = document.getElementById('deptName').value.trim();
  const code = document.getElementById('deptCode').value.trim().toUpperCase();
  const adminUserId = document.getElementById('deptAdmin').value;
  const desc = document.getElementById('deptDesc').value.trim();

  const payload = {
    department_name: name,
    department_code: code,
    description: desc,
    department_head_user_id: adminUserId ? parseInt(adminUserId) : null
  };

  const isEdit = idRef !== '';
  if (isEdit) payload.department_id = parseInt(idRef);

  try {
    const response = await fetch('../../api/employee/departments.php', {
      method: isEdit ? 'PUT' : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const result = await response.json();
    if (result.status === 'success') {
      showToast(result.message || 'Department saved successfully.');
      closeModal('deptModal');
      await fetchDepartments();
    } else {
      showToast(result.message || 'Failed to save department.');
    }
  } catch (err) {
    console.error('Error saving department:', err);
    showToast('Failed to save department TO DATABASE.');
  }
}
