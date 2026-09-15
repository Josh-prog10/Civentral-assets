function toggleDropdown(id, chevronId) {
  const dropdown = document.getElementById(id);
  const chevron = document.getElementById(chevronId);
  if (!dropdown) return;

  const allDropdowns = ['userDropdown', 'roleDropdown', 'deptDropdown', 'citizenDropdown', 'scholarshipDropdown', 'auditDropdown'];
  const allChevrons = ['userChevron', 'roleChevron', 'deptChevron', 'citizenChevron', 'scholarshipChevron', 'auditChevron'];

  allDropdowns.forEach((d, i) => {
    if (d !== id) {
      const otherEl = document.getElementById(d);
      if (otherEl) otherEl.classList.add('hidden');
      const otherChev = document.getElementById(allChevrons[i]);
      if (otherChev) otherChev.classList.remove('rotate-180');
    }
  });

  if (dropdown.classList.contains('hidden')) {
    dropdown.classList.remove('hidden');
    if (chevron) chevron.classList.add('rotate-180');
  } else {
    dropdown.classList.add('hidden');
    if (chevron) chevron.classList.remove('rotate-180');
  }
}
