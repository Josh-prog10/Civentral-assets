// Profile Dropdown
function toggleProfileDropdown(event) {
  if (event) event.stopPropagation();
  const menu = document.getElementById('profileDropdownMenu');
  if (!menu) return;

  if (menu.classList.contains('hidden')) {
    menu.classList.remove('hidden');
    setTimeout(() => {
      menu.classList.remove('scale-95', 'opacity-0');
      menu.classList.add('scale-100', 'opacity-100');
    }, 10);
  } else {
    menu.classList.add('scale-95', 'opacity-0');
    menu.classList.remove('scale-100', 'opacity-100');
    setTimeout(() => {
      menu.classList.add('hidden');
    }, 150);
  }
}

// Close Dropdown on outside click
document.addEventListener('click', function(event) {
  const menu = document.getElementById('profileDropdownMenu');
  const btn = document.getElementById('profileDropdownBtn');
  if (menu && !menu.classList.contains('hidden')) {
    if (!menu.contains(event.target) && !btn.contains(event.target)) {
      menu.classList.add('scale-95', 'opacity-0');
      menu.classList.remove('scale-100', 'opacity-100');
      setTimeout(() => {
        menu.classList.add('hidden');
      }, 150);
    }
  }
});
