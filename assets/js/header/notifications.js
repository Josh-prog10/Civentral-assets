// CIVENTRAL Notification Management Module
window.civNotif = window.civNotif || {};

(function() {
  const basePath = (typeof window.civentralBasePath !== 'undefined') ? window.civentralBasePath : '../';

  window.civNotif = {
    unreadCount: 0,
    pollingInterval: null,

    init() {
      this.fetchCount();
      // Start polling every 60 seconds
      this.pollingInterval = setInterval(() => this.fetchCount(), 60000);

      // Register click-outside to close dropdown
      document.addEventListener('click', (e) => {
        const dropdown = document.getElementById('notifDropdownMenu');
        const btn = document.getElementById('notifDropdownBtn');
        if (dropdown && !dropdown.classList.contains('hidden') && btn && !btn.contains(e.target) && !dropdown.contains(e.target)) {
          this.closeDropdown();
        }
      });
    },

    async fetchCount() {
      try {
        const res = await fetch(basePath + 'api/employee/notifications.php?action=unread_count');
        const data = await res.json();
        if (data.status === 'success') {
          this.unreadCount = parseInt(data.count) || 0;
          this.updateBadge();
        }
      } catch (err) {
        console.error('Failed to fetch unread count:', err);
      }
    },

    updateBadge() {
      const badge = document.getElementById('notifBadge');
      if (!badge) return;
      if (this.unreadCount > 0) {
        badge.innerText = this.unreadCount > 99 ? '99+' : this.unreadCount;
        badge.classList.remove('hidden');
      } else {
        badge.classList.add('hidden');
      }
    },

    async toggle(event) {
      if (event) event.stopPropagation();
      const dropdown = document.getElementById('notifDropdownMenu');
      if (!dropdown) return;

      if (dropdown.classList.contains('hidden')) {
        this.openDropdown();
        await this.loadNotificationsList();
      } else {
        this.closeDropdown();
      }
    },

    openDropdown() {
      const dropdown = document.getElementById('notifDropdownMenu');
      if (!dropdown) return;
      dropdown.classList.remove('hidden');
      setTimeout(() => {
        dropdown.classList.remove('scale-95', 'opacity-0');
        dropdown.classList.add('scale-100', 'opacity-100');
      }, 10);
    },

    closeDropdown() {
      const dropdown = document.getElementById('notifDropdownMenu');
      if (!dropdown) return;
      dropdown.classList.remove('scale-100', 'opacity-100');
      dropdown.classList.add('scale-95', 'opacity-0');
      setTimeout(() => dropdown.classList.add('hidden'), 150);
    },

    async loadNotificationsList() {
      const container = document.getElementById('notifItemsContainer');
      if (!container) return;

      try {
        const res = await fetch(basePath + 'api/employee/notifications.php?action=list&limit=15');
        const data = await res.json();
        
        if (data.status === 'success' && Array.isArray(data.notifications)) {
          this.renderList(data.notifications);
        } else {
          container.innerHTML = `
            <div class="py-12 text-center text-slate-400 dark:text-slate-500">
              <i class="fa-solid fa-bell-slash text-2xl mb-1.5 opacity-55"></i>
              <p class="text-xs font-bold">Failed to load notifications</p>
            </div>
          `;
        }
      } catch (err) {
        console.error('Failed to load notifications list:', err);
        container.innerHTML = `
          <div class="py-12 text-center text-slate-400 dark:text-slate-500">
            <i class="fa-solid fa-triangle-exclamation text-2xl mb-1.5 text-rose-500"></i>
            <p class="text-xs font-bold">Connection error</p>
          </div>
        `;
      }
    },

    renderList(notifications) {
      const container = document.getElementById('notifItemsContainer');
      if (!container) return;

      if (notifications.length === 0) {
        container.innerHTML = `
          <div class="py-12 text-center text-slate-400 dark:text-slate-500 shrink-0">
            <i class="fa-solid fa-bell-slash text-2xl mb-1.5 opacity-55"></i>
            <p class="text-xs font-bold">You're all caught up!</p>
            <p class="text-[10px] text-slate-400 mt-0.5">No unread notifications.</p>
          </div>
        `;
        return;
      }

      container.innerHTML = '';
      notifications.forEach(notif => {
        container.appendChild(this.buildNotificationItem(notif));
      });
    },

    buildNotificationItem(notif) {
      const isUnread = notif.notification_status === 'Unread';
      const div = document.createElement('div');
      
      // Outer layout wrapper
      div.className = `p-4 flex gap-3 transition cursor-pointer select-none relative border-l-2 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 ${
        isUnread 
          ? 'bg-blue-50/30 dark:bg-[#EEF5FF]/5 border-l-brand-medium' 
          : 'border-l-transparent'
      }`;

      // Left Icon Avatar / Priority badge indicator
      const priorityColors = this.getPriorityColors(notif.priority);
      const avatarSrc = notif.actor_pic && notif.actor_pic !== 'default-avatar.png'
        ? basePath + notif.actor_pic
        : null;

      let leftIconHTML = '';
      if (avatarSrc) {
        leftIconHTML = `<img src="${avatarSrc}" alt="Actor avatar" class="h-9 w-9 rounded-lg object-cover border border-slate-200 dark:border-slate-700 shrink-0">`;
      } else {
        const initials = ((notif.actor_first || 'U')[0] + (notif.actor_last || 'S')[0]).toUpperCase();
        leftIconHTML = `
          <div class="h-9 w-9 rounded-lg bg-brand-light dark:bg-slate-800 border border-brand-border dark:border-slate-700 flex items-center justify-center text-brand-dark dark:text-brand-medium font-black text-xs shrink-0">
            ${initials}
          </div>
        `;
      }

      // Action routing URL
      const routePage = this.getAuditRoute(notif);
      const auditId = notif.audit_id || '';
      const actionUrl = `${basePath}pages/audit/${routePage}?audit_id=${auditId}`;

      const esc = window.escapeHtml || (s => s ? s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;') : '');
      const actorName = (notif.actor_first || notif.actor_last) 
        ? `${notif.actor_first || ''} ${notif.actor_last || ''}`.trim() 
        : 'System User';

      div.innerHTML = `
        <!-- Left Side: Actor Avatar -->
        ${leftIconHTML}

        <!-- Right Side: Content -->
        <div class="flex-1 min-w-0">
          <div class="flex items-start justify-between gap-1.5">
            <span class="font-bold text-slate-800 dark:text-slate-200 text-xs truncate leading-snug pr-4">${esc(notif.title)}</span>
            <span class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase shrink-0 tracking-wider ${priorityColors.badge}">
              ${esc(notif.priority)}
            </span>
          </div>
          <p class="text-[10px] text-brand-dark dark:text-brand-medium font-bold mt-0.5 flex items-center gap-1">
            <i class="fa-solid fa-user-gear text-[9px] opacity-75"></i> ${esc(actorName)}
          </p>
          <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-relaxed line-clamp-2">${esc(notif.message)}</p>
          <div class="flex items-center justify-between mt-2.5">
            <span class="text-[10px] text-slate-400 font-semibold flex items-center gap-1">
              <i class="fa-regular fa-clock text-[9px]"></i>
              ${this.timeAgo(notif.created_at)}
            </span>
            <a href="${actionUrl}" onclick="window.civNotif.handleItemClick(event, ${notif.notification_id}, '${actionUrl}')" 
               class="text-[10px] text-brand-medium hover:text-[#176B87] dark:hover:text-white font-bold transition flex items-center gap-0.5">
               View Log <i class="fa-solid fa-arrow-right text-[8px]"></i>
            </a>
          </div>
        </div>

        <!-- Unread Badge Marker dot -->
        ${isUnread ? `<span class="absolute top-4 right-4 h-2 w-2 rounded-full bg-brand-medium"></span>` : ''}
      `;

      return div;
    },

    getPriorityColors(priority) {
      switch (priority) {
        case 'Critical':
          return { badge: 'bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200/50 dark:border-rose-800/30' };
        case 'High':
          return { badge: 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200/50 dark:border-amber-800/30' };
        case 'Normal':
          return { badge: 'bg-blue-100 dark:bg-slate-800 text-blue-700 dark:text-blue-400 border border-blue-200/50 dark:border-slate-700' };
        default:
          return { badge: 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700' };
      }
    },

    getAuditRoute(notif) {
      const action = (notif.action_name || '').toLowerCase();
      const table = (notif.target_table || '').toLowerCase();
      
      if (action.includes('login') || action.includes('logout') || action.includes('2fa') || action.includes('session') || table === 'login_history') {
        return 'login-history.php';
      }
      
      const mutationKeywords = ['create', 'edit', 'update', 'delete', 'archive', 'restore', 'mutation', 'change'];
      const isMutation = mutationKeywords.some(keyword => action.includes(keyword)) || ['users', 'departments', 'positions', 'roles', 'citizen_users'].includes(table);
      
      if (isMutation && !action.includes('matrix') && !action.includes('permissions')) {
        return 'data-changes.php';
      }
      
      return 'user-activities.php';
    },

    async handleItemClick(event, notifId, targetUrl) {
      event.preventDefault();
      event.stopPropagation();
      
      try {
        // Mark as read asynchronously in the background
        await fetch(basePath + 'api/employee/notifications.php?action=mark_read', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ notification_id: notifId })
        });
      } catch (err) {
        console.error('Failed to mark notification as read:', err);
      }

      this.closeDropdown();
      window.location.href = targetUrl;
    },

    async markAllRead(event) {
      if (event) {
        event.preventDefault();
        event.stopPropagation();
      }

      try {
        const res = await fetch(basePath + 'api/employee/notifications.php?action=mark_all_read', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        
        if (data.status === 'success') {
          this.unreadCount = 0;
          this.updateBadge();
          await this.loadNotificationsList();
          
          if (window.showToast) {
            window.showToast('Success', 'All notifications marked as read.');
          }
        }
      } catch (err) {
        console.error('Failed to mark all as read:', err);
      }
    },

    timeAgo(dateStr) {
      if (!dateStr) return 'N/A';
      
      // Parse database timestamp cleanly into Manila (+08:00) context
      const cleaned = dateStr.includes('T') ? dateStr : dateStr.replace(' ', 'T') + '+08:00';
      const created = new Date(cleaned);
      const now = new Date();
      
      const seconds = Math.floor((now - created) / 1000);
      if (seconds < 5) return 'Just now';
      if (seconds < 60) return seconds + 's ago';
      
      const minutes = Math.floor(seconds / 60);
      if (minutes < 60) return minutes + 'm ago';
      
      const hours = Math.floor(minutes / 60);
      if (hours < 24) return hours + 'h ago';
      
      const days = Math.floor(hours / 24);
      if (days === 1) return 'Yesterday';
      if (days < 7) return days + 'd ago';
      
      return created.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }
  };

  // Auto-init notification handler when DOM is loaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => window.civNotif.init());
  } else {
    window.civNotif.init();
  }
})();
