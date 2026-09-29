// PERMISSIONS EVENTS

// HANDLE SWITCHING ACTIVE ROLES
function selectRole(roleId) {
  if (window.isDirty) {
    const confirmLeave = confirm("You have unsaved changes in the permission assignments. Discard changes and switch roles?");
    if (!confirmLeave) return;
  }

  window.selectedRoleId = roleId;
  window.currentPermissions = JSON.parse(JSON.stringify(window.savedPermissions));
  if (typeof setDirtyState === 'function') setDirtyState(false);

  const currentRoleObj = rolesData.find(r => r.role_id === roleId);
  const roleNameDisplay = currentRoleObj ? currentRoleObj.role_name : 'Role';

  const editHeader = document.getElementById('editingRoleHeader');
  if (editHeader) {
    editHeader.innerText = `Editing Permissions for: ${roleNameDisplay}`;
  }
  
  const searchInput = document.getElementById('roleSearchInput');
  if (typeof renderRoleSelector === 'function') renderRoleSelector(searchInput ? searchInput.value : '');
  if (typeof renderAccordions === 'function') renderAccordions();
  if (typeof renderRestrictions === 'function') renderRestrictions();
}

// TOGGLE MODULE COLLAPSE STATE
function toggleModuleCollapse(moduleName) {
  window.expandedModules[moduleName] = !window.expandedModules[moduleName];
  if (typeof renderAccordions === 'function') renderAccordions();
}

// TOGGLE FEATURE ENABLE / DISABLE FOR A RESOURCE
function toggleFeatureEnable(chk) {
  const resourceId = parseInt(chk.getAttribute('data-resource-toggle'));
  const isChecked = chk.checked;
  const roleId = window.selectedRoleId;

  if (!window.currentPermissions[roleId]) {
    window.currentPermissions[roleId] = {};
  }

  if (isChecked) {
    // Enable Feature: Default VIEW (Read) action checked automatically
    const viewAction = actionsData.find(a => 
      (a.action_name || '').toUpperCase() === 'VIEW' || 
      (a.action_name || '').toUpperCase() === 'READ'
    );
    const defaultActionId = viewAction ? viewAction.action_id : (actionsData[0] ? actionsData[0].action_id : null);

    if (defaultActionId) {
      window.currentPermissions[roleId][resourceId] = [defaultActionId];
    } else {
      window.currentPermissions[roleId][resourceId] = [];
    }
  } else {
    // Disable Feature: Uncheck all actions for this resource
    window.currentPermissions[roleId][resourceId] = [];
  }

  if (typeof setDirtyState === 'function') setDirtyState(true);
  if (typeof renderAccordions === 'function') renderAccordions();
  if (typeof renderRestrictions === 'function') renderRestrictions();
}

// TOGGLE SINGLE CHECKBOX FOR A RESOURCE ACTION
function toggleResourcePermission(chk) {
  const resourceId = parseInt(chk.getAttribute('data-resource'));
  const actionId = parseInt(chk.getAttribute('data-action'));
  const roleId = window.selectedRoleId;

  if (!window.currentPermissions[roleId]) {
    window.currentPermissions[roleId] = {};
  }
  if (!window.currentPermissions[roleId][resourceId]) {
    window.currentPermissions[roleId][resourceId] = [];
  }

  const resourcePerms = window.currentPermissions[roleId][resourceId];

  if (chk.checked) {
    if (!resourcePerms.includes(actionId)) {
      resourcePerms.push(actionId);
    }
  } else {
    window.currentPermissions[roleId][resourceId] = resourcePerms.filter(a => a !== actionId);
  }

  let parentMod = null;
  for (let mod of modulesData) {
    if (mod.resources.some(res => res.id === resourceId)) {
      parentMod = mod;
      break;
    }
  }

  if (parentMod) {
    const masterChk = document.querySelector(`input[data-module-master="${parentMod.name}"]`);
    if (masterChk) {
      if (typeof isModuleMasterChecked === 'function') {
        masterChk.checked = isModuleMasterChecked(parentMod, window.currentPermissions[roleId]);
      }
    }
  }

  if (typeof checkDirtyState === 'function') checkDirtyState();
}

// TOGGLE ALL MODULE CHECKBOXES AT ONCE VIA MASTER ACCESS CHECKBOX
function toggleModuleMaster(masterChk) {
  const moduleName = masterChk.getAttribute('data-module-master');
  const roleId = window.selectedRoleId;

  const modObj = modulesData.find(m => m.name === moduleName);
  if (!modObj) return;

  if (!window.currentPermissions[roleId]) {
    window.currentPermissions[roleId] = {};
  }

  const isSuperAdmin = (typeof window.currentUserIsSuperAdmin !== 'undefined')
    ? window.currentUserIsSuperAdmin
    : (currentUserScope ? (!!currentUserScope.is_superadmin || !!currentUserScope.is_global_access) : false);

  const userGrantedRes = (typeof window.currentUserGrantedResources !== 'undefined' && Array.isArray(window.currentUserGrantedResources))
    ? window.currentUserGrantedResources
    : (currentUserScope ? (currentUserScope.granted_resources || []) : []);

  const userGrantedActions = (typeof window.currentUserGrantedActions !== 'undefined' && Array.isArray(window.currentUserGrantedActions))
    ? window.currentUserGrantedActions
    : (currentUserScope ? (currentUserScope.granted_actions || []) : []);

  modObj.resources.forEach(res => {
    if (!isSuperAdmin && typeof isResourceGrantedToCurrentUser === 'function' && !isResourceGrantedToCurrentUser(res, isSuperAdmin, userGrantedRes)) {
      return; // Skip resources not granted to logged-in user
    }

    if (masterChk.checked) {
      const supportedActionIds = actionsData
        .filter(a => {
          const isSupported = (typeof isActionSupportedForResource === 'function') ? isActionSupportedForResource(res, a) : true;
          const isGranted = (typeof isActionGrantedToCurrentUser === 'function') ? isActionGrantedToCurrentUser(a, isSuperAdmin, userGrantedActions) : true;
          return isSupported && isGranted;
        })
        .map(a => a.action_id);
      window.currentPermissions[roleId][res.id] = supportedActionIds;
    } else {
      window.currentPermissions[roleId][res.id] = [];
    }
  });

  if (typeof renderAccordions === 'function') renderAccordions();
  if (typeof checkDirtyState === 'function') checkDirtyState();
}

// ACTION: DISCARD UNSAVED CHANGES
function discardChanges() {
  window.currentPermissions = JSON.parse(JSON.stringify(window.savedPermissions));
  if (typeof setDirtyState === 'function') setDirtyState(false);
  if (typeof renderAccordions === 'function') renderAccordions();
  if (typeof showToast === 'function') showToast("Unsaved changes discarded.");
}

// ACTION: RESET ACTIVE ROLE TO EMPTY PERMISSIONS
function resetToDefaults() {
  const roleId = window.selectedRoleId;
  if (roleId && window.currentPermissions[roleId]) {
    window.currentPermissions[roleId] = {};
    if (typeof renderAccordions === 'function') renderAccordions();
    if (typeof checkDirtyState === 'function') checkDirtyState();
    if (typeof showToast === 'function') showToast("Reset role permissions to empty.");
  }
}
