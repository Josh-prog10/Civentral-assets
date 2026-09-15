// SUCCESS MODAL HELPERS
function openSuccessModal() {
  const modal = document.getElementById('successModal');
  if (!modal) return;
  modal.classList.remove('opacity-0', 'pointer-events-none');
  modal.classList.add('opacity-100', 'pointer-events-auto');
  const card = modal.querySelector('.transform');
  if (card) {
    card.classList.remove('scale-95');
    card.classList.add('scale-100');
  }
}

function closeSuccessModal() {
  const modal = document.getElementById('successModal');
  if (!modal) return;
  modal.classList.remove('opacity-100', 'pointer-events-auto');
  modal.classList.add('opacity-0', 'pointer-events-none');
  const card = modal.querySelector('.transform');
  if (card) {
    card.classList.remove('scale-100');
    card.classList.add('scale-95');
  }
}

function copyModalPassword() {
  const tempPass = document.getElementById('modalTempPass').innerText;
  navigator.clipboard.writeText(tempPass).then(() => {
    if (typeof showToast === 'function') showToast('Temporary password copied to clipboard!');
  }).catch(() => {
    if (typeof showToast === 'function') showToast('Failed to copy password.', true);
  });
}
