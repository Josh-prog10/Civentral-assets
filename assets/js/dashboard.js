// CALENDAR/CLOCK FUNCTION
    function updateClock() {
      const now = new Date();
      const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
      document.getElementById('headerClock').innerText = now.toLocaleDateString('en-US', options);
    }
    setInterval(updateClock, 1000);
    updateClock();

    // DROPDOWN
    function toggleDropdown(id, chevronId) {
      if (typeof isCollapsed !== 'undefined' && isCollapsed) return; 

      const dropdown = document.getElementById(id);
      const chevron = document.getElementById(chevronId);
      if (!dropdown) return;
      
      const dropdowns = ['userDropdown', 'roleDropdown', 'deptDropdown', 'citizenDropdown', 'scholarshipDropdown', 'auditDropdown'];
      const chevrons = ['userChevron', 'roleChevron', 'deptChevron', 'citizenChevron', 'scholarshipChevron', 'auditChevron'];
      
      dropdowns.forEach((d, i) => {
        if (d !== id) {
          const otherEl = document.getElementById(d);
          if (otherEl) otherEl.classList.add('hidden');
          const otherChevron = document.getElementById(chevrons[i]);
          if (otherChevron) otherChevron.classList.remove('rotate-180');
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

    // SIDEBAR RESPONSIVE
  let isCollapsed = false;

function toggleSidebar() {

    const sidebar = document.getElementById('sidebar');
    const arrow = document.getElementById('toggleArrow');

    const sideLabels = document.querySelectorAll('.sidebar-text');
    const dropdownButtons = document.querySelectorAll('.dropdown-btn');
    const dropdownRights = document.querySelectorAll('.dropdown-right');

    const dropdowns = [
        'userDropdown',
        'roleDropdown',
        'deptDropdown',
        'citizenDropdown',
        'auditDropdown'
    ];

    isCollapsed = !isCollapsed;

    if (isCollapsed) {

        // CLOSE
        dropdowns.forEach(id => {
            const menu = document.getElementById(id);

            if (menu) {
                menu.classList.add('hidden');
            }
        });

        // COLLAPSE
        sidebar.classList.remove('w-72');
        sidebar.classList.add('w-20');

        arrow.className = "fa-solid fa-chevron-right text-xs";

        // HIDE
        sideLabels.forEach(label => {
            label.classList.add('hidden');
        });

        // HIDE DROPDOWN
        dropdownRights.forEach(right => {
            right.classList.add('hidden');
        });

        // CENTER ICON
        dropdownButtons.forEach(btn => {
            btn.classList.remove('justify-between');
            btn.classList.add('justify-center');
        });

    } else {

        // EXPAND
        sidebar.classList.remove('w-20');
        sidebar.classList.add('w-72');

        arrow.className = "fa-solid fa-chevron-left text-xs";

        // SHOW TEXT
        sideLabels.forEach(label => {
            label.classList.remove('hidden');
        });

        // SHOW DROP DOWN BUTTON
        dropdownRights.forEach(right => {
            right.classList.remove('hidden');
        });

        // RESTORE
        dropdownButtons.forEach(btn => {
            btn.classList.remove('justify-center');
            btn.classList.add('justify-between');
        });

        // RESET
        document.querySelectorAll('.dropdown-chevron').forEach(chv => {
            chv.classList.remove('rotate-180');
        });

    }
}