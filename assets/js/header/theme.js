// Sync Theme Toggle Icon
function syncThemeToggleIcon() {
  const icon = document.getElementById('themeToggleIcon');
  if (!icon) return;
  if (document.documentElement.classList.contains('dark')) {
    icon.className = 'fa-solid fa-sun text-lg text-amber-400';
  } else {
    icon.className = 'fa-solid fa-moon text-lg text-slate-500 hover:text-brand-dark';
  }
}

// Toggle Light / Dark Mode
function toggleAppTheme() {
  if (document.documentElement.classList.contains('dark')) {
    document.documentElement.classList.remove('dark');
    localStorage.setItem('civentral_theme', 'light');
  } else {
    document.documentElement.classList.add('dark');
    localStorage.setItem('civentral_theme', 'dark');
  }
  syncThemeToggleIcon();
  window.dispatchEvent(new Event('civentralThemeChanged'));
}
