// MODAL CONTROLS
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
