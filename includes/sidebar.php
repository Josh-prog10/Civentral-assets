<?php
      $currentPage = basename($_SERVER['PHP_SELF']);

      $usermanagementPages = [
        'user-directory.php',
        'create-account.php',
        'account-status.php'
      ];

      $rolesmanagementPages = [
        'roles-management.php',
        'permissions.php',
        'module-management.php',
        'resource-management.php',
        'resourcemanagement.php',
        'action-management.php',
        'actionmanagement.php',
        'access-control.php'
      ];

      $departmentmanagementPages = [
        'departments.php'
      ];
      $citizenPages = [
        'citizen-directory.php',
        'citizen-account.php'
      ];
      $cemeteryPages = [
        'cdb.php',
        'cemtery_lots.php',
        'deceased.php',
        'burials.php',
        'cemeteries.php'
      ];
      $parksPages = [
        'pdb.php',
        'facility.php',
        'reservation.php',
        'reserves.php',
        'avail.php'
      ];
      $lguPages = [
        'ldb.php',
        'facilities.php',
        'reservations.php',
        'reserve.php',
        'availability.php'
      ];
      $waterDrainagePages = [
        'wdb.php',
        'categories.php',
        'requests.php',
        'submit.php',
        'view.php'
      ];
      $assetsPages = [
        'adb.php',
        'view.php',
        'add.php',
        'edit.php',
        'category.php'
      ];
      $scholarshipPages = [
        'scholarship-types.php'
      ];
      $auditPages = [
        'user-activities.php',
        'login-history.php',
        'data-changes.php'
      ];

      $isSuperAdmin = !empty($headerUser['is_superadmin']) || !empty($headerUser['is_global_access']);
      $userGrantedRes = $headerUser['granted_resources'] ?? [];

      // Dynamic RBAC Permission Checker
      $hasResourceAccess = function($keywords) use ($isSuperAdmin, $userGrantedRes) {
          if ($isSuperAdmin) return true;
          if (empty($userGrantedRes)) return false;
          if (is_string($keywords)) $keywords = [$keywords];
          foreach ($userGrantedRes as $resName) {
              $resLower = strtolower($resName);
              foreach ($keywords as $kw) {
                  if (strpos($resLower, strtolower($kw)) !== false) return true;
              }
          }
          return false;
      };

      // ---------------------------------------------------------------
      // Which sidebar dropdown should be open for the CURRENT page.
      // Used by the script at the bottom of this file so that dropdowns
      // from other modules always collapse when you navigate away.
      // ---------------------------------------------------------------
      $currentPath = str_replace('\\', '/', $_SERVER['PHP_SELF']);

      // Helper: page must match AND (optionally) live inside a folder,
      // so shared filenames like view.php don't light up two modules.
      $inModule = function ($pages, $folder = null) use ($currentPage, $currentPath) {
          if (!in_array($currentPage, $pages, true)) return false;
          if ($folder !== null && strpos($currentPath, '/' . $folder . '/') === false) return false;
          return true;
      };

      $activeSidebarDropdowns = [];

      if ($inModule($usermanagementPages))        $activeSidebarDropdowns[] = 'userDropdown';
      if ($inModule($rolesmanagementPages))       $activeSidebarDropdowns[] = 'roleDropdown';
      if ($inModule($departmentmanagementPages))  $activeSidebarDropdowns[] = 'deptDropdown';
      if ($inModule($citizenPages))               $activeSidebarDropdowns[] = 'citizenDropdown';
      if ($inModule($cemeteryPages, 'cemetery'))  $activeSidebarDropdowns[] = 'cemeteryDropdown';
      if ($inModule($parksPages, 'parks'))        $activeSidebarDropdowns[] = 'parksDropdown';
      if ($inModule($lguPages, 'lgu-facilities')) $activeSidebarDropdowns[] = 'lguDropdown';
      if ($inModule($waterDrainagePages, 'water-drainage')) $activeSidebarDropdowns[] = 'wdDropdown';
      if ($inModule($assetsPages, 'assets'))      $activeSidebarDropdowns[] = 'assetsDropdown';
      if ($inModule($auditPages))                 $activeSidebarDropdowns[] = 'auditDropdown';
    ?>
    
    <aside id="sidebar" class="bg-brand-light text-slate-600 w-72 min-h-[calc(100vh-5rem)] flex flex-col justify-between transition-all duration-300 border-r border-brand-border/60 sticky top-20 h-[calc(100vh-5rem)] z-30 shrink-0 shadow-sm">
      
      <div class="flex flex-col h-full overflow-hidden">
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto custom-scrollbar">
          
          <div class="sidebar-divider px-1 pb-3 mb-2 border-b">
            <button onclick="toggleSidebar()" class="sidebar-collapse-btn w-full py-2 rounded-xl border flex items-center justify-center focus:outline-none transition cursor-pointer shadow-xs" title="Collapse Menu Panel">
              <i id="toggleArrow" class="fa-solid fa-chevron-left text-xs"></i>
            </button>
          </div>

          <span class="sidebar-text text-[9px] font-bold tracking-widest text-slate-400 uppercase block px-3 mb-2">Main Controls</span>
          
          <a href="<?php echo $basePath ?? '../'; ?>pages/dashboard.php" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-xs tracking-wide transition cursor-pointer <?php echo $currentPage == 'dashboard.php' ? 'bg-white text-brand-dark border border-brand-border font-bold shadow-xs' : 'hover:bg-white dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-brand-dark dark:hover:text-[#86B6F6] border border-transparent font-semibold'; ?>">
            <i class="fa-solid fa-table-columns text-sm <?php echo $currentPage == 'dashboard.php' ? 'text-brand-medium' : 'text-slate-400'; ?>"></i>
            <span class="sidebar-text truncate">Dashboard Overview</span>
          </a>

          <?php 
          $canAccessUserMgmt = $isSuperAdmin || $hasResourceAccess(['user directory', 'user account', 'users account', 'account status', 'user', 'account', 'employee']);
          if ($canAccessUserMgmt): 
          ?>
          <div class="space-y-1">
          <button onclick="toggleDropdown('userDropdown', 'userChevron')" class="dropdown-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs tracking-wide transition group cursor-pointer <?php echo in_array($currentPage, $usermanagementPages) ? 'bg-white text-brand-dark border border-brand-border font-bold shadow-xs' : 'hover:bg-white dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-brand-dark dark:hover:text-[#86B6F6] border border-transparent font-semibold'; ?>">
            <div class="flex items-center space-x-3">
                <i class="fa-solid fa-users-gear text-sm <?php echo in_array($currentPage, $usermanagementPages) ? 'text-brand-medium' : 'text-slate-400'; ?> group-hover:text-brand-medium transition"></i>
                <span class="sidebar-text truncate">User Management</span>
            </div>
            <div class="dropdown-right">
                <i id="userChevron"
                  class="fa-solid fa-chevron-down text-[10px] opacity-60 dropdown-chevron transition-transform duration-200 <?php echo in_array($currentPage, $usermanagementPages) ? 'rotate-180' : ''; ?>"></i>
            </div>
            </button>
            <div id="userDropdown" class="<?php echo in_array($currentPage, $usermanagementPages) ? '' : 'hidden'; ?> pl-8 pr-2 space-y-0.5 font-medium sidebar-text">
              <a href="<?php echo $basePath ?? '../'; ?>pages/usermanagement/user-directory.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'user-directory.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>"><i class="fa-solid fa-user-pen text-[10px] <?php echo $currentPage == 'user-directory.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>User Directory</span></a>

              <?php if ($isSuperAdmin || $hasResourceAccess(['users account', 'user account', 'create account'])): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/usermanagement/create-account.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'create-account.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>"><i class="fa-solid fa-user-plus text-[10px] <?php echo $currentPage == 'create-account.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Create Staff Accounts</span></a>
              <?php endif; ?>

              <?php if ($isSuperAdmin || $hasResourceAccess(['account status', 'status control', 'status'])): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/usermanagement/account-status.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'account-status.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>"><i class="fa-solid fa-user-check text-[10px] <?php echo $currentPage == 'account-status.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Activate/Deactivate</span></a>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>

          <?php 
          $canAccessRoleMgmt = $isSuperAdmin || $hasResourceAccess(['role', 'permission', 'module', 'resource', 'access control']);
          if ($canAccessRoleMgmt): 
          ?>
          <div class="space-y-1">
            <button onclick="toggleDropdown('roleDropdown', 'roleChevron')" class="dropdown-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs tracking-wide transition group cursor-pointer <?php echo in_array($currentPage, $rolesmanagementPages) ? 'bg-white text-brand-dark border border-brand-border font-bold shadow-xs' : 'hover:bg-white dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-brand-dark dark:hover:text-[#86B6F6] border border-transparent font-semibold'; ?>">
              <div class="flex items-center space-x-3">
                  <i class="fa-solid fa-user-shield text-sm <?php echo in_array($currentPage, $rolesmanagementPages) ? 'text-brand-medium' : 'text-slate-400'; ?> group-hover:text-brand-medium transition"></i>
                  <span class="sidebar-text truncate">Role & Permissions</span>
              </div>
              <div class="dropdown-right">
                  <i id="roleChevron"
                    class="fa-solid fa-chevron-down text-[10px] opacity-60 dropdown-chevron transition-transform duration-200 <?php echo in_array($currentPage, $rolesmanagementPages) ? 'rotate-180' : ''; ?>"></i>
              </div>
            </button>
            <div id="roleDropdown" class="<?php echo in_array($currentPage, $rolesmanagementPages) ? '' : 'hidden'; ?> pl-8 pr-2 space-y-0.5 font-medium sidebar-text">
              <?php if ($isSuperAdmin || $hasResourceAccess(['roles'])): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/rolespermission/roles-management.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'roles-management.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>"><i class="fa-solid fa-users text-[10px] <?php echo $currentPage == 'roles-management.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Roles</span></a>
              <?php endif; ?>

              <?php if ($isSuperAdmin || $hasResourceAccess(['module management'])): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/rolespermission/module-management.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'module-management.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>"><i class="fa-solid fa-cubes text-[10px] <?php echo $currentPage == 'module-management.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Module Management</span></a>
              <?php endif; ?>

              <?php if ($isSuperAdmin || $hasResourceAccess(['resource management'])): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/rolespermission/resource-management.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo (in_array($currentPage, ['resource-management.php', 'resourcemanagement.php'])) ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>"><i class="fa-solid fa-file-lines text-[10px] <?php echo (in_array($currentPage, ['resource-management.php', 'resourcemanagement.php'])) ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Resource Management</span></a>
              <?php endif; ?>

              <?php if ($isSuperAdmin || $hasResourceAccess(['action management'])): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/rolespermission/action-management.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo (in_array($currentPage, ['action-management.php', 'actionmanagement.php'])) ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>"><i class="fa-solid fa-bolt text-[10px] <?php echo (in_array($currentPage, ['action-management.php', 'actionmanagement.php'])) ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Action Management</span></a>
              <?php endif; ?>

              <?php if ($isSuperAdmin || $hasResourceAccess(['permission builder'])): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/rolespermission/permissions.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'permissions.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>"><i class="fa-solid fa-key text-[10px] <?php echo $currentPage == 'permissions.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Permission Builder</span></a>
              <?php endif; ?>

              <?php if ($isSuperAdmin || $hasResourceAccess(['role permission matrix'])): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/rolespermission/access-control.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'access-control.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>"><i class="fa-solid fa-shield-halved text-[10px] <?php echo $currentPage == 'access-control.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Role Permission Matrix</span></a>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>

          <?php 
          $canAccessDeptMgmt = $hasResourceAccess(['department', 'position', 'sitemap', 'department management']);
          if ($canAccessDeptMgmt): 
          ?>
          <div class="space-y-1">
            <button onclick="toggleDropdown('deptDropdown', 'deptChevron')" class="dropdown-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs tracking-wide transition group cursor-pointer <?php echo in_array($currentPage, $departmentmanagementPages) ? 'bg-white text-brand-dark border border-brand-border font-bold shadow-xs' : 'hover:bg-white dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-brand-dark dark:hover:text-[#86B6F6] border border-transparent font-semibold'; ?>">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-sitemap text-sm <?php echo in_array($currentPage, $departmentmanagementPages) ? 'text-brand-medium' : 'text-slate-400'; ?> group-hover:text-brand-medium transition"></i>
                    <span class="sidebar-text truncate">Department Management</span>
              </div>
                <div class="dropdown-right">
                    <i id="deptChevron"
                      class="fa-solid fa-chevron-down text-[10px] opacity-60 dropdown-chevron transition-transform duration-200 <?php echo in_array($currentPage, $departmentmanagementPages) ? 'rotate-180' : ''; ?>"></i>
                </div>
            </button>

            <div id="deptDropdown" class="<?php echo in_array($currentPage, $departmentmanagementPages) ? '' : 'hidden'; ?> pl-8 pr-2 space-y-0.5 font-medium sidebar-text">
              <a href="<?php echo $basePath ?? '../'; ?>pages/department/departments.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'departments.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>"><i class="fa-solid fa-building text-[10px] <?php echo $currentPage == 'departments.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Departments</span></a>
            </div>
          </div>
          <?php endif; ?>

          <?php 
          $canAccessCitizenMgmt = $hasResourceAccess(['citizen', 'kyc', 'verification']);
          if ($canAccessCitizenMgmt): 
          ?>
          <div class="space-y-1">
            <button onclick="toggleDropdown('citizenDropdown', 'citizenChevron')" class="dropdown-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs tracking-wide transition group cursor-pointer <?php echo in_array($currentPage, $citizenPages) ? 'bg-white text-brand-dark border border-brand-border font-bold shadow-xs' : 'hover:bg-white dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-brand-dark dark:hover:text-[#86B6F6] border border-transparent font-semibold'; ?>">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-address-book text-sm <?php echo in_array($currentPage, $citizenPages) ? 'text-brand-medium' : 'text-slate-400'; ?> group-hover:text-brand-medium transition"></i>
                    <span class="sidebar-text truncate">Citizen Management</span>
              </div>
                <div class="dropdown-right">
                    <i id="citizenChevron"
                      class="fa-solid fa-chevron-down text-[10px] opacity-60 dropdown-chevron transition-transform duration-200 <?php echo in_array($currentPage, $citizenPages) ? 'rotate-180' : ''; ?>"></i>
                </div>
            </button>

          <div id="citizenDropdown" class="<?php echo in_array($currentPage, $citizenPages) ? '' : 'hidden'; ?> pl-8 pr-2 space-y-0.5 font-medium sidebar-text">
              <?php if ($hasResourceAccess('citizen directory')): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/citizen/citizen-directory.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'citizen-directory.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>"><i class="fa-solid fa-id-card text-[10px] <?php echo $currentPage == 'citizen-directory.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Citizen Directory</span></a>
              <?php endif; ?>

              <?php if ($hasResourceAccess(['citizen account', 'kyc', 'verification'])): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/citizen/citizen-account.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'citizen-account.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>"><i class="fa-solid fa-database text-[10px] <?php echo $currentPage == 'citizen-account.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Citizen Account</span></a>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>

          <?php 
          $canAccessCemeteryMgmt = $isSuperAdmin || $hasResourceAccess(['cemetery', 'burial', 'lot', 'deceased']);
          if ($canAccessCemeteryMgmt): 
          ?>
          <div class="space-y-1">
            <button onclick="toggleDropdown('cemeteryDropdown', 'cemeteryChevron')" class="dropdown-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs tracking-wide transition group cursor-pointer <?php echo in_array($currentPage, $cemeteryPages) ? 'bg-white text-brand-dark border border-brand-border font-bold shadow-xs' : 'hover:bg-white dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-brand-dark dark:hover:text-[#86B6F6] border border-transparent font-semibold'; ?>">
              <div class="flex items-center space-x-3">
                  <i class="fa-solid fa-user-shield text-sm <?php echo in_array($currentPage, $cemeteryPages) ? 'text-brand-medium' : 'text-slate-400'; ?> group-hover:text-brand-medium transition"></i>
                  <span class="sidebar-text truncate">Cemetery Management</span>
              </div>
              <div class="dropdown-right">
                  <i id="cemeteryChevron"
                    class="fa-solid fa-chevron-down text-[10px] opacity-60 dropdown-chevron transition-transform duration-200 <?php echo in_array($currentPage, $cemeteryPages) ? 'rotate-180' : ''; ?>"></i>
              </div>
            </button>
            <div id="cemeteryDropdown" class="<?php echo in_array($currentPage, $cemeteryPages) ? '' : 'hidden'; ?> pl-8 pr-2 space-y-0.5 font-medium sidebar-text">
              <?php if ($isSuperAdmin || $hasResourceAccess(['cemetery','lot'])): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/cemetery/cemtery_lots.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'cemtery_lots.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>"><i class="fa-solid fa-users text-[10px] <?php echo $currentPage == 'cemtery_lots.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Cemetery Lot</span></a>
              <?php endif; ?>

              <?php if ($isSuperAdmin || $hasResourceAccess(['cemetery', 'deceased'])): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/cemetery/deceased.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'deceased.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>"><i class="fa-solid fa-cubes text-[10px] <?php echo $currentPage == 'deceased.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Deceased Records</span></a>
              <?php endif; ?>

              <?php if ($isSuperAdmin || $hasResourceAccess(['cemetery', 'burial'])): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/cemetery/burials.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo (in_array($currentPage, ['cdb.php', 'cemtery_lots.php', 'deceased.php', 'burials.php'])) ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>"><i class="fa-solid fa-file-lines text-[10px] <?php echo (in_array($currentPage, ['burials.php', 'burial.php'])) ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Burial Records</span></a>
              <?php endif; ?>

              <?php if ($isSuperAdmin || $hasResourceAccess(['cemetery'])): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/cemetery/cemeteries.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'cemeteries.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>">
              <i class="fa-solid fa-map-location-dot text-[10px] <?php echo $currentPage == 'cemeteries.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Cemeteries</span></a>
              <?php endif; ?>

              <?php if ($isSuperAdmin || $hasResourceAccess(['cemetery'])): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/cemetery/cdb.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo (in_array($currentPage, ['cdb.php'])) ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>"><i class="fa-solid fa-bolt text-[10px] <?php echo (in_array($currentPage, ['cdb.php'])) ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Dashboard</span></a>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>
          
          <?php
          // Check permission for Parks & Recreation module
          $canAccessParks = $isSuperAdmin || $hasResourceAccess(['parks', 'recreation']);
          if ($canAccessParks):
          ?>
          <div class="space-y-1">
            <button onclick="toggleDropdown('parksDropdown', 'parksChevron')" 
            class="dropdown-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs tracking-wide transition group cursor-pointer <?php echo in_array($currentPage, $parksPages) ? 'bg-white text-brand-dark border border-brand-border font-bold shadow-xs' : 'hover:bg-white dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-brand-dark dark:hover:text-[#86B6F6] border border-transparent font-semibold'; ?>">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-tree text-sm <?php echo in_array($currentPage, $parksPages) ? 'text-brand-medium' : 'text-slate-400'; ?> group-hover:text-brand-medium transition"></i>
                    <span class="sidebar-text truncate">Parks & Recreation Schedulling</span>
              </div>
                <div class="dropdown-right">
                    <i id="parksChevron"
                    class="fa-solid fa-chevron-down text-[10px] opacity-60 dropdown-chevron transition-transform duration-200 <?php echo in_array($currentPage, $parksPages) ? 'rotate-180' : ''; ?>"></i>
              </div>
          </button>

          <div id="parksDropdown" class="<?php echo in_array($currentPage, $parksPages) ? '' : 'hidden'; ?> pl-8 pr-2 space-y-0.5 font-medium sidebar-text">
              <?php if ($isSuperAdmin || $hasResourceAccess(['parks', 'facility'])): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/parks/facility.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'facility.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>">
                  <i class="fa-solid fa-building text-[10px] <?php echo $currentPage == 'facility.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Parks</span>
              </a>
              <?php endif; ?>
              <?php if ($isSuperAdmin || $hasResourceAccess(['parks', 'reservation'])): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/parks/reservation.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'reservation.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>">
                  <i class="fa-solid fa-calendar-check text-[10px] <?php echo $currentPage == 'reservation.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Reservations</span>
              </a>
              <a href="<?php echo $basePath ?? '../'; ?>pages/parks/reserves.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'reserves.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>">
                  <i class="fa-solid fa-plus text-[10px] <?php echo $currentPage == 'reserves.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>New Reservation</span>
              </a>
              <?php endif; ?>
              <?php if ($isSuperAdmin || $hasResourceAccess(['parks', 'availability'])): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/parks/avail.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'avail.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>">
                  <i class="fa-solid fa-eye text-[10px] <?php echo $currentPage == 'avail.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Availability</span>
              </a>
              <?php endif; ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/parks/pdb.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'pdb.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>">
                  <i class="fa-solid fa-chart-simple text-[10px] <?php echo $currentPage == 'pdb.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Dashboard</span>
              </a>
            </div>
          </div>
          <?php endif; ?>

          <?php
          // Check permission for LGU Facility module
          $canAccessLgu = $isSuperAdmin || $hasResourceAccess(['lgu', 'facility', 'facilities', 'facility_manage', 'facility management', 'reservation', 'availability']);
          if ($canAccessLgu):
          ?>
          <div class="space-y-1">
            <button onclick="toggleDropdown('lguDropdown', 'lguChevron')" 
            class="dropdown-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs tracking-wide transition group cursor-pointer <?php echo in_array($currentPage, $lguPages) ? 'bg-white text-brand-dark border border-brand-border font-bold shadow-xs' : 'hover:bg-white dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-brand-dark dark:hover:text-[#86B6F6] border border-transparent font-semibold'; ?>">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-building-columns text-sm <?php echo in_array($currentPage, $lguPages) ? 'text-brand-medium' : 'text-slate-400'; ?> group-hover:text-brand-medium transition"></i>
                    <span class="sidebar-text truncate">Facility Reservation System</span>
              </div>
                <div class="dropdown-right">
                    <i id="lguChevron"
                    class="fa-solid fa-chevron-down text-[10px] opacity-60 dropdown-chevron transition-transform duration-200 <?php echo in_array($currentPage, $lguPages) ? 'rotate-180' : ''; ?>"></i>
              </div>
          </button>

        <div id="lguDropdown" class="<?php echo in_array($currentPage, $lguPages) ? '' : 'hidden'; ?> pl-8 pr-2 space-y-0.5 font-medium sidebar-text">
            <?php if ($isSuperAdmin || $hasResourceAccess(['lgu', 'facility', 'facilities', 'facility_manage', 'facility management'])): ?>
            <a href="<?php echo $basePath ?? '../'; ?>pages/lgu-facilities/facilities.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'facilities.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>">
                <i class="fa-solid fa-warehouse text-[10px] <?php echo $currentPage == 'facilities.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Facilities</span>
            </a>
            <?php endif; ?>
            <?php if ($isSuperAdmin || $hasResourceAccess(['lgu', 'reservation'])): ?>
            <a href="<?php echo $basePath ?? '../'; ?>pages/lgu-facilities/reservations.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'reservations.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>">
                <i class="fa-solid fa-calendar-check text-[10px] <?php echo $currentPage == 'reservations.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Reservations</span>
            </a>
            <a href="<?php echo $basePath ?? '../'; ?>pages/lgu-facilities/reserve.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'reserve.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>">
                <i class="fa-solid fa-plus text-[10px] <?php echo $currentPage == 'reserve.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>New Reservation</span>
            </a>
            <?php endif; ?>
            <?php if ($isSuperAdmin || $hasResourceAccess(['lgu', 'availability'])): ?>
            <a href="<?php echo $basePath ?? '../'; ?>pages/lgu-facilities/availability.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'availability.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>">
                <i class="fa-solid fa-eye text-[10px] <?php echo $currentPage == 'availability.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Availability</span>
            </a>
            <?php endif; ?>
            <a href="<?php echo $basePath ?? '../'; ?>pages/lgu-facilities/ldb.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'ldb.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>">
                <i class="fa-solid fa-chart-simple text-[10px] <?php echo $currentPage == 'ldb.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Dashboard</span>
              </a>
            </div>
          </div>
          <?php endif; ?>

          <?php
          $waterDrainageAccessKeywords = ['water_drainage', 'water drainage', 'water supply', 'drainage'];
          $canAccessWaterDrainage = $isSuperAdmin || $hasResourceAccess($waterDrainageAccessKeywords);
          if ($canAccessWaterDrainage):
          ?>
          <div class="space-y-1">
            <button onclick="toggleDropdown('wdDropdown', 'wdChevron')" 
            class="dropdown-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs tracking-wide transition group cursor-pointer <?php echo in_array($currentPage, ['wdb.php', 'requests.php', 'submit.php', 'view.php', 'categories.php']) ? 'bg-white text-brand-dark border border-brand-border font-bold shadow-xs' : 'hover:bg-white dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-brand-dark dark:hover:text-[#86B6F6] border border-transparent font-semibold'; ?>">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-water text-sm <?php echo in_array($currentPage, ['wdb.php', 'requests.php', 'submit.php', 'view.php', 'categories.php']) ? 'text-brand-medium' : 'text-slate-400'; ?> group-hover:text-brand-medium transition"></i>
                    <span class="sidebar-text truncate">Water Supply & Drainage Requests</span>
              </div>
                <div class="dropdown-right">
                    <i id="wdChevron"
                    class="fa-solid fa-chevron-down text-[10px] opacity-60 dropdown-chevron transition-transform duration-200 <?php echo in_array($currentPage, ['wdb.php', 'requests.php', 'submit.php', 'view.php', 'categories.php']) ? 'rotate-180' : ''; ?>"></i>
              </div>
          </button>

        <div id="wdDropdown" class="<?php echo in_array($currentPage, ['wdb.php', 'requests.php', 'submit.php', 'view.php', 'categories.php']) ? '' : 'hidden'; ?> pl-8 pr-2 space-y-0.5 font-medium sidebar-text">
            <?php if ($isSuperAdmin || $hasResourceAccess($waterDrainageAccessKeywords)): ?>
            <a href="<?php echo $basePath ?? '../'; ?>pages/water-drainage/requests.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'requests.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>">
                <i class="fa-solid fa-list text-[10px] <?php echo $currentPage == 'requests.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Requests</span>
            </a>
            <a href="<?php echo $basePath ?? '../'; ?>pages/water-drainage/submit.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'submit.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>">
                <i class="fa-solid fa-plus text-[10px] <?php echo $currentPage == 'submit.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Submit Request</span>
            </a>
            <?php endif; ?>
            <?php if ($isSuperAdmin || $hasResourceAccess(['water_drainage_manage'])): ?>
            <a href="<?php echo $basePath ?? '../'; ?>pages/water-drainage/categories.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'categories.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>">
                <i class="fa-solid fa-tags text-[10px] <?php echo $currentPage == 'categories.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Categories</span>
            </a>
            <?php endif; ?>
            <a href="<?php echo $basePath ?? '../'; ?>pages/water-drainage/wdb.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'wdb.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>">
            <i class="fa-solid fa-chart-simple text-[10px] <?php echo $currentPage == 'wdb.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Dashboard</span>
            </a>
          </div>
        </div>
        <?php endif; ?>

          <?php
$assetAccessKeywords = ['assets', 'asset', 'asset inventory', 'asset inventory tracker'];
$assetManageKeywords = ['assets_manage', 'asset', 'asset management', 'asset inventory', 'asset inventory tracker'];
$canAccessAssets = $isSuperAdmin || $hasResourceAccess($assetAccessKeywords);
if ($canAccessAssets):
?>
<div class="space-y-1">
    <button onclick="toggleDropdown('assetsDropdown', 'assetsChevron')" 
            class="dropdown-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs tracking-wide transition group cursor-pointer <?php echo in_array($currentPage, ['adb.php', 'view.php', 'add.php', 'edit.php', 'category.php']) ? 'bg-white text-brand-dark border border-brand-border font-bold shadow-xs' : 'hover:bg-white dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-brand-dark dark:hover:text-[#86B6F6] border border-transparent font-semibold'; ?>">
        <div class="flex items-center space-x-3">
            <i class="fa-solid fa-boxes text-sm <?php echo in_array($currentPage, ['adb.php', 'view.php', 'add.php', 'edit.php', 'category.php']) ? 'text-brand-medium' : 'text-slate-400'; ?> group-hover:text-brand-medium transition"></i>
            <span class="sidebar-text truncate">Asset Inventory Tracker</span>
        </div>
        <div class="dropdown-right">
            <i id="assetsChevron"
              class="fa-solid fa-chevron-down text-[10px] opacity-60 dropdown-chevron transition-transform duration-200 <?php echo in_array($currentPage, ['adb.php', 'view.php', 'add.php', 'edit.php', 'category.php']) ? 'rotate-180' : ''; ?>"></i>
        </div>
    </button>

    <div id="assetsDropdown" class="<?php echo in_array($currentPage, ['adb.php', 'view.php', 'add.php', 'edit.php', 'category.php']) ? '' : 'hidden'; ?> pl-8 pr-2 space-y-0.5 font-medium sidebar-text">
        <?php if ($isSuperAdmin || $hasResourceAccess($assetAccessKeywords)): ?>
            <a href="<?php echo $basePath ?? '../'; ?>pages/assets/adb.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'adb.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>">
                <i class="fa-solid fa-list text-[10px] <?php echo $currentPage == 'adb.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Dashboard</span>
            </a>
        <?php endif; ?>
        <?php if ($isSuperAdmin || $hasResourceAccess($assetManageKeywords)): ?>
            <a href="<?php echo $basePath ?? '../'; ?>pages/assets/adb.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'adb.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>">
              <i class="fa-solid fa-plus text-[10px] <?php echo $currentPage == 'adb.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Add Asset</span>
            </a>
            <a href="<?php echo $basePath ?? '../'; ?>pages/assets/category.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'category.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>">
                <i class="fa-solid fa-tags text-[10px] <?php echo $currentPage == 'category.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Categories</span>
            </a>
          <?php endif; ?>
        </div>
      </div>
  <?php endif; ?>

          <?php 
          $canAccessAuditLogs = $hasResourceAccess(['audit', 'activity', 'log', 'change', 'history']);
          if ($canAccessAuditLogs): 
          ?>
          <div class="space-y-1">
          <button
                  onclick="toggleDropdown('auditDropdown', 'auditChevron')"
                  class="dropdown-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs tracking-wide transition group cursor-pointer <?php echo in_array($currentPage, $auditPages) ? 'bg-white text-brand-dark border border-brand-border font-bold shadow-xs' : 'hover:bg-white dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-brand-dark dark:hover:text-[#86B6F6] border border-transparent font-semibold'; ?>">

                  <div class="flex items-center space-x-3">
                      <i class="fa-solid fa-clock-rotate-left text-sm <?php echo in_array($currentPage, $auditPages) ? 'text-brand-medium' : 'text-slate-400'; ?> group-hover:text-brand-medium transition"></i>
                      <span class="sidebar-text truncate">Audit Logs System</span>
              </div>

              <div class="dropdown-right">
                  <i id="auditChevron"
                    class="fa-solid fa-chevron-down text-[10px] opacity-60 dropdown-chevron transition-transform duration-200 <?php echo in_array($currentPage, $auditPages) ? 'rotate-180' : ''; ?>"></i>
              </div>
            </button>

            <div id="auditDropdown" class="<?php echo in_array($currentPage, $auditPages) ? '' : 'hidden'; ?> pl-8 pr-2 space-y-0.5 font-medium sidebar-text">
              <a href="<?php echo $basePath ?? '../'; ?>pages/audit/user-activities.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'user-activities.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>"><i class="fa-solid fa-chart-line text-[10px] <?php echo $currentPage == 'user-activities.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>User Activities</span></a>
              <a href="<?php echo $basePath ?? '../'; ?>pages/audit/login-history.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'login-history.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>"><i class="fa-solid fa-history text-[10px] <?php echo $currentPage == 'login-history.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Login History</span></a>
              <a href="<?php echo $basePath ?? '../'; ?>pages/audit/data-changes.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] rounded-md transition <?php echo $currentPage == 'data-changes.php' ? 'text-brand-medium font-black bg-white border border-brand-border/40 shadow-xs' : 'text-slate-500 hover:text-brand-dark'; ?>"><i class="fa-solid fa-pen-to-square text-[10px] <?php echo $currentPage == 'data-changes.php' ? 'text-brand-medium' : 'opacity-50'; ?>"></i> <span>Data Changes</span></a>
            </div>
          </div>
          <?php endif; ?>

        </nav>
        
        <div class="p-4 border-t shrink-0 sidebar-footer">
          <a href="#" onclick="openLogoutModal(event)" class="sidebar-logout-btn flex items-center space-x-3 px-3 py-2.5 rounded-xl text-xs font-bold tracking-wide transition group cursor-pointer">
            <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i>
            <span class="sidebar-text truncate">Logout</span>
          </a>
        </div>
      </div>
    </aside>

    <script>
    (function () {
        // Dropdowns that belong to the page we are currently on.
        // Injected from PHP above.
        var ACTIVE = <?php echo json_encode(array_values($activeSidebarDropdowns)); ?>;

        function panels() {
            return document.querySelectorAll('#sidebar div[id$="Dropdown"]');
        }

        function chevronFor(id) {
            return document.getElementById(id.replace(/Dropdown$/, 'Chevron'));
        }

        function closePanel(panel) {
            panel.classList.add('hidden');
            var c = chevronFor(panel.id);
            if (c) c.classList.remove('rotate-180');
        }

        function openPanel(panel) {
            panel.classList.remove('hidden');
            var c = chevronFor(panel.id);
            if (c) c.classList.add('rotate-180');
        }

        function closeAll(exceptId) {
            var list = panels();
            for (var i = 0; i < list.length; i++) {
                if (list[i].id === exceptId) continue;
                closePanel(list[i]);
            }
        }

        // Only the dropdown for the current page may stay open.
        // Everything else is forced shut, no matter what another
        // script or stored state did to it.
        function normalize() {
            var list = panels();
            for (var i = 0; i < list.length; i++) {
                var p = list[i];
                if (ACTIVE.indexOf(p.id) !== -1) openPanel(p);
                else closePanel(p);
            }
        }

        // Capture phase -> runs BEFORE the inline onclick toggle,
        // guaranteeing only one dropdown can ever be expanded.
        document.addEventListener('click', function (e) {
            var t = e.target;
            if (!t || !t.closest) return;

            var btn = t.closest('#sidebar .dropdown-btn');
            if (btn) {
                var panel = btn.nextElementSibling;
                closeAll(panel && panel.id ? panel.id : null);
                return;
            }

            // Any navigation click inside the sidebar collapses
            // everything so the next page never inherits an open panel.
            if (t.closest('#sidebar nav a')) closeAll(null);
        }, true);

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', normalize);
        } else {
            normalize();
        }

        // Run once more after everything has loaded, in case another
        // script re-opened a panel while restoring its own state.
        window.addEventListener('load', normalize);
    })();
    </script>