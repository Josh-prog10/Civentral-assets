// CITIZEN DIRECTORY MODALS

function openModal(id) {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.classList.remove('opacity-0', 'pointer-events-none');
  modal.classList.add('opacity-100', 'pointer-events-auto');
  const card = modal.querySelector('.transform');
  if (card) {
    card.classList.remove('scale-95');
    card.classList.add('scale-100');
  }
}

function closeModal(id) {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.classList.remove('opacity-100', 'pointer-events-auto');
  modal.classList.add('opacity-0', 'pointer-events-none');
  const card = modal.querySelector('.transform');
  if (card) {
    card.classList.remove('scale-100');
    card.classList.add('scale-95');
  }
}

function openViewModal(citId) {
  const cit = mockCitizens.find(c => c.id === citId);
  if (!cit) return;

  const viewName = document.getElementById('viewName');
  const viewId = document.getElementById('viewId');
  if (viewName) viewName.innerText = cit.name;
  if (viewId) viewId.innerText = cit.id;

  const rawDate = new Date(cit.regDate);
  const options = { month: 'short', day: 'numeric', year: 'numeric' };
  
  const viewRegDate = document.getElementById('viewRegDate');
  const viewLastLogin = document.getElementById('viewLastLogin');
  if (viewRegDate) viewRegDate.innerText = rawDate.toLocaleDateString('en-US', options);
  if (viewLastLogin) viewLastLogin.innerText = cit.lastLogin;

  const sBadge = document.getElementById('viewStatusBadge');
  if (sBadge) {
    sBadge.innerText = cit.status;
    if (cit.status === 'Active') {
      sBadge.className = 'text-[9px] font-black uppercase px-2.5 py-1 rounded-full border bg-emerald-50 text-emerald-700 border-emerald-150';
    } else if (cit.status === 'Inactive') {
      sBadge.className = 'text-[9px] font-black uppercase px-2.5 py-1 rounded-full border bg-slate-50 text-slate-500 border-slate-200';
    } else {
      sBadge.className = 'text-[9px] font-black uppercase px-2.5 py-1 rounded-full border bg-rose-50 text-rose-700 border-rose-150';
    }
  }

  const styleService = (statusStr) => {
    if (statusStr === 'Active') return 'bg-emerald-50 text-emerald-700 border-emerald-150';
    if (statusStr === 'Pending') return 'bg-amber-50 text-amber-700 border-amber-150';
    if (statusStr === 'Expired') return 'bg-rose-50 text-rose-700 border-rose-150';
    return 'bg-slate-50 text-slate-500 border-slate-200';
  };

  const cardScholarship = document.getElementById('serviceScholarship');
  const cardPermit = document.getElementById('servicePermit');
  const cardWelfare = document.getElementById('serviceWelfare');

  if (cardScholarship) {
    cardScholarship.innerText = cit.services.scholarship;
    cardScholarship.className = `text-[9px] font-black tracking-wide px-2 py-0.5 rounded border self-start ${styleService(cit.services.scholarship)}`;
  }

  if (cardPermit) {
    cardPermit.innerText = cit.services.permit;
    cardPermit.className = `text-[9px] font-black tracking-wide px-2 py-0.5 rounded border self-start ${styleService(cit.services.permit)}`;
  }

  if (cardWelfare) {
    cardWelfare.innerText = cit.services.welfare;
    cardWelfare.className = `text-[9px] font-black tracking-wide px-2 py-0.5 rounded border self-start ${styleService(cit.services.welfare)}`;
  }

  openModal('viewCitizenModal');
}

function openResetModal(citId) {
  const cit = mockCitizens.find(c => c.id === citId);
  if (!cit) return;

  window.pendingCitizenId = citId;
  const resetConfirmText = document.getElementById('resetConfirmText');
  const tempCitizenPass = document.getElementById('tempCitizenPass');

  if (resetConfirmText) resetConfirmText.innerText = `Are you sure you want to reset the password for ${cit.name}?`;
  if (tempCitizenPass) tempCitizenPass.value = ''; // Clear box

  openModal('resetCitizenModal');
}

function generateCitizenPassword() {
  const code = Math.floor(1000 + Math.random() * 9000);
  const newPass = `CaloocanCiv#${code}`;
  const tempCitizenPass = document.getElementById('tempCitizenPass');
  if (tempCitizenPass) tempCitizenPass.value = newPass;
  if (typeof showToast === 'function') showToast("Temporary security password successfully generated.");
}

function copyCitizenPass() {
  const passEl = document.getElementById('tempCitizenPass');
  if (!passEl) return;
  if (passEl.value === '') {
    if (typeof showToast === 'function') showToast("Please generate a password first.", true);
    return;
  }
  passEl.select();
  navigator.clipboard.writeText(passEl.value);
  if (typeof showToast === 'function') showToast("Temporary password copied to clipboard.");
}

function openLockModal(citId) {
  const cit = mockCitizens.find(c => c.id === citId);
  if (!cit) return;

  window.pendingCitizenId = citId;
  const lockCitizenIdRef = document.getElementById('lockCitizenIdRef');
  if (lockCitizenIdRef) lockCitizenIdRef.value = citId;

  const mTitle = document.getElementById('lockModalTitle');
  const mHeader = document.getElementById('lockModalHeader');
  const mText = document.getElementById('lockModalText');
  const mConfirm = document.getElementById('lockModalSubmitBtn');
  const iconBox = document.getElementById('lockIconBox');
  const reasonContainer = document.getElementById('lockReasonContainer');

  if (cit.status === 'Locked') {
    if (mTitle) mTitle.innerText = "Unlock Citizen Credentials";
    if (mHeader) mHeader.innerText = `Place Access Clearance?`;
    if (mText) mText.innerText = `Confirm unlocking ${cit.name}'s profile. This immediately clears verification blocks.`;
    if (mConfirm) {
      mConfirm.innerText = "Unlock Account";
      mConfirm.className = "bg-[#0f172a] hover:bg-slate-800 text-white px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer shadow-xs";
    }
    if (iconBox) {
      iconBox.className = "h-10 w-10 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0";
      iconBox.innerHTML = '<i class="fa-solid fa-lock-open text-base animate-pulse"></i>';
    }
    if (reasonContainer) reasonContainer.classList.add('hidden'); 
  } else {
    if (mTitle) mTitle.innerText = "Lock Citizen Credentials";
    if (mHeader) mHeader.innerText = `Place Security Access Block?`;
    if (mText) mText.innerText = `Confirm locking ${cit.name}'s profile. This prevents verification logins immediately.`;
    if (mConfirm) {
      mConfirm.innerText = "Lock Account";
      mConfirm.className = "bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer shadow-xs";
    }
    if (iconBox) {
      iconBox.className = "h-10 w-10 rounded-full bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-500 shrink-0";
      iconBox.innerHTML = '<i class="fa-solid fa-user-lock text-base"></i>';
    }
    if (reasonContainer) reasonContainer.classList.remove('hidden'); 
  }

  openModal('lockCitizenModal');
}
