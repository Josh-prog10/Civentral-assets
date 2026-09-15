<?php
$basePath = '../../';
require_once __DIR__ . '/../../src/bootstrap.php';

\App\Middleware\AuthMiddleware::handle($basePath);
\App\Middleware\PermissionMiddleware::require('audit.user_activities', $basePath);

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<main class="flex-1 p-6 md:p-8 max-w-7xl mx-auto space-y-6 overflow-y-auto">

  <!-- Breadcrumb & Header Section -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200/60 pb-5">
    <div class="space-y-1">
      <div class="flex items-center space-x-2 text-xs font-bold uppercase tracking-wider text-slate-400">
        <span>Audit Trail System</span>
        <i class="fa-solid fa-chevron-right text-[8px] opacity-60"></i>
        <span class="text-[#176B87]">User Activities</span>
      </div>
      <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2 mt-4">
        <i class="fa-solid fa-list-check text-[#176B87]"></i>
        User Activity Audit Log
      </h1>
      <p class="text-xs text-slate-500 max-w-2xl leading-relaxed">
        Comprehensive administrative gateway ledger recording system interaction logs, operational user events, and real-time security events across all LGU modules.
      </p>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center gap-3 shrink-0">
      <button onclick="refreshData()" class="px-3.5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 font-bold border border-slate-200 rounded-xl text-xs flex items-center gap-2 transition cursor-pointer shadow-xs">
        <i class="fa-solid fa-rotate-right text-slate-400"></i>
        <span>Refresh Logs</span>
      </button>
      
      <!-- Export Dropdown -->
      <div class="relative">
        <button id="exportDropdownBtn" onclick="toggleExportDropdown(event)"
          class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl text-xs tracking-wide transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-400/50 shadow-sm">
          <i class="fa-solid fa-file-export"></i>
          <span>Export Logs</span>
          <i class="fa-solid fa-chevron-down text-[9px] opacity-75"></i>
        </button>
        <!-- Dropdown Card -->
        <div id="exportDropdownMenu" class="hidden absolute right-0 mt-2 w-52 bg-white border border-[#B4D4FF] rounded-xl shadow-xl py-1.5 z-50 text-xs text-slate-600 transition-all transform scale-95 origin-top-right dark:bg-slate-800 dark:border-slate-600">
          <a href="#" onclick="exportLogs('PDF', event)" class="flex items-center gap-2.5 px-4 py-2.5 hover:bg-[#EEF5FF] text-slate-700 transition font-bold rounded-lg mx-1 dark:text-slate-200 dark:hover:bg-slate-700">
            <i class="fa-solid fa-file-pdf text-red-500 text-sm"></i>
            <span>Export to PDF</span>
          </a>
          <a href="#" onclick="exportLogs('Excel', event)" class="flex items-center gap-2.5 px-4 py-2.5 hover:bg-[#EEF5FF] text-slate-700 transition font-bold rounded-lg mx-1 dark:text-slate-200 dark:hover:bg-slate-700">
            <i class="fa-solid fa-file-excel text-emerald-600 text-sm"></i>
            <span>Export to Excel</span>
          </a>
          <a href="#" onclick="exportLogs('CSV', event)" class="flex items-center gap-2.5 px-4 py-2.5 hover:bg-[#EEF5FF] text-slate-700 transition font-bold rounded-lg mx-1 dark:text-slate-200 dark:hover:bg-slate-700">
            <i class="fa-solid fa-file-csv text-emerald-500 text-sm"></i>
            <span>Download CSV</span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- FULL CONTENT SKELETON LOADER -->
  <div id="userActivitiesSkeleton" class="space-y-6 transition-all duration-500 opacity-100 pointer-events-auto">
    <!-- Filter Controls Skeleton -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 md:p-6 space-y-4">
      <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <div class="skeleton-loader h-4 w-48 rounded-md"></div>
        <div class="skeleton-loader h-4 w-24 rounded-md"></div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <?php for($k=0; $k<6; $k++): ?>
        <div class="space-y-1.5">
          <div class="skeleton-loader h-3 w-28 rounded-md"></div>
          <div class="skeleton-loader h-10 w-full rounded-xl"></div>
        </div>
        <?php endfor; ?>
      </div>
    </div>

    <!-- Table Container Skeleton -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden flex flex-col">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[950px]">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
              <th class="py-4 px-5"><div class="skeleton-loader h-3 w-20 rounded-md"></div></th>
              <th class="py-4 px-5"><div class="skeleton-loader h-3 w-32 rounded-md"></div></th>
              <th class="py-4 px-5"><div class="skeleton-loader h-3 w-28 rounded-md"></div></th>
              <th class="py-4 px-5"><div class="skeleton-loader h-3 w-32 rounded-md"></div></th>
              <th class="py-4 px-5"><div class="skeleton-loader h-3 w-40 rounded-md"></div></th>
              <th class="py-4 px-5"><div class="skeleton-loader h-3 w-24 rounded-md"></div></th>
              <th class="py-4 px-5 text-center"><div class="skeleton-loader h-3 w-16 rounded-md mx-auto"></div></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
            <?php for($i = 0; $i < 5; $i++): ?>
            <tr class="animate-pulse">
              <td class="py-4 px-5"><div class="skeleton-loader h-3.5 w-20 rounded-md"></div></td>
              <td class="py-4 px-5"><div class="skeleton-loader h-3.5 w-32 rounded-md"></div></td>
              <td class="py-4 px-5">
                <div class="space-y-1">
                  <div class="skeleton-loader h-3.5 w-28 rounded-md"></div>
                  <div class="skeleton-loader h-2.5 w-20 rounded-md"></div>
                </div>
              </td>
              <td class="py-4 px-5">
                <div class="space-y-1">
                  <div class="skeleton-loader h-3.5 w-32 rounded-md"></div>
                  <div class="skeleton-loader h-2.5 w-24 rounded-md"></div>
                </div>
              </td>
              <td class="py-4 px-5">
                <div class="space-y-1">
                  <div class="skeleton-loader h-3.5 w-40 rounded-md"></div>
                  <div class="skeleton-loader h-2.5 w-56 rounded-md"></div>
                </div>
              </td>
              <td class="py-4 px-5"><div class="skeleton-loader h-3.5 w-24 rounded-md"></div></td>
              <td class="py-4 px-5 text-center"><div class="skeleton-loader h-5 w-16 rounded-full mx-auto"></div></td>
            </tr>
            <?php endfor; ?>
          </tbody>
        </table>
      </div>
      <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
        <div class="skeleton-loader h-4 w-48 rounded-md"></div>
        <div class="skeleton-loader h-8 w-40 rounded-xl"></div>
      </div>
    </div>
  </div>

  <!-- REAL PAGE CONTENT -->
  <div id="userActivitiesRealContent" class="space-y-6 hidden opacity-0 transition-all duration-700 ease-out transform translate-y-2">
    
    <!-- Comprehensive Activity Filter Panel -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs p-5 md:p-6 space-y-4">
      <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <div class="flex items-center gap-2 text-slate-900">
          <i class="fa-solid fa-sliders text-xs text-slate-400"></i>
          <h3 class="text-xs font-bold uppercase tracking-wider">Search & Filter Controls</h3>
        </div>
        <button onclick="resetFilters()" class="text-xs font-bold text-[#0f172a] hover:text-[#1e3a8a] transition flex items-center gap-1.5 focus:outline-none cursor-pointer">
          <i class="fa-solid fa-rotate-right text-[10px]"></i>
          Reset Filters
        </button>
      </div>

      <!-- Filter Fields Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Date Picker -->
        <div class="space-y-1.5">
          <label class="text-[10px] font-black uppercase tracking-wider text-slate-400 block" for="filterDate">Date Range Picker</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
              <i class="fa-solid fa-calendar text-xs"></i>
            </span>
            <input type="date" id="filterDate" onchange="applyFilters()"
              class="w-full bg-white border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl pl-10 pr-4 py-2.5 focus:outline-none focus:border-[#0f172a] focus:ring-2 focus:ring-[#0f172a]/10 transition">
          </div>
        </div>

        <!-- Search User -->
        <div class="space-y-1.5">
          <label class="text-[10px] font-black uppercase tracking-wider text-slate-400 block" for="filterSearch">Search User / Description</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
              <i class="fa-solid fa-magnifying-glass text-xs"></i>
            </span>
            <input type="text" id="filterSearch" oninput="applyFilters()" placeholder="e.g. Joshua Suruiz, #ACT-90812..."
              class="w-full bg-white border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl pl-10 pr-4 py-2.5 focus:outline-none focus:border-[#0f172a] focus:ring-2 focus:ring-[#0f172a]/10 transition placeholder-slate-400">
          </div>
        </div>

        <!-- Department Selector -->
        <div class="space-y-1.5">
          <label class="text-[10px] font-black uppercase tracking-wider text-slate-400 block" for="filterDepartment">Department Selector</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
              <i class="fa-solid fa-building text-xs"></i>
            </span>
            <select id="filterDepartment" onchange="applyFilters()"
              class="w-full bg-white border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl pl-10 pr-4 py-2.5 appearance-none focus:outline-none focus:border-[#0f172a] focus:ring-2 focus:ring-[#0f172a]/10 transition cursor-pointer">
              <option value="All">All Departments</option>
            </select>
            <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
              <i class="fa-solid fa-chevron-down text-[9px]"></i>
            </span>
          </div>
        </div>

        <!-- Module Filter -->
        <div class="space-y-1.5">
          <label class="text-[10px] font-black uppercase tracking-wider text-slate-400 block" for="filterModule">Module Filter</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
              <i class="fa-solid fa-cubes text-xs"></i>
            </span>
            <select id="filterModule" onchange="applyFilters()"
              class="w-full bg-white border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl pl-10 pr-4 py-2.5 appearance-none focus:outline-none focus:border-[#0f172a] focus:ring-2 focus:ring-[#0f172a]/10 transition cursor-pointer">
              <option value="All">All Modules</option>
            </select>
            <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
              <i class="fa-solid fa-chevron-down text-[9px]"></i>
            </span>
          </div>
        </div>

        <!-- Action Type Selector -->
        <div class="space-y-1.5">
          <label class="text-[10px] font-black uppercase tracking-wider text-slate-400 block" for="filterAction">Action Type Selector</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
              <i class="fa-solid fa-bolt text-xs"></i>
            </span>
            <select id="filterAction" onchange="applyFilters()"
              class="w-full bg-white border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl pl-10 pr-4 py-2.5 appearance-none focus:outline-none focus:border-[#0f172a] focus:ring-2 focus:ring-[#0f172a]/10 transition cursor-pointer">
              <option value="All">All Actions</option>
              <option value="Create">Create</option>
              <option value="Update">Update</option>
              <option value="Delete">Delete</option>
              <option value="Approve">Approve</option>
              <option value="Reject">Reject</option>
            </select>
            <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
              <i class="fa-solid fa-chevron-down text-[9px]"></i>
            </span>
          </div>
        </div>

        <!-- Status Selector -->
        <div class="space-y-1.5">
          <label class="text-[10px] font-black uppercase tracking-wider text-slate-400 block" for="filterStatus">Status Selector</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
              <i class="fa-solid fa-circle-check text-xs"></i>
            </span>
            <select id="filterStatus" onchange="applyFilters()"
              class="w-full bg-white border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl pl-10 pr-4 py-2.5 appearance-none focus:outline-none focus:border-[#0f172a] focus:ring-2 focus:ring-[#0f172a]/10 transition cursor-pointer">
              <option value="All">All Statuses</option>
              <option value="Success">Success</option>
              <option value="Failed">Failed / Blocked</option>
            </select>
            <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
              <i class="fa-solid fa-chevron-down text-[9px]"></i>
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Audit Table Container -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden flex flex-col">
      <div class="overflow-x-auto overflow-y-auto max-h-[600px] w-full custom-scrollbar">
        <table class="w-full text-left border-collapse min-w-[950px]">
          <thead class="sticky top-0 bg-slate-50 z-10 border-b border-slate-200 shadow-xs">
            <tr class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-400">
              <th class="py-4 px-5">Activity ID</th>
              <th class="py-4 px-5">Date & Time (Asia/Manila)</th>
              <th class="py-4 px-5">Actor & Role</th>
              <th class="py-4 px-5">Department / Module</th>
              <th class="py-4 px-5">Action & Description</th>
              <th class="py-4 px-5">IP & Device Trace</th>
              <th class="py-4 px-5 text-center">Status</th>
            </tr>
          </thead>
          <tbody id="auditTableBody" class="divide-y divide-slate-100 text-xs text-slate-700">
            <!-- Populated dynamically by JS -->
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer Container -->
      <div id="paginationFooter" class="px-5 py-3.5 bg-[#EEF5FF]/60 border-t border-[#B4D4FF]/60 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs font-semibold select-none dark:bg-slate-800/60 dark:border-slate-700">
        <div class="flex items-center gap-4 flex-wrap">
          <div id="paginationInfo" class="text-xs text-slate-500 font-medium dark:text-slate-400">
            Showing <span id="paginationStart" class="font-bold text-[#176B87] dark:text-[#86B6F6]">0</span> to <span id="paginationEnd" class="font-bold text-[#176B87] dark:text-[#86B6F6]">0</span> of <span id="paginationTotal" class="font-bold text-[#176B87] dark:text-[#86B6F6]">0</span> entries
          </div>
          <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400">
            <label for="pageSizeSelect" class="text-[11px] font-semibold">Rows per page:</label>
            <select id="pageSizeSelect" onchange="changePageSize(this.value)" class="bg-white dark:bg-slate-800 border border-[#B4D4FF] dark:border-slate-600 rounded-lg px-2 py-1 text-xs font-bold text-[#176B87] dark:text-[#86B6F6] focus:outline-none cursor-pointer">
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50" selected>50</option>
              <option value="100">100</option>
            </select>
          </div>
        </div>
        <div class="flex items-center gap-1.5" id="paginationControls">
          <button id="prevPageBtn" onclick="changePage(-1)" class="px-3 py-1.5 border border-[#B4D4FF] rounded-xl bg-white hover:bg-[#EEF5FF] disabled:opacity-40 disabled:cursor-not-allowed text-[#176B87] font-bold transition flex items-center gap-1 text-xs cursor-pointer shadow-sm dark:bg-slate-800 dark:border-slate-600 dark:text-[#86B6F6] dark:hover:bg-slate-700">
            <i class="fa-solid fa-chevron-left text-[10px]"></i> Previous
          </button>
          <div id="pageNumbers" class="flex items-center gap-1 font-bold text-xs">
            <!-- Dynamic Page Numbers -->
          </div>
          <button id="nextPageBtn" onclick="changePage(1)" class="px-3 py-1.5 border border-[#B4D4FF] rounded-xl bg-white hover:bg-[#EEF5FF] disabled:opacity-40 disabled:cursor-not-allowed text-[#176B87] font-bold transition flex items-center gap-1 text-xs cursor-pointer shadow-sm dark:bg-slate-800 dark:border-slate-600 dark:text-[#86B6F6] dark:hover:bg-slate-700">
            Next <i class="fa-solid fa-chevron-right text-[10px]"></i>
          </button>
        </div>
      </div>
    </div>

  </div>

  <!-- Audit Entry Detailed Inspector Modal -->
  <div id="logDetailsModal" class="hidden fixed inset-0 z-[99999] p-4 sm:p-6 pt-20 sm:pt-16 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm transition-all duration-300 overflow-y-auto">
    <div id="modalCard" class="bg-white border border-slate-200 rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden transform scale-95 opacity-0 transition-all duration-200 flex flex-col max-h-[82vh] my-auto dark:bg-slate-900 dark:border-slate-800">
      
      <!-- Modal Header -->
      <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between bg-slate-50/50 shrink-0">
        <div class="flex items-center gap-2.5">
          <div class="h-9 w-9 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-[#0f172a]">
            <i class="fa-solid fa-clock-rotate-left text-base"></i>
          </div>
          <div>
            <h3 class="text-sm font-black text-slate-900">Activity Log Inspector</h3>
            <p class="text-[10px] text-slate-400 font-semibold" id="modalActId">#ACT-0000</p>
          </div>
        </div>
        <button onclick="closeLogDetailsModal()" class="h-8 w-8 rounded-lg hover:bg-slate-200/70 text-slate-400 hover:text-slate-600 transition flex items-center justify-center cursor-pointer">
          <i class="fa-solid fa-xmark text-sm"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-6 space-y-6 overflow-y-auto custom-scrollbar flex-1">
        
        <!-- Status Indicator Banner -->
        <div id="modalStatusBanner" class="p-4 rounded-xl flex items-center gap-3 bg-emerald-50 border border-emerald-100 text-emerald-800">
          <div id="modalStatusIconContainer" class="h-8 w-8 rounded-lg flex items-center justify-center shrink-0 bg-emerald-100 text-emerald-700">
            <i class="fa-solid fa-circle-check text-base"></i>
          </div>
          <div class="space-y-0.5">
            <h4 id="modalStatusTitle" class="text-xs font-bold">Transaction Success</h4>
            <p id="modalStatusMsg" class="text-[10px] leading-relaxed font-semibold text-emerald-600">The operations were processed, audited, and committed successfully.</p>
          </div>
        </div>

        <!-- Detail Breakdown Grid -->
        <div class="grid grid-cols-2 gap-4 text-xs">
          <div>
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-1">Actor Identity</span>
            <div id="modalActorName" class="font-bold text-slate-900">Joshua Suruiz</div>
            <div id="modalActorRole" class="mt-1"></div>
          </div>
          
          <div>
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-1">Date & Time</span>
            <div id="modalDateTime" class="font-bold text-slate-900">Jul 18, 2026 at 09:20 AM</div>
            <div class="text-[10px] text-slate-400 font-semibold mt-1">LGU Caloocan Local Time</div>
          </div>

          <div>
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-1">Department Branch</span>
            <div id="modalDepartment" class="font-bold text-slate-900">Education & Scholarship</div>
          </div>

          <div>
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-1">Module Target</span>
            <div id="modalModule" class="font-bold text-slate-900">User Management</div>
          </div>

          <div class="col-span-2">
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-1">Action Performed</span>
            <div class="flex items-center gap-2 mt-1">
              <span id="modalActionBadge" class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase">Create</span>
              <span id="modalActionDesc" class="font-extrabold text-slate-900 leading-snug">Created Department Admin Account</span>
            </div>
          </div>

          <div class="col-span-2 grid grid-cols-2 gap-4 bg-slate-50 border border-slate-200/60 p-3.5 rounded-xl">
            <div>
              <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-0.5">IP Address</span>
              <span id="modalIp" class="font-mono font-bold text-slate-700 text-xs">192.168.1.45</span>
            </div>
            <div>
              <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-0.5">Device & Browser</span>
              <span id="modalDevice" class="font-bold text-slate-700 text-xs">Chrome - Windows</span>
            </div>
          </div>
        </div>

        <!-- Technical Metadata Payload (JSON) -->
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400">Technical Context Payload</span>
            <button onclick="copyModalPayload()" class="text-[10px] font-bold text-[#0f172a] hover:text-[#1e3a8a] transition flex items-center gap-1 focus:outline-none cursor-pointer">
              <i class="fa-solid fa-copy"></i>
              Copy JSON
            </button>
          </div>
          <pre class="bg-slate-900 text-slate-200 p-4 rounded-xl text-[10px] font-mono overflow-x-auto leading-relaxed border border-slate-800 shadow-inner max-h-48 custom-scrollbar"><code id="modalPayloadText">{}</code></pre>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="px-6 py-4 border-t border-slate-200/80 bg-slate-50 flex items-center justify-end gap-3 shrink-0">
        <button onclick="closeLogDetailsModal()" class="px-4 py-2 border border-slate-200 text-slate-700 hover:bg-slate-100 font-bold rounded-xl text-xs tracking-wide transition cursor-pointer focus:outline-none">
          Close Details
        </button>
        <button onclick="printData()" class="px-4 py-2 bg-[#0f172a] hover:bg-[#1e3a8a] text-white font-bold rounded-xl text-xs tracking-wide transition flex items-center gap-1.5 cursor-pointer focus:outline-none shadow-sm">
          <i class="fa-solid fa-print"></i>
          Print Log Entry
        </button>
      </div>

    </div>
  </div>

  <!-- Toast Notification Container -->
  <div id="toastContainer" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>

</main>

<script src="../../assets/js/audit/shared/utils.js"></script>
<script src="../../assets/js/audit/shared/toast.js"></script>
<script src="../../assets/js/audit/audit-export.js"></script>
<script src="../../assets/js/audit/user-activities/api.js"></script>
<script src="../../assets/js/audit/user-activities/ui.js"></script>
<script src="../../assets/js/audit/user-activities/filters.js"></script>
<script src="../../assets/js/audit/user-activities/modal.js"></script>
<script src="../../assets/js/audit/user-activities/events.js"></script>
<script src="../../assets/js/audit/user-activities/app.js"></script>

<?php include '../../includes/footer.php'; ?>
