// Logout Modal
function openLogoutModal(e) {
  if (e) e.preventDefault();
  const modal = document.getElementById('logoutModal');
  const menu = document.getElementById('profileDropdownMenu');
  if (menu && !menu.classList.contains('hidden')) {
    menu.classList.add('hidden');
  }
  if (!modal) return;

  modal.classList.remove('hidden');
  setTimeout(() => {
    modal.classList.remove('opacity-0');
    const card = modal.querySelector('div');
    if (card) {
      card.classList.remove('scale-95');
      card.classList.add('scale-100');
    }
  }, 10);
}

function closeLogoutModal() {
  const modal = document.getElementById('logoutModal');
  if (!modal) return;

  modal.classList.add('opacity-0');
  const card = modal.querySelector('div');
  if (card) {
    card.classList.remove('scale-100');
    card.classList.add('scale-95');
  }
  setTimeout(() => {
    modal.classList.add('hidden');
  }, 200);
}
