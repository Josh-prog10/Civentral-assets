// Settings Theme
// Responsible for handling user preferences for light/dark mode within settings

function selectSystemTheme(theme) {
  if (theme === 'dark') {
    document.documentElement.classList.add('dark');
    localStorage.setItem('civentral_theme', 'dark');
    showToast('Dark mode activated!', 'info', 'Theme preference saved.');
  } else {
    document.documentElement.classList.remove('dark');
    localStorage.setItem('civentral_theme', 'light');
    showToast('Light mode activated!', 'info', 'Theme preference saved.');
  }

  syncThemeCardsUI();
  if (typeof syncThemeToggleIcon === 'function') {
    syncThemeToggleIcon();
  }
}

function syncThemeCardsUI() {
  const cardLight = document.getElementById('themeCardLight');
  const cardDark = document.getElementById('themeCardDark');
  const badgeLight = document.getElementById('themeBadgeLight');
  const badgeDark = document.getElementById('themeBadgeDark');

  if (!cardLight || !cardDark) return;

  const isDark = document.documentElement.classList.contains('dark');

  if (isDark) {
    cardLight.className = "border-2 border-slate-200 bg-white dark:bg-slate-900 rounded-2xl p-4 flex items-start gap-3.5 cursor-pointer transition select-none hover:border-slate-300";
    if (badgeLight) badgeLight.classList.add('hidden');

    cardDark.className = "border-2 border-amber-400 bg-slate-900 text-white rounded-2xl p-4 flex items-start gap-3.5 cursor-pointer transition select-none shadow-md";
    if (badgeDark) badgeDark.classList.remove('hidden');
  } else {
    cardLight.className = "border-2 border-brand-medium bg-brand-light/30 rounded-2xl p-4 flex items-start gap-3.5 cursor-pointer transition select-none shadow-xs";
    if (badgeLight) badgeLight.classList.remove('hidden');

    cardDark.className = "border-2 border-slate-200 bg-slate-900 text-white rounded-2xl p-4 flex items-start gap-3.5 cursor-pointer transition select-none hover:border-slate-700";
    if (badgeDark) badgeDark.classList.add('hidden');
  }
}

