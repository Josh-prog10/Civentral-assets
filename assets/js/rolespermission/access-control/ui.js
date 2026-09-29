// ACCESS CONTROL UI

// DEPARTMENT ICON MAP
const deptIconMap = {
  'citizen': 'fa-comments',
  'permit': 'fa-file-invoice',
  'social': 'fa-handshake-angle',
  'health': 'fa-heart-pulse',
  'education': 'fa-graduation-cap',
  'scholarship': 'fa-graduation-cap',
  'disaster': 'fa-triangle-exclamation',
  'drrm': 'fa-triangle-exclamation',
  'finance': 'fa-coins',
  'admin': 'fa-building',
  'engineering': 'fa-hard-hat',
  'infrastructure': 'fa-road'
};

function getDeptIcon(name) {
  const lower = (name || '').toLowerCase();
  for (const [key, icon] of Object.entries(deptIconMap)) {
    if (lower.includes(key)) return icon;
  }
  return 'fa-building';
}

// RENDER SIDEBAR ROLE LIST
function renderRoles() {
  const roleListContainer = document.getElementById('scopeRoleSelectorList');
  if (!roleListContainer) return;
  roleListContainer.innerHTML = '';

  scopeRoles.forEach(role => {
    const isActive = role.role_id === window.selectedScopeRoleId;
    const scope = window.currentScopes[role.role_id] || {};

    let subtitleText = 'Access Boundary: Restricted';
    if (scope.globalMode) subtitleText = 'Access Boundary: Global System';
    else if (scope.deptLockin) subtitleText = 'Access Boundary: Single Department';
    else if (scope.departmentIds && scope.departmentIds.length > 0) subtitleText = `${scope.departmentIds.length} Department(s) Assigned`;

    const activeClasses = isActive
      ? 'border-l-4 border-brand-dark bg-brand-light font-black text-brand-dark shadow-xs'
      : 'border-l-4 border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-50 font-semibold';

    const card = document.createElement('button');
    card.type = 'button';
    card.className = `w-full text-left px-4 py-3 rounded-xl transition cursor-pointer flex items-center justify-between ${activeClasses}`;
    card.innerHTML = `
      <div class="space-y-0.5">
        <p class="text-xs font-bold leading-tight">${role.role_name}</p>
        <span class="text-[9px] text-slate-400 block">${subtitleText}</span>
      </div>
      <i class="fa-solid fa-chevron-right text-[8px] opacity-60"></i>
    `;
    card.onclick = () => { if (typeof selectRole === 'function') selectRole(role.role_id); };
    roleListContainer.appendChild(card);
  });
}

// RENDER DEPARTMENT CARDS GRID
function renderDeptCards() {
  const deptCardsGrid = document.getElementById('deptCardsGrid');
  if (!deptCardsGrid) return;
  deptCardsGrid.innerHTML = '';

  const roleId = window.selectedScopeRoleId;
  const current = (window.currentScopes && window.currentScopes[roleId]) ? window.currentScopes[roleId] : { globalMode: false, deptLockin: false, departmentIds: [] };

  if (departmentsList.length === 0) {
    deptCardsGrid.innerHTML = `
      <div class="col-span-2 text-center py-6 text-xs text-slate-400 font-semibold">
        <i class="fa-solid fa-building text-slate-300 text-xl block mb-2"></i>
        No departments found in the system.
      </div>
    `;
    return;
  }

  departmentsList.forEach(dept => {
    const isChecked = (current.departmentIds || []).includes(dept.department_id);

    const cardActiveStyles = isChecked
      ? 'border-brand-medium/50 bg-brand-light/40 text-brand-dark shadow-xs'
      : 'border-slate-200 bg-white hover:bg-slate-50/50 text-slate-650';

    const checkIndicator = isChecked
      ? 'bg-brand-medium border-brand-medium text-white'
      : 'border-slate-350 bg-white';

    const checkIconState = isChecked ? 'opacity-100' : 'opacity-0';
    const icon = getDeptIcon(dept.department_name);

    const card = document.createElement('div');
    card.className = `border rounded-xl p-4 flex items-center gap-3 transition cursor-pointer select-none ${cardActiveStyles}`;
    card.innerHTML = `
      <i class="fa-solid ${icon} text-slate-450 text-sm shrink-0"></i>
      <div class="flex-1 min-w-0">
        <p class="text-xs font-bold truncate">${dept.department_name}</p>
        ${dept.department_code ? `<span class="text-[9px] text-slate-400 block">${dept.department_code}</span>` : ''}
      </div>
      <div class="h-4.5 w-4.5 border rounded flex items-center justify-center text-[10px] shrink-0 transition ${checkIndicator}">
        <i class="fa-solid fa-check ${checkIconState}"></i>
      </div>
    `;
    card.onclick = () => { if (typeof toggleDeptCard === 'function') toggleDeptCard(dept.department_id); };
    deptCardsGrid.appendChild(card);
  });

  if (current.globalMode) {
    deptCardsGrid.classList.add('pointer-events-none', 'opacity-65');
  } else {
    deptCardsGrid.classList.remove('pointer-events-none', 'opacity-65');
  }
}

// SYNC TOGGLE SWITCHES
function syncToggles() {
  const switchGlobal = document.getElementById('toggleGlobalAccess');
  const switchLockin = document.getElementById('toggleDeptLockin');
  if (!switchGlobal || !switchLockin) return;
  
  const roleId = window.selectedScopeRoleId;
  const current = (window.currentScopes && window.currentScopes[roleId]) ? window.currentScopes[roleId] : { globalMode: false, deptLockin: false };

  switchGlobal.checked = current.globalMode;
  switchLockin.checked = current.deptLockin;

  if (current.globalMode) {
    switchLockin.disabled = true;
    switchLockin.parentElement.classList.add('opacity-50', 'cursor-not-allowed');
  } else {
    switchLockin.disabled = false;
    switchLockin.parentElement.classList.remove('opacity-50', 'cursor-not-allowed');
  }
}

// COMPILE & SYNC SSO TOKEN LIVE JSON PREVIEW
function syncTokenPreview() {
  const tokenJson = document.getElementById('tokenJsonCode');
  if (!tokenJson) return;

  const roleId = window.selectedScopeRoleId;
  const current = (window.currentScopes && window.currentScopes[roleId]) ? window.currentScopes[roleId] : { globalMode: false, deptLockin: false, departmentIds: [] };
  const roleObj = scopeRoles.find(r => r.role_id === roleId);

  const iatVal = Math.floor(Date.now() / 1000);
  const expVal = iatVal + 3600;

  const grantedDeptNames = current.globalMode
    ? ['*']
    : departmentsList
        .filter(d => current.departmentIds.includes(d.department_id))
        .map(d => d.department_name);

  const tokenPayload = {
    iss: "civentral-sso-gateway",
    sub: roleObj ? `civentral-role-${roleId}` : "civentral-role-anonymous",
    role: roleObj ? roleObj.role_name : 'Unknown',
    role_prefix: roleObj ? roleObj.role_prefix : '',
    access_mode: current.globalMode ? "global" : (current.deptLockin ? "dept_lockin" : "restricted"),
    department_lockin: current.deptLockin,
    is_system_role: roleObj ? !!roleObj.is_system_role : false,
    allowed_scopes: grantedDeptNames,
    iat: iatVal,
    exp: expVal
  };

  tokenJson.innerText = JSON.stringify(tokenPayload, null, 2);
}
