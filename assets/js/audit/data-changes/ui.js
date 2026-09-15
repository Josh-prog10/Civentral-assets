window.civAudit = window.civAudit || {};
window.civAudit.dataChanges = window.civAudit.dataChanges || {};

window.civAudit.dataChanges.ui = {
  currentPage: 1,
  pageSize: 50,
  currentFilteredLogs: [],

  humanizeFieldName(field) {
    if (!field) return 'Field';
    const map = {
      'first_name': 'First Name',
      'last_name': 'Last Name',
      'middle_name': 'Middle Name',
      'email': 'Email Address',
      'username': 'Username',
      'role_id': 'Role ID',
      'role_name': 'Role Name',
      'department_id': 'Department ID',
      'department_name': 'Department',
      'status': 'Account Status',
      'is_active': 'Active Status',
      'phone': 'Phone Number',
      'mobile_number': 'Mobile Number',
      'address': 'Address',
      'permission_id': 'Permission ID',
      'permission_name': 'Permission Name',
      'granted_resources': 'Granted Resources',
      'granted_actions': 'Granted Actions'
    };
    const key = field.toLowerCase();
    if (map[key]) return map[key];
    return field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
  },

  populateModuleDropdown() {
    const moduleSelect = document.getElementById('filterModule');
    if (!moduleSelect) return;
    const currentVal = moduleSelect.value;
    
    let html = '<option value="All">All Modules</option>';
    const api = window.civAudit.dataChanges.api;
    
    const moduleNames = new Set();
    api.availableModules.forEach(m => { if (m.module_name) moduleNames.add(m.module_name); });
    api.auditLogsData.forEach(log => {
      if (log.modules && log.modules.module_name) {
        moduleNames.add(log.modules.module_name);
      } else if (log.target_table) {
        moduleNames.add(log.target_table);
      }
    });

    const esc = window.escapeHtml || (s => s);
    Array.from(moduleNames).sort().forEach(mod => {
      html += `<option value="${esc(mod)}">${esc(mod)}</option>`;
    });

    moduleSelect.innerHTML = html;
    if (currentVal && moduleNames.has(currentVal)) {
      moduleSelect.value = currentVal;
    }
  },

  changePageSize(newSize) {
    this.pageSize = parseInt(newSize) || 10;
    this.currentPage = 1;
    this.renderMutationLogs(this.currentFilteredLogs, 1);
  },

  changePage(delta) {
    const totalPages = Math.ceil(this.currentFilteredLogs.length / this.pageSize) || 1;
    const newPage = this.currentPage + delta;
    if (newPage >= 1 && newPage <= totalPages) {
      this.renderMutationLogs(this.currentFilteredLogs, newPage);
    }
  },

  goToPage(pageNum) {
    const totalPages = Math.ceil(this.currentFilteredLogs.length / this.pageSize) || 1;
    if (pageNum >= 1 && pageNum <= totalPages) {
      this.renderMutationLogs(this.currentFilteredLogs, pageNum);
    }
  },

  renderMutationLogs(logs, page = 1) {
    this.currentFilteredLogs = logs || [];
    this.currentPage = page;

    const tbody = document.getElementById('mutationTableBody');
    if (!tbody) return;

    const total = this.currentFilteredLogs.length;
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

    if (!logs || logs.length === 0) {
      tbody.innerHTML = `
        <tr id="noResultsRow">
          <td colspan="9" class="py-12 text-center text-slate-400">
            <div class="flex flex-col items-center justify-center space-y-2">
              <i class="fa-solid fa-database text-3xl text-slate-300"></i>
              <p class="text-xs font-bold">No data mutation logs found in database</p>
              <p class="text-[10px] font-semibold text-slate-400">New CRUD operations and record edits will automatically appear here.</p>
            </div>
          </td>
        </tr>
      `;
      return;
    }

    let html = '';
    const esc = window.escapeHtml || (s => s);
    const pageLogs = this.currentFilteredLogs.slice(startIdx, endIdx);

    pageLogs.forEach(log => {
      const mutId = `#MUT-${log.audit_id}`;
      const rawDate = log.created_at || '';
      
      // Parse timestamp cleanly for Manila (+08:00) display
      let dateStr = 'N/A';
      let timeStr = '';

      if (rawDate) {
        const dt = new Date(rawDate.includes('T') ? rawDate : rawDate.replace(' ', 'T') + '+08:00');
        if (!isNaN(dt.getTime())) {
          dateStr = dt.toLocaleDateString('en-US', { timeZone: 'Asia/Manila', month: 'short', day: 'numeric', year: 'numeric' });
          timeStr = dt.toLocaleTimeString('en-US', { timeZone: 'Asia/Manila', hour: '2-digit', minute: '2-digit', hour12: true });
        } else {
          dateStr = rawDate.split(' ')[0] || 'N/A';
          timeStr = rawDate.split(' ')[1] || '';
        }
      }

      let actorName = 'System / Automated';
      if (log.users) {
        actorName = `${log.users.first_name || ''} ${log.users.last_name || ''}`.trim() || log.users.email || 'User';
      }

      const moduleName = (log.modules && log.modules.module_name) ? log.modules.module_name : (log.target_table ? log.target_table.toUpperCase() : 'System');
      const recordId = log.target_id ? `ID: ${log.target_id}` : (log.session_id ? `SESS-${log.session_id}` : 'REC-CORE');
      const actionField = log.action || 'Data Update';
      const isSuccess = (log.status || 'Success') === 'Success';

      // Smart Delta parsing from context_json
      let oldVal = 'None (New Record)';
      let newVal = isSuccess ? 'Success' : 'Failed';
      let oldJsonStr = 'null';
      let newJsonStr = '{}';
      let fieldSummaries = [];

      if (log.context_json) {
        try {
          const parsed = typeof log.context_json === 'string' ? JSON.parse(log.context_json) : log.context_json;
          
          const oldData = parsed.old_data || parsed.old || null;
          const newData = parsed.new_data || parsed.new || null;
          const changes = parsed.changes || null;

          if (oldData) {
            oldJsonStr = JSON.stringify(oldData, null, 2);
          } else if (parsed.before) {
            oldJsonStr = JSON.stringify(parsed.before, null, 2);
          } else {
            oldJsonStr = JSON.stringify({
              record_id: log.target_id || 'N/A',
              target_entity: log.target_table || 'system',
              snapshot: 'Initial Pre-mutation State'
            }, null, 2);
          }

          if (newData) {
            newJsonStr = JSON.stringify(newData, null, 2);
          } else if (parsed.after) {
            newJsonStr = JSON.stringify(parsed.after, null, 2);
          } else {
            newJsonStr = JSON.stringify(parsed, null, 2);
          }

          if (changes && typeof changes === 'object') {
            const keys = Object.keys(changes);
            keys.forEach(k => {
              const label = this.humanizeFieldName(k);
              const oVal = changes[k]?.old ?? 'None';
              const nVal = changes[k]?.new ?? 'None';
              fieldSummaries.push(`${label}: ${oVal} ➔ ${nVal}`);
            });

            if (keys.length > 0) {
              const firstKey = keys[0];
              const firstLabel = this.humanizeFieldName(firstKey);
              const oldDiff = changes[firstKey]?.old ?? 'None';
              const newDiff = changes[firstKey]?.new ?? 'None';
              oldVal = `${firstLabel}: ${oldDiff}`;
              newVal = `${firstLabel}: ${newDiff}`;

              if (keys.length > 1) {
                oldVal += ` (+${keys.length - 1} more)`;
                newVal += ` (+${keys.length - 1} more)`;
              }
            }
          } else if (oldData && newData && typeof oldData === 'object' && typeof newData === 'object') {
            // Compute diff between oldData and newData
            const diffKeys = [];
            Object.keys(newData).forEach(k => {
              if (oldData[k] !== newData[k]) {
                diffKeys.push(k);
                const label = this.humanizeFieldName(k);
                fieldSummaries.push(`${label}: ${oldData[k] ?? 'None'} ➔ ${newData[k] ?? 'None'}`);
              }
            });

            if (diffKeys.length > 0) {
              const firstK = diffKeys[0];
              const firstL = this.humanizeFieldName(firstK);
              oldVal = `${firstL}: ${oldData[firstK] ?? 'None'}`;
              newVal = `${firstL}: ${newData[firstK] ?? 'None'}`;
              if (diffKeys.length > 1) {
                oldVal += ` (+${diffKeys.length - 1} more)`;
                newVal += ` (+${diffKeys.length - 1} more)`;
              }
            } else {
              oldVal = 'Record Present';
              newVal = 'Updated';
            }
          } else if (oldData && !newData) {
            oldVal = 'Record Present';
            newVal = 'Archived / Deleted';
          } else if (!oldData && newData) {
            oldVal = 'None (New Record)';
            newVal = 'Record Created';
          }
        } catch (e) {
          oldJsonStr = JSON.stringify({
            record_id: log.target_id || 'N/A',
            target_table: log.target_table || 'system',
            description: log.description || 'Pre-mutation state'
          }, null, 2);
          newJsonStr = JSON.stringify({ raw_context: log.context_json }, null, 2);
        }
      } else {
        oldVal = log.target_table ? `${log.target_table} (Pre-State)` : 'Initial';
        newVal = isSuccess ? 'Committed' : 'Failed';
      }

      let actionBadgeHtml = '';
      if (actionField.includes('Create') || actionField.includes('Add')) {
        actionBadgeHtml = `<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-blue-50 text-blue-700 ring-1 ring-blue-600/20">${esc(actionField)}</span>`;
      } else if (actionField.includes('Delete') || actionField.includes('Remove')) {
        actionBadgeHtml = `<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-rose-50 text-rose-700 ring-1 ring-rose-600/20">${esc(actionField)}</span>`;
      } else {
        actionBadgeHtml = `<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-amber-50 text-amber-800 ring-1 ring-amber-600/20">${esc(actionField)}</span>`;
      }

      const formattedManilaTime = `${dateStr} at ${timeStr}`;

      html += `
        <tr class="hover:bg-slate-50/70 transition cursor-pointer" 
            onclick="window.civAudit.dataChanges.modal.openMutationModal(this)"
            data-id="${esc(mutId)}"
            data-actor="${esc(actorName)}"
            data-time="${esc(formattedManilaTime)}"
            data-module="${esc(moduleName)}"
            data-record="${esc(recordId)}"
            data-field="${esc(actionField)}"
            data-old="${esc(oldVal)}"
            data-new="${esc(newVal)}"
            data-reason="${esc(log.description || fieldSummaries.join(' | ') || 'System mutation record logged in database.')}"
            data-ip="${esc(log.ip_address || '127.0.0.1')}"
            data-method="${esc(log.request_method || 'POST')}"
            data-uri="${esc(log.request_uri || '/api')}"
            data-browser="${esc((log.browser || 'Browser') + ' - ' + (log.operating_system || 'OS'))}"
            data-old-json="${esc(oldJsonStr)}"
            data-new-json="${esc(newJsonStr)}">
          <td class="py-4 px-5 font-mono text-[11px] font-bold text-slate-500">${esc(mutId)}</td>
          <td class="py-4 px-5">
            <div class="font-bold text-slate-800">${esc(dateStr)}</div>
            <div class="text-[10px] text-slate-400 font-semibold mt-0.5">${esc(timeStr)}</div>
          </td>
          <td class="py-4 px-5 font-bold text-slate-800">${esc(actorName)}</td>
          <td class="py-4 px-5">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 ring-1 ring-slate-600/10">${esc(moduleName)}</span>
          </td>
          <td class="py-4 px-5 font-mono text-[11px] font-semibold text-slate-600">${esc(recordId)}</td>
          <td class="py-4 px-5">${actionBadgeHtml}</td>
          <td class="py-4 px-5 text-slate-500 font-medium text-xs truncate max-w-[130px]">${esc(oldVal)}</td>
          <td class="py-4 px-5 font-bold text-slate-800 text-xs truncate max-w-[130px]">${esc(newVal)}</td>
          <td class="py-4 px-5 text-right">
            <button class="text-xs font-bold text-[#176B87] hover:text-[#0f172a] transition flex items-center justify-end gap-1 ml-auto cursor-pointer">
              <i class="fa-solid fa-eye text-[10px]"></i> Inspect
            </button>
          </td>
        </tr>
      `;
    });

    tbody.innerHTML = html;

    // Auto-open mutation log modal if audit_id is specified in URL query
    const urlParams = new URLSearchParams(window.location.search);
    const auditIdParam = urlParams.get('audit_id');
    if (auditIdParam) {
      const targetRow = tbody.querySelector(`tr[data-id="#MUT-${auditIdParam}"]`);
      if (targetRow) {
        const newSearch = window.location.search.replace(new RegExp('[?&]audit_id=' + auditIdParam), '').replace(/^&/, '?');
        const cleanUrl = window.location.pathname + newSearch;
        window.history.replaceState({}, document.title, cleanUrl);
        window.civAudit.dataChanges.modal.openMutationModal(targetRow);
      } else {
        const matchIdx = this.currentFilteredLogs.findIndex(log => log.audit_id == auditIdParam);
        if (matchIdx !== -1) {
          const targetPage = Math.floor(matchIdx / this.pageSize) + 1;
          if (targetPage !== this.currentPage) {
            this.goToPage(targetPage);
          }
        }
      }
    }
    hideDataChangesSkeleton();
  }
};

function hideDataChangesSkeleton() {
  const skel = document.getElementById('dataChangesSkeleton');
  const real = document.getElementById('dataChangesRealContent');
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
  if (window.civAudit && window.civAudit.dataChanges && window.civAudit.dataChanges.ui) {
    window.civAudit.dataChanges.ui.changePage(delta);
  }
};

window.goToPage = function(pageNum) {
  if (window.civAudit && window.civAudit.dataChanges && window.civAudit.dataChanges.ui) {
    window.civAudit.dataChanges.ui.goToPage(pageNum);
  }
};

window.changePageSize = function(newSize) {
  if (window.civAudit && window.civAudit.dataChanges && window.civAudit.dataChanges.ui) {
    window.civAudit.dataChanges.ui.changePageSize(newSize);
  }
};
