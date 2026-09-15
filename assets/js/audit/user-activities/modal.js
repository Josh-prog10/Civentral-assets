window.civAudit = window.civAudit || {};
window.civAudit.userActivities = window.civAudit.userActivities || {};

window.civAudit.userActivities.modal = {
  openLogDetailsModal(row) {
    const id = row.getAttribute('data-id');
    const dateTime = row.getAttribute('data-date-time');
    const actor = row.getAttribute('data-actor');
    const role = row.getAttribute('data-role');
    const roleColor = row.getAttribute('data-role-color');
    const department = row.getAttribute('data-department');
    const moduleName = row.getAttribute('data-module');
    const action = row.getAttribute('data-action');
    const desc = row.getAttribute('data-desc');
    const ip = row.getAttribute('data-ip');
    const device = row.getAttribute('data-device');
    const status = row.getAttribute('data-status');
    const payloadStr = row.getAttribute('data-payload');

    const elId = document.getElementById('modalActId');
    const elActor = document.getElementById('modalActorName');
    const elDT = document.getElementById('modalDateTime');
    const elDept = document.getElementById('modalDepartment');
    const elMod = document.getElementById('modalModule');
    const elDesc = document.getElementById('modalActionDesc');
    const elIp = document.getElementById('modalIp');
    const elDev = document.getElementById('modalDevice');

    if (elId) elId.innerText = id;
    if (elActor) elActor.innerText = actor;
    if (elDT) elDT.innerText = dateTime;
    if (elDept) elDept.innerText = department;
    if (elMod) elMod.innerText = moduleName;
    if (elDesc) elDesc.innerText = desc;
    if (elIp) elIp.innerText = ip;
    if (elDev) elDev.innerText = device;

    // Pretty print JSON payload
    const payloadText = document.getElementById('modalPayloadText');
    if (payloadText) {
      try {
        const parsedPayload = JSON.parse(payloadStr);
        payloadText.textContent = JSON.stringify(parsedPayload, null, 2);
      } catch (e) {
        payloadText.textContent = payloadStr;
      }
    }

    // Setup Actor Badge Color
    const actorRoleDiv = document.getElementById('modalActorRole');
    if (actorRoleDiv) {
      let actorBadgeClasses = "inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase ";
      if (roleColor === 'purple') actorBadgeClasses += "bg-purple-50 text-purple-700 ring-1 ring-purple-600/20";
      else if (roleColor === 'blue') actorBadgeClasses += "bg-blue-50 text-blue-700 ring-1 ring-blue-600/20";
      else actorBadgeClasses += "bg-slate-100 text-slate-600 ring-1 ring-slate-600/10";
      actorRoleDiv.innerHTML = `<span class="${actorBadgeClasses}">${window.escapeHtml ? window.escapeHtml(role) : role}</span>`;
    }

    // Setup Action Badge
    const actionBadge = document.getElementById('modalActionBadge');
    if (actionBadge) {
      actionBadge.innerText = action;
      actionBadge.className = "inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase ";
      if (action === 'Approve') actionBadge.className += "bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20";
      else if (action === 'Create') actionBadge.className += "bg-blue-50 text-blue-700 ring-1 ring-blue-600/20";
      else if (action === 'Update' || action === 'Edit') actionBadge.className += "bg-amber-50 text-amber-800 ring-1 ring-amber-600/20";
      else if (action === 'Reject' || action === 'Delete') actionBadge.className += "bg-rose-50 text-rose-700 ring-1 ring-rose-600/20";
      else actionBadge.className += "bg-slate-50 text-slate-700 ring-1 ring-slate-600/20";
    }

    // Setup Status Banner
    const statusBanner = document.getElementById('modalStatusBanner');
    const statusIconCont = document.getElementById('modalStatusIconContainer');
    const statusTitle = document.getElementById('modalStatusTitle');
    const statusMsg = document.getElementById('modalStatusMsg');

    if (statusBanner && statusIconCont && statusTitle && statusMsg) {
      if (status === 'Success') {
        statusBanner.className = "p-4 rounded-xl flex items-center gap-3 bg-emerald-50 border border-emerald-100 text-emerald-800";
        statusIconCont.className = "h-8 w-8 rounded-lg flex items-center justify-center shrink-0 bg-emerald-100 text-emerald-700";
        statusIconCont.innerHTML = `<i class="fa-solid fa-circle-check text-base"></i>`;
        statusTitle.innerText = "Transaction Success";
        statusMsg.innerText = "The operations were processed, audited, and committed successfully within Caloocan LGU security layers.";
        statusMsg.className = "text-[10px] leading-relaxed font-semibold text-emerald-600 mt-0.5";
      } else {
        statusBanner.className = "p-4 rounded-xl flex items-center gap-3 bg-rose-50 border border-rose-100 text-rose-800";
        statusIconCont.className = "h-8 w-8 rounded-lg flex items-center justify-center shrink-0 bg-rose-100 text-rose-700";
        statusIconCont.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-base"></i>`;
        statusTitle.innerText = "Transaction Blocked / Failed";
        statusMsg.innerText = "The request failed validation checks, permission triggers, or policy locks and was blocked.";
        statusMsg.className = "text-[10px] leading-relaxed font-semibold text-rose-600 mt-0.5";
      }
    }

    const modal = document.getElementById('logDetailsModal');
    const card = document.getElementById('modalCard');
    if (modal && card) {
      modal.classList.remove('hidden');
      setTimeout(() => {
        card.classList.remove('scale-95', 'opacity-0');
        card.classList.add('scale-100', 'opacity-100');
      }, 10);
    }
  },

  closeLogDetailsModal() {
    const modal = document.getElementById('logDetailsModal');
    const card = document.getElementById('modalCard');
    if (modal && card) {
      card.classList.remove('scale-100', 'opacity-100');
      card.classList.add('scale-95', 'opacity-0');
      setTimeout(() => {
        modal.classList.add('hidden');
      }, 150);
    }
  }
};
