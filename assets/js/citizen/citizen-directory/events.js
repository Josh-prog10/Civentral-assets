// CITIZEN DIRECTORY EVENTS

function handleExecuteLock(e) {
  e.preventDefault();
  
  const cit = mockCitizens.find(c => c.id === window.pendingCitizenId);
  if (cit) {
    if (cit.status === 'Locked') {
      cit.status = 'Active';
      if (typeof showToast === 'function') showToast(`Credential lockout cleared. Portal access restored for ${cit.name}`);
    } else {
      const lockReason = document.getElementById('lockReason');
      const reason = lockReason ? lockReason.value : '';
      cit.status = 'Locked';
      if (typeof showToast === 'function') showToast(`Account locked for ${cit.name}. Reason: ${reason}`);
    }
    if (typeof filterCitizenTable === 'function') filterCitizenTable();
  }

  if (typeof closeModal === 'function') closeModal('lockCitizenModal');
}
