// CITIZEN ACCOUNT EVENTS

// A. TRIGGER SECURITY STATE CHANGE MODAL
function triggerStateChange(citId, selectEl) {
  const action = selectEl.value;
  if (action === '') return;

  const cit = ctrlCitizens.find(c => c.id === citId);
  if (!cit) return;

  // Store variables in hidden inputs
  const mTargetId = document.getElementById('stateTargetId');
  const mTargetAction = document.getElementById('stateTargetAction');
  const mReason = document.getElementById('stateReason');
  
  if (mTargetId) mTargetId.value = citId;
  if (mTargetAction) mTargetAction.value = action;
  if (mReason) mReason.value = ''; // Reset reason

  // Select UI Elements to update in modal
  const mTitle = document.getElementById('stateModalTitle');
  const mHeader = document.getElementById('stateHeader');
  const mText = document.getElementById('stateText');
  const confirmBtn = document.getElementById('stateConfirmBtn');
  const iconBox = document.getElementById('stateIconBox');

  if (mTitle) mTitle.innerText = `${action} Citizen Credentials`;
  if (mHeader) mHeader.innerText = `${action} ${cit.name}'s Profile?`;

  // Mapped styling based on chosen action
  if (action === 'Activate' || action === 'Unlock' || action === 'Restore') {
    if (confirmBtn) confirmBtn.className = "bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2 rounded-xl text-xs transition cursor-pointer shadow-xs";
    if (iconBox) {
      iconBox.className = "h-10 w-10 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0";
      iconBox.innerHTML = '<i class="fa-solid fa-circle-check text-base animate-pulse"></i>';
    }
    if (mText) mText.innerText = `Confirm releasing lockouts. This grants immediate Caloocan portal access credentials.`;
  } else if (action === 'Deactivate') {
    if (confirmBtn) confirmBtn.className = "bg-slate-700 hover:bg-slate-800 text-white font-bold px-4 py-2 rounded-xl text-xs transition cursor-pointer shadow-xs";
    if (iconBox) {
      iconBox.className = "h-10 w-10 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600 shrink-0";
      iconBox.innerHTML = '<i class="fa-solid fa-circle-minus text-base"></i>';
    }
    if (mText) mText.innerText = `Confirm deactivating profile. Access remains blocked until reactivated manually.`;
  } else if (action === 'Lock' || action === 'Suspend') {
    if (confirmBtn) confirmBtn.className = "bg-rose-600 hover:bg-rose-700 text-white font-bold px-4 py-2 rounded-xl text-xs transition cursor-pointer shadow-xs";
    if (iconBox) {
      iconBox.className = "h-10 w-10 rounded-full bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-500 shrink-0";
      iconBox.innerHTML = action === 'Lock' ? '<i class="fa-solid fa-lock text-base"></i>' : '<i class="fa-solid fa-triangle-exclamation text-base"></i>';
    }
    if (mText) mText.innerText = `Confirm placing security holds on this account. All logins will fail validation tests immediately.`;
  }

  if (typeof openModal === 'function') openModal('stateChangeModal');
  selectEl.value = ''; // Reset select tag value
}

// B. EXECUTE AND CONFIRM ACTION
async function handleConfirmStateChange(e) {
  e.preventDefault();

  const mTargetId = document.getElementById('stateTargetId');
  const mTargetAction = document.getElementById('stateTargetAction');
  const mReason = document.getElementById('stateReason');

  const citId = mTargetId ? mTargetId.value : '';
  const action = mTargetAction ? mTargetAction.value : '';
  const reason = mReason ? mReason.value.trim() : '';

  const cit = ctrlCitizens.find(c => c.id === citId);
  if (!cit) return;

  // Map action to status state values
  let nextStatus = cit.status;
  let nextViolations = cit.violations;
  let nextFlagged = cit.flagged;

  if (action === 'Activate' || action === 'Unlock' || action === 'Restore') {
    nextStatus = 'Active';
    nextViolations = '0 failed login attempts';
    nextFlagged = false;
  } else if (action === 'Deactivate') {
    nextStatus = 'Inactive';
    nextViolations = 'Deactivated by administrator';
    nextFlagged = false;
  } else if (action === 'Lock') {
    nextStatus = 'Locked';
    nextViolations = 'Locked by administrator';
    nextFlagged = true;
  } else if (action === 'Suspend') {
    nextStatus = 'Suspended';
    nextViolations = 'Flagged: Security Investigation Hold';
    nextFlagged = true;
  }

  // Send update to database API
  try {
    await fetch('../../api/citizen/update-status.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        db_id: cit.db_id,
        citizen_id: cit.id,
        action: action,
        reason: reason
      })
    });
  } catch (err) {
    console.error('Error updating status on server:', err);
  }

  // Update local object states
  cit.status = nextStatus;
  cit.violations = nextViolations;
  cit.flagged = nextFlagged;

  // Add timeline record
  if (!Array.isArray(cit.timeline)) cit.timeline = [];
  cit.timeline.unshift({
    action: `Status updated to ${nextStatus}`,
    admin: "Superadmin",
    date: new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }),
    reason: reason
  });

  if (typeof closeModal === 'function') closeModal('stateChangeModal');
  if (typeof filterControlTable === 'function') filterControlTable();
  if (typeof updateControlStats === 'function') updateControlStats();
  if (typeof showToast === 'function') showToast(`Security parameters successfully updated for "${cit.name}".`);
}
