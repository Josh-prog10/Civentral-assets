// PERMISSIONS STATE MANAGEMENT

// CHECK IF ALL ACTION CHECKBOXES IN A MODULE ARE ENABLED
function isModuleMasterChecked(moduleObj, rolePermissions) {
  if (!moduleObj.resources || moduleObj.resources.length === 0) return false;
  return moduleObj.resources.every(res => {
    const resourcePerms = rolePermissions[res.id] || [];
    return actionsData.length > 0 && actionsData.every(action => resourcePerms.includes(action.action_id));
  });
}

// CHECK AND MANAGE IS-DIRTY STATE
function checkDirtyState() {
  const originalStr = JSON.stringify(window.savedPermissions);
  const currentStr = JSON.stringify(window.currentPermissions);
  
  const isDiff = originalStr !== currentStr;
  setDirtyState(isDiff);
}

function setDirtyState(isDirty) {
  window.isDirty = isDirty;
  const dirtyBanner = document.getElementById('dirtyBanner');
  if (!dirtyBanner) return;
  
  if (isDirty) {
    dirtyBanner.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-4');
    dirtyBanner.classList.add('opacity-100', 'pointer-events-auto', 'translate-y-0');
  } else {
    dirtyBanner.classList.remove('opacity-100', 'pointer-events-auto', 'translate-y-0');
    dirtyBanner.classList.add('opacity-0', 'pointer-events-none', 'translate-y-4');
  }
}
