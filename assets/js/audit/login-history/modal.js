window.civAudit = window.civAudit || {};
window.civAudit.loginHistory = window.civAudit.loginHistory || {};

window.civAudit.loginHistory.modal = {
  openSessionModal(row) {
    const id = row.getAttribute('data-id');
    const actor = row.getAttribute('data-actor');
    const email = row.getAttribute('data-email');
    const role = row.getAttribute('data-role');
    const department = row.getAttribute('data-department');
    const loginTime = row.getAttribute('data-login-time');
    const logoutTime = row.getAttribute('data-logout-time');
    const lifespan = row.getAttribute('data-lifespan');
    const status = row.getAttribute('data-status');
    const ip = row.getAttribute('data-ip');
    const device = row.getAttribute('data-device');
    const location = row.getAttribute('data-location');
    const details = row.getAttribute('data-details');
    const payloadStr = row.getAttribute('data-payload');

    // Load details
    document.getElementById('modalLogId').innerText = id;
    document.getElementById('modalActorName').innerText = actor;
    document.getElementById('modalActorEmail').innerText = email;
    document.getElementById('modalRoleDept').innerText = `${role} | ${department}`;
    document.getElementById('modalLoginTime').innerText = loginTime;
    document.getElementById('modalLogoutTime').innerText = logoutTime;
    document.getElementById('modalLifespan').innerText = lifespan;
    document.getElementById('modalLogDetails').innerText = details;
    document.getElementById('modalIp').innerText = ip;
    document.getElementById('modalDevice').innerText = device;
    document.getElementById('modalLocation').innerText = location;

    // Code payload
    try {
      const parsedPayload = JSON.parse(payloadStr);
      document.getElementById('modalPayloadText').textContent = JSON.stringify(parsedPayload, null, 2);
    } catch (e) {
      document.getElementById('modalPayloadText').textContent = payloadStr;
    }

    // Setup Status Badge
    const statusBadge = document.getElementById('modalStatusBadge');
    statusBadge.innerText = status;
    let statusBadgeClasses = "inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold ";
    if (status === 'Successful Login') {
      statusBadgeClasses += "bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20";
    } else if (status === 'Failed Login') {
      statusBadgeClasses += "bg-rose-50 text-rose-700 ring-1 ring-rose-600/20";
    } else if (status === 'Account Locked') {
      statusBadgeClasses += "bg-red-100 text-red-900 ring-1 ring-red-800/30 font-black";
    } else if (status === 'Session Expired') {
      statusBadgeClasses += "bg-amber-50 text-amber-800 ring-1 ring-amber-600/20";
    } else if (status === 'Logout') {
      statusBadgeClasses += "bg-slate-100 text-slate-650 ring-1 ring-slate-600/10";
    } else {
      statusBadgeClasses += "bg-indigo-50 text-indigo-700 ring-1 ring-indigo-600/20";
    }
    statusBadge.className = statusBadgeClasses;

    // Setup Status Banner
    const statusBanner = document.getElementById('modalStatusBanner');
    const statusIconCont = document.getElementById('modalStatusIconContainer');
    const statusTitle = document.getElementById('modalStatusTitle');
    const statusMsg = document.getElementById('modalStatusMsg');

    if (status === 'Successful Login') {
      statusBanner.className = "p-4 rounded-xl flex items-center gap-3 bg-emerald-50 border border-emerald-100 text-emerald-800";
      statusIconCont.className = "h-8 w-8 rounded-lg flex items-center justify-center shrink-0 bg-emerald-100 text-emerald-700";
      statusIconCont.innerHTML = `<i class="fa-solid fa-circle-check text-base"></i>`;
      statusTitle.innerText = "Successful Login";
      statusMsg.innerText = "Auth session established successfully. Security keys verified.";
      statusMsg.className = "text-[10px] leading-relaxed font-semibold text-emerald-600 mt-0.5";
    } else if (status === 'Failed Login') {
      statusBanner.className = "p-4 rounded-xl flex items-center gap-3 bg-rose-50 border border-rose-100 text-rose-800";
      statusIconCont.className = "h-8 w-8 rounded-lg flex items-center justify-center shrink-0 bg-rose-100 text-rose-700";
      statusIconCont.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-base"></i>`;
      statusTitle.innerText = "Failed Login Attempt";
      statusMsg.innerText = "Authorization request rejected due to invalid credential verification keys.";
      statusMsg.className = "text-[10px] leading-relaxed font-semibold text-rose-600 mt-0.5";
    } else if (status === 'Account Locked') {
      statusBanner.className = "p-4 rounded-xl flex items-center gap-3 bg-red-950/15 border border-red-200/50 text-red-950";
      statusIconCont.className = "h-8 w-8 rounded-lg flex items-center justify-center shrink-0 bg-red-200 text-red-900";
      statusIconCont.innerHTML = `<i class="fa-solid fa-user-lock text-base"></i>`;
      statusTitle.innerText = "Account Locked";
      statusMsg.innerText = "User account has been locked due to excessive authentication failures.";
      statusMsg.className = "text-[10px] leading-relaxed font-semibold text-red-700 mt-0.5";
    } else if (status === 'Session Expired') {
      statusBanner.className = "p-4 rounded-xl flex items-center gap-3 bg-amber-50 border border-amber-100 text-amber-800";
      statusIconCont.className = "h-8 w-8 rounded-lg flex items-center justify-center shrink-0 bg-amber-100 text-amber-700";
      statusIconCont.innerHTML = `<i class="fa-solid fa-hourglass-end text-base"></i>`;
      statusTitle.innerText = "Session Expired";
      statusMsg.innerText = "Authentication credentials expired due to standard idle inactivity triggers.";
      statusMsg.className = "text-[10px] leading-relaxed font-semibold text-amber-600 mt-0.5";
    } else {
      statusBanner.className = "p-4 rounded-xl flex items-center gap-3 bg-slate-50 border border-slate-200 text-slate-800";
      statusIconCont.className = "h-8 w-8 rounded-lg flex items-center justify-center shrink-0 bg-slate-200 text-slate-600";
      statusIconCont.innerHTML = `<i class="fa-solid fa-circle-xmark text-base"></i>`;
      statusTitle.innerText = status;
      statusMsg.innerText = "Authentication trace event resolved successfully.";
      statusMsg.className = "text-[10px] leading-relaxed font-semibold text-slate-500 mt-0.5";
    }

    // Show Modal
    const modal = document.getElementById('sessionInspectorModal') || document.getElementById('sessionDetailsModal');
    const card = document.getElementById('modalCard');
    
    if (modal) {
      modal.classList.remove('hidden');
      setTimeout(() => {
        if (card) {
          card.classList.remove('scale-95', 'opacity-0');
          card.classList.add('scale-100', 'opacity-100');
        }
      }, 10);
    }
  },

  closeSessionModal() {
    const modal = document.getElementById('sessionInspectorModal') || document.getElementById('sessionDetailsModal');
    const card = document.getElementById('modalCard');
    
    if (card) {
      card.classList.remove('scale-100', 'opacity-100');
      card.classList.add('scale-95', 'opacity-0');
    }
    setTimeout(() => {
      if (modal) modal.classList.add('hidden');
    }, 150);
  },

  copyModalPayload() {
    const payloadText = document.getElementById('modalPayloadText').textContent;
    navigator.clipboard.writeText(payloadText).then(() => {
      if (window.showToast) {
        window.showToast("Payload Copied", "Session JSON payload details copied to clipboard.");
      }
    }).catch(err => {
      console.error('Copy failed: ', err);
    });
  }
};

window.openSessionModal = function(row) {
  if (window.civAudit.loginHistory.modal) {
    window.civAudit.loginHistory.modal.openSessionModal(row);
  }
};

window.closeSessionModal = function() {
  if (window.civAudit.loginHistory.modal) {
    window.civAudit.loginHistory.modal.closeSessionModal();
  }
};

window.copyModalPayload = function() {
  if (window.civAudit.loginHistory.modal) {
    window.civAudit.loginHistory.modal.copyModalPayload();
  }
};
