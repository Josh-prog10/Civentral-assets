window.civAudit = window.civAudit || {};
window.civAudit.loginHistory = window.civAudit.loginHistory || {};

window.civAudit.loginHistory.ui = {
  currentPage: 1,
  pageSize: 50,
  filteredLoginLogs: [],

  populateDepartments(departments) {
    const sel = document.getElementById('filterDepartment');
    if (!sel) return;
    const currentVal = sel.value;
    sel.innerHTML = '<option value="All">All Departments</option>';
    departments.forEach(dept => {
      const name = dept.department_name;
      const opt = document.createElement('option');
      opt.value = name;
      opt.textContent = name;
      sel.appendChild(opt);
    });
    sel.value = currentVal || 'All';
  },

  changePageSize(newSize) {
    this.pageSize = parseInt(newSize) || 10;
    this.currentPage = 1;
    this.renderPaginatedTable(this.filteredLoginLogs, 1);
  },

  formatDateTime(isoStr) {
    if (!isoStr || isoStr === '—') return '—';
    const d = new Date(isoStr.includes('T') ? isoStr : isoStr.replace(' ', 'T') + '+08:00');
    if (isNaN(d.getTime())) return isoStr;
    const dateStr = d.toLocaleDateString('en-US', { timeZone: 'Asia/Manila', month: 'short', day: 'numeric', year: 'numeric' });
    const timeStr = d.toLocaleTimeString('en-US', { timeZone: 'Asia/Manila', hour: '2-digit', minute: '2-digit', hour12: true });
    return `${dateStr} at ${timeStr}`;
  },

  parseUserAgent(ua) {
    if (!ua) return 'Desktop - Browser';
    let os = 'Desktop';
    if (/mobile/i.test(ua)) os = 'Mobile';
    else if (/mac/i.test(ua)) os = 'Mac';
    else if (/windows/i.test(ua)) os = 'Windows';
    else if (/linux/i.test(ua)) os = 'Linux';
    else if (/android/i.test(ua)) os = 'Android';
    else if (/iphone|ipad/i.test(ua)) os = 'iOS';

    let browser = 'Browser';
    if (/chrome|crios/i.test(ua) && !/edg/i.test(ua)) browser = 'Chrome';
    else if (/edg/i.test(ua)) browser = 'Edge';
    else if (/safari/i.test(ua) && !/chrome/i.test(ua)) browser = 'Safari';
    else if (/firefox|fxios/i.test(ua)) browser = 'Firefox';

    return `${os} - ${browser}`;
  },

  calculateLifespan(loginTimeStr, logoutTimeStr, loginStatus) {
    if (loginStatus === 'Failed') return '0m';
    if (!logoutTimeStr) return 'Active Session';
    const t1 = new Date(loginTimeStr.includes('T') ? loginTimeStr : loginTimeStr.replace(' ', 'T') + '+08:00').getTime();
    const t2 = new Date(logoutTimeStr.includes('T') ? logoutTimeStr : logoutTimeStr.replace(' ', 'T') + '+08:00').getTime();
    
    if (isNaN(t1) || isNaN(t2) || t2 < t1) return 'Active';
    
    const diffMs = t2 - t1;
    const mins = Math.floor(diffMs / 60000);
    const hrs = Math.floor(mins / 60);
    const remMins = mins % 60;

    if (hrs > 0) return `${hrs}h ${remMins}m`;
    return `${mins}m`;
  },

  changePage(delta) {
    const totalPages = Math.ceil(this.filteredLoginLogs.length / this.pageSize) || 1;
    const newPage = this.currentPage + delta;
    if (newPage >= 1 && newPage <= totalPages) {
      this.renderPaginatedTable(this.filteredLoginLogs, newPage);
    }
  },

  goToPage(pageNum) {
    const totalPages = Math.ceil(this.filteredLoginLogs.length / this.pageSize) || 1;
    if (pageNum >= 1 && pageNum <= totalPages) {
      this.renderPaginatedTable(this.filteredLoginLogs, pageNum);
    }
  },

  renderPaginatedTable(logs, page = 1) {
    this.filteredLoginLogs = logs || [];
    this.currentPage = page;

    const tbody = document.getElementById('loginTableBody');
    if (!tbody) return;

    const total = this.filteredLoginLogs.length;
    const totalPages = Math.ceil(total / this.pageSize) || 1;

    if (this.currentPage > totalPages) this.currentPage = totalPages;
    if (this.currentPage < 1) this.currentPage = 1;

    const startIdx = total > 0 ? (this.currentPage - 1) * this.pageSize : 0;
    const endIdx = Math.min(startIdx + this.pageSize, total);

    const startEl = document.getElementById('paginationStart');
    const endEl = document.getElementById('paginationEnd');
    const totalEl = document.getElementById('paginationTotal');
    const prevBtn = document.getElementById('prevPageBtn');
    const nextBtn = document.getElementById('nextPageBtn');
    const pageNumbersEl = document.getElementById('pageNumbers');

    if (startEl) startEl.innerText = total > 0 ? startIdx + 1 : 0;
    if (endEl) endEl.innerText = endIdx;
    if (totalEl) totalEl.innerText = total;

    if (prevBtn) prevBtn.disabled = (this.currentPage <= 1);
    if (nextBtn) nextBtn.disabled = (this.currentPage >= totalPages);

    if (pageNumbersEl) {
      pageNumbersEl.innerHTML = '';
      let maxVisible = 5;
      let startP = Math.max(1, this.currentPage - 2);
      let endP = Math.min(totalPages, startP + maxVisible - 1);
      if (endP - startP + 1 < maxVisible) {
        startP = Math.max(1, endP - maxVisible + 1);
      }

      for (let p = startP; p <= endP; p++) {
        const isDark = document.documentElement.classList.contains('dark');
        const btn = document.createElement('button');
        btn.onclick = () => this.goToPage(p);
        btn.className = `h-7 min-w-[28px] px-2 rounded-lg text-xs font-bold transition cursor-pointer ${
          p === this.currentPage
            ? 'bg-[#86B6F6] text-white shadow-sm ring-2 ring-[#86B6F6]/30'
            : isDark
              ? 'bg-slate-700 border border-slate-600 text-[#86B6F6] hover:bg-slate-600'
              : 'bg-white border border-[#B4D4FF] text-[#176B87] hover:bg-[#EEF5FF]'
        }`;
        btn.innerText = p;
        pageNumbersEl.appendChild(btn);
      }
    }

    tbody.innerHTML = '';

    if (total === 0) {
      tbody.innerHTML = `
        <tr id="noResultsRow">
          <td colspan="6" class="py-12 text-center text-slate-400">
            <div class="flex flex-col items-center justify-center space-y-2">
              <i class="fa-solid fa-user-slash text-3xl text-slate-300"></i>
              <p class="text-xs font-bold">No matching authentication logs found</p>
              <p class="text-[10px] font-semibold text-slate-400">Try adjusting your filters or resetting them to defaults.</p>
            </div>
          </td>
        </tr>
      `;
      return;
    }

    const pageLogs = this.filteredLoginLogs.slice(startIdx, endIdx);

    pageLogs.forEach(log => {
      const user = log.users || {};
      const roleObj = user.roles || {};
      const posObj = user.positions || {};
      const deptObj = posObj.departments || {};

      const actor = user.first_name ? `${user.first_name} ${user.last_name}` : (user.email || 'Unknown User');
      const email = user.email || 'N/A';
      const role = roleObj.role_name || 'User';
      const department = deptObj.department_name || 'Central IT';

      const loginTimeFormatted = this.formatDateTime(log.login_time);
      const logoutTimeFormatted = this.formatDateTime(log.logout_time);
      
      let datePart = 'N/A', timePart = '';
      if (log.login_time) {
        const d = new Date(log.login_time.includes('T') ? log.login_time : log.login_time.replace(' ', 'T') + '+08:00');
        if (!isNaN(d.getTime())) {
          datePart = d.toLocaleDateString('en-US', { timeZone: 'Asia/Manila', month: 'short', day: 'numeric', year: 'numeric' });
          timePart = d.toLocaleTimeString('en-US', { timeZone: 'Asia/Manila', hour: '2-digit', minute: '2-digit', hour12: true });
        }
      }

      let statusText = 'Successful Login';
      if (log.login_status === 'Failed') {
        if (user.status === 'Locked' || (log.failure_reason && log.failure_reason.toLowerCase().includes('locked'))) {
          statusText = 'Account Locked';
        } else {
          statusText = 'Failed Login';
        }
      } else if (log.logout_time) {
        statusText = 'Logout';
      }

      const lifespan = this.calculateLifespan(log.login_time, log.logout_time, log.login_status);
      const ip = log.ip_address || '127.0.0.1';
      const device = this.parseUserAgent(log.browser);
      const details = log.failure_reason || (log.login_status === 'Success' ? 'Auth session established successfully.' : 'Authentication attempt recorded.');

      const payloadObj = {
        login_id: log.login_id,
        user_id: log.user_id,
        session_id: log.session_id,
        login_time: log.login_time,
        logout_time: log.logout_time,
        ip_address: log.ip_address,
        browser: log.browser,
        login_status: log.login_status,
        failure_reason: log.failure_reason
      };

      const tr = document.createElement('tr');
      tr.onclick = function() { window.civAudit.loginHistory.modal.openSessionModal(this); };
      tr.className = 'hover:bg-slate-50/70 transition cursor-pointer';
      tr.setAttribute('data-id', `#LOG-${log.login_id}`);
      tr.setAttribute('data-date', log.login_time ? log.login_time.split(' ')[0] : '');
      tr.setAttribute('data-actor', actor);
      tr.setAttribute('data-email', email);
      tr.setAttribute('data-role', role);
      tr.setAttribute('data-department', department);
      tr.setAttribute('data-login-time', loginTimeFormatted);
      tr.setAttribute('data-logout-time', logoutTimeFormatted);
      tr.setAttribute('data-lifespan', lifespan);
      tr.setAttribute('data-status', statusText);
      tr.setAttribute('data-ip', ip);
      tr.setAttribute('data-device', device);
      tr.setAttribute('data-location', 'Caloocan City, PH');
      tr.setAttribute('data-details', details);
      tr.setAttribute('data-payload', JSON.stringify(payloadObj));

      let statusBadgeHtml = '';
      if (statusText === 'Successful Login') {
        statusBadgeHtml = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">🟢 Success</span>`;
      } else if (statusText === 'Logout') {
        statusBadgeHtml = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 ring-1 ring-slate-600/10">⚪ Logged Out</span>`;
      } else if (statusText === 'Account Locked') {
        statusBadgeHtml = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-red-100 text-red-900 ring-1 ring-red-800/30"><i class="fa-solid fa-user-lock text-[9px]"></i> Account Locked</span>`;
      } else if (statusText === 'Failed Login') {
        statusBadgeHtml = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 ring-1 ring-rose-600/20">🔴 Failed ${log.failure_reason ? '- ' + log.failure_reason : ''}</span>`;
      } else {
        statusBadgeHtml = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 ring-1 ring-indigo-600/20">🔑 ${statusText}</span>`;
      }

      let loginTimeSub = '';
      if (log.logout_time) {
        loginTimeSub = `<div class="text-[10px] text-slate-400 font-semibold mt-0.5">Out: ${logoutTimeFormatted.split('at ')[1] || logoutTimeFormatted}</div>`;
      } else if (log.login_status !== 'Failed') {
        loginTimeSub = `<div class="text-[10px] text-emerald-600 font-bold mt-0.5">Active Session</div>`;
      }

      const esc = window.escapeHtml || (s => s);

      tr.innerHTML = `
        <td class="py-4 px-5 font-mono text-[11px] font-bold text-slate-500">#LOG-${log.login_id}</td>
        <td class="py-4 px-5">
          <div class="font-bold text-slate-800">${esc(actor)}</div>
          <div class="text-[10px] text-slate-400 font-semibold mt-0.5">${esc(email)} • ${esc(role)}</div>
        </td>
        <td class="py-4 px-5">
          <div class="font-bold text-slate-800">${esc(datePart)} at ${esc(timePart)}</div>
          ${loginTimeSub}
        </td>
        <td class="py-4 px-5 text-center font-bold text-slate-700">${esc(lifespan)}</td>
        <td class="py-4 px-5">${statusBadgeHtml}</td>
        <td class="py-4 px-5">
          <div class="font-mono text-[11px] font-bold text-slate-700">${esc(ip)}</div>
          <div class="text-[10px] text-slate-400 font-semibold mt-0.5">${esc(device)}</div>
        </td>
      `;

      tbody.appendChild(tr);
    });
    hideLoginHistorySkeleton();
  }
};

function hideLoginHistorySkeleton() {
  const skel = document.getElementById('loginHistorySkeleton');
  const real = document.getElementById('loginHistoryRealContent');
  if (!skel || !real) return;

  real.classList.remove('hidden');
  requestAnimationFrame(() => {
    skel.classList.add('opacity-0', 'pointer-events-none');
    real.classList.remove('opacity-0', 'translate-y-2');
    real.classList.add('opacity-100', 'translate-y-0');
    setTimeout(() => {
      skel.classList.add('hidden');
    }, 500);
  });
}

window.changePage = function(delta) {
  if (window.civAudit.loginHistory.ui) {
    window.civAudit.loginHistory.ui.changePage(delta);
  }
};

window.goToPage = function(pageNum) {
  if (window.civAudit.loginHistory.ui) {
    window.civAudit.loginHistory.ui.goToPage(pageNum);
  }
};

window.changePageSize = function(newSize) {
  if (window.civAudit.loginHistory.ui) {
    window.civAudit.loginHistory.ui.changePageSize(newSize);
  }
};
