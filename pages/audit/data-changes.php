<?php
$basePath = '../../';
require_once __DIR__ . '/../../src/bootstrap.php';

\App\Middleware\AuthMiddleware::handle($basePath);
\App\Middleware\PermissionMiddleware::require('audit.data_changes', $basePath);

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<main class="flex-1 p-6 md:p-8 w-full space-y-6 overflow-y-auto bg-slate-50">

  <!-- Breadcrumb & Page Header -->
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-200/80 dark:border-slate-700/80 pb-5">
    <div class="space-y-1.5">
      <div class="flex items-center space-x-2 text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-400">
        <span>Audit Logs</span>
        <i class="fa-solid fa-chevron-right text-[8px] opacity-60"></i>
        <span class="text-slate-900 dark:text-slate-200">Data Mutation Logs</span>
      </div>
      <h1 class="text-2xl font-black text-slate-950 dark:text-white tracking-tight flex items-center gap-2.5 mt-4">
        <i class="fa-solid fa-database text-[#176B87] dark:text-[#86B6F6] shrink-0"></i>
        <span>Data Mutation & Records Audit</span>
      </h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 max-w-3xl leading-relaxed font-medium">
        Track structural row edits, history changes, and delta record mutations to maximize data accountability.
      </p>
    </div>

    <!-- Non-Destructive Action Controls -->
    <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
      <button onclick="refreshLogs()" class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 border border-[#B4D4FF] text-[#176B87] bg-[#EEF5FF] hover:bg-[#86B6F6]/20 font-bold rounded-xl text-xs tracking-wide transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#86B6F6]/40 shadow-sm dark:bg-slate-800 dark:border-slate-600 dark:text-[#86B6F6] dark:hover:bg-slate-700">
        <i class="fa-solid fa-rotate text-[#86B6F6] dark:text-[#86B6F6]"></i>
        <span>Refresh Log</span>
      </button>

      <!-- Export Dropdown -->
      <div class="relative inline-block text-left" id="exportDropdownContainer">
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
  <div id="dataChangesSkeleton" class="space-y-6 transition-all duration-500 opacity-100 pointer-events-auto">
    <!-- Policy Banner Skeleton -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 flex items-center justify-between">
      <div class="skeleton-loader h-4 w-72 rounded-md"></div>
      <div class="skeleton-loader h-4 w-36 rounded-md"></div>
    </div>
    <!-- Search Controls Skeleton -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 md:p-6 space-y-4">
      <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <div class="skeleton-loader h-4 w-48 rounded-md"></div>
        <div class="skeleton-loader h-4 w-24 rounded-md"></div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="skeleton-loader h-10 w-full rounded-xl"></div>
        <div class="skeleton-loader h-10 w-full rounded-xl"></div>
        <div class="skeleton-loader h-10 w-full rounded-xl"></div>
      </div>
    </div>
    <!-- Table Skeleton -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden flex flex-col">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[950px]">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
              <th class="py-4 px-5"><div class="skeleton-loader h-3 w-20 rounded-md"></div></th>
              <th class="py-4 px-5"><div class="skeleton-loader h-3 w-32 rounded-md"></div></th>
              <th class="py-4 px-5"><div class="skeleton-loader h-3 w-28 rounded-md"></div></th>
              <th class="py-4 px-5"><div class="skeleton-loader h-3 w-24 rounded-md"></div></th>
              <th class="py-4 px-5"><div class="skeleton-loader h-3 w-24 rounded-md"></div></th>
              <th class="py-4 px-5"><div class="skeleton-loader h-3 w-20 rounded-md"></div></th>
              <th class="py-4 px-5"><div class="skeleton-loader h-3 w-36 rounded-md"></div></th>
              <th class="py-4 px-5"><div class="skeleton-loader h-3 w-20 rounded-md"></div></th>
              <th class="py-4 px-5 text-right"><div class="skeleton-loader h-3 w-16 rounded-md ml-auto"></div></th>
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
              <td class="py-4 px-5"><div class="skeleton-loader h-3.5 w-24 rounded-md"></div></td>
              <td class="py-4 px-5"><div class="skeleton-loader h-3.5 w-24 rounded-md"></div></td>
              <td class="py-4 px-5"><div class="skeleton-loader h-5 w-16 rounded-full"></div></td>
              <td class="py-4 px-5"><div class="skeleton-loader h-3.5 w-36 rounded-md"></div></td>
              <td class="py-4 px-5"><div class="skeleton-loader h-3.5 w-20 rounded-md"></div></td>
              <td class="py-4 px-5 text-right"><div class="skeleton-loader h-7 w-16 rounded-lg ml-auto"></div></td>
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
  <div id="dataChangesRealContent" class="space-y-6 hidden opacity-0 transition-all duration-700 ease-out transform translate-y-2">

    <!-- Policy Information Banner -->
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 flex items-start gap-3.5 text-xs text-blue-900 leading-relaxed font-semibold">
      <div class="h-6 w-6 rounded-lg bg-blue-100 flex items-center justify-center shrink-0 text-blue-700">
        <i class="fa-solid fa-circle-info"></i>
      </div>
      <div>
        <h4 class="font-bold text-blue-950">Read-Only Immutable Mutation Trail</h4>
        <p class="text-[10px] text-blue-800 mt-0.5">
          To maintain absolute municipal portal compliance and trust parameters, the log record database is permanently protected against administrative deletes, structural updates, or edits.
        </p>
      </div>
    </div>

    <!-- Search & Filter Controls -->
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

      <!-- Filters Fields Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Search Query -->
        <div class="space-y-1.5">
          <label class="text-[10px] font-black uppercase tracking-wider text-slate-400 block" for="filterSearch">Search Query</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
              <i class="fa-solid fa-magnifying-glass text-xs"></i>
            </span>
            <input type="text" id="filterSearch" oninput="applyFilters()" placeholder="e.g. Record ID, User, Action, Description..."
              class="w-full bg-white border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl pl-10 pr-4 py-2.5 focus:outline-none focus:border-[#0f172a] focus:ring-2 focus:ring-[#0f172a]/10 transition placeholder-slate-400">
          </div>
        </div>

        <!-- Date Filter -->
        <div class="space-y-1.5">
          <label class="text-[10px] font-black uppercase tracking-wider text-slate-400 block" for="filterDate">Date Picker</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
              <i class="fa-solid fa-calendar text-xs"></i>
            </span>
            <input type="date" id="filterDate" onchange="applyFilters()"
              class="w-full bg-white border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl pl-10 pr-4 py-2.5 focus:outline-none focus:border-[#0f172a] focus:ring-2 focus:ring-[#0f172a]/10 transition">
          </div>
        </div>

        <!-- Module / Target Table Filter -->
        <div class="space-y-1.5">
          <label class="text-[10px] font-black uppercase tracking-wider text-slate-400 block" for="filterModule">Module / Target Area</label>
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
      </div>
    </div>

    <!-- Data Mutation Audit Table Container -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden flex flex-col">
      <div class="overflow-x-auto overflow-y-auto max-h-[600px] w-full custom-scrollbar">
        <table class="w-full text-left border-collapse min-w-[950px]">
          <thead class="sticky top-0 bg-slate-50 z-10 border-b border-slate-200 shadow-xs">
            <tr class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-400">
              <th class="py-4 px-5">Change Ref</th>
              <th class="py-4 px-5">Timestamp (Asia/Manila)</th>
              <th class="py-4 px-5">Actor Name</th>
              <th class="py-4 px-5">Module / Target</th>
              <th class="py-4 px-5">Record Ref</th>
              <th class="py-4 px-5">Action Type</th>
              <th class="py-4 px-5">Pre-State (Old)</th>
              <th class="py-4 px-5">Post-State (New)</th>
              <th class="py-4 px-5 text-right">Details</th>
            </tr>
          </thead>
          <tbody id="mutationTableBody" class="divide-y divide-slate-100 text-xs text-slate-700">
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

  <!-- Data Mutation Detail Inspector Modal -->
  <div id="mutationDetailsModal" class="hidden fixed inset-0 z-[99999] p-4 sm:p-6 pt-20 sm:pt-16 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm transition-all duration-300 overflow-y-auto">
    <div id="modalCard" class="bg-white border border-slate-200 rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden transform scale-95 opacity-0 transition-all duration-200 flex flex-col max-h-[82vh] my-auto dark:bg-slate-900 dark:border-slate-800">
      
      <!-- Modal Header -->
      <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between bg-slate-50/50 shrink-0">
        <div class="flex items-center gap-2.5">
          <div class="h-9 w-9 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-[#176B87]">
            <i class="fa-solid fa-database text-base"></i>
          </div>
          <div>
            <h3 class="text-sm font-black text-slate-900">Data Mutation Delta Inspector</h3>
            <p class="text-[10px] text-slate-400 font-semibold" id="modalMutId">#MUT-0000</p>
          </div>
        </div>
        <button onclick="closeMutationModal()" class="h-8 w-8 rounded-lg hover:bg-slate-200/70 text-slate-400 hover:text-slate-600 transition flex items-center justify-center cursor-pointer">
          <i class="fa-solid fa-xmark text-sm"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-6 space-y-6 overflow-y-auto custom-scrollbar flex-1">
        
        <!-- Summary Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs bg-slate-50 border border-slate-200/60 p-4 rounded-xl">
          <div>
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-0.5">Actor Identity</span>
            <div id="modalActor" class="font-bold text-slate-900">System</div>
          </div>
          <div>
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-0.5">Timestamp (Asia/Manila)</span>
            <div id="modalTime" class="font-bold text-slate-900">—</div>
          </div>
          <div>
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-0.5">Module / Target</span>
            <div id="modalModule" class="font-bold text-slate-900">System</div>
          </div>
          <div>
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-0.5">Record Ref</span>
            <div id="modalRecord" class="font-mono font-bold text-slate-900">—</div>
          </div>
        </div>

        <!-- Delta State Comparison Panel -->
        <div class="space-y-3">
          <h4 class="text-xs font-black uppercase tracking-wider text-slate-800 flex items-center gap-2">
            <i class="fa-solid fa-code-compare text-slate-400"></i>
            <span>Delta State Comparison</span>
          </h4>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Old Value Box -->
            <div class="space-y-1.5">
              <span id="modalFieldOldLabel" class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Pre-Mutation State (Old):</span>
              <div id="modalOldValue" class="p-3 bg-slate-100 border border-slate-200 rounded-xl text-xs font-mono font-semibold text-slate-700 break-words leading-relaxed min-h-[48px]">
                None (New Record)
              </div>
            </div>
            
            <!-- New Value Box -->
            <div class="space-y-1.5">
              <span id="modalFieldNewLabel" class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Post-Mutation State (New):</span>
              <div id="modalNewValue" class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-mono font-bold text-emerald-800 break-words leading-relaxed min-h-[48px]">
                Success
              </div>
            </div>
          </div>
        </div>

        <!-- Context Description -->
        <div class="space-y-1.5">
          <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block">System Description & Reason</span>
          <p id="modalReason" class="p-3 bg-slate-50 border border-slate-200/80 rounded-xl text-xs font-semibold text-slate-700 leading-relaxed">
            Record mutation committed to audit log database.
          </p>
        </div>

        <!-- Request Network Context -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 border border-slate-200/60 p-3.5 rounded-xl text-xs">
          <div>
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-0.5">IP Address</span>
            <span id="modalIp" class="font-mono font-bold text-slate-700">127.0.0.1</span>
          </div>
          <div>
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-0.5">HTTP Method</span>
            <span id="modalMethod" class="font-mono font-bold text-slate-700">POST</span>
          </div>
          <div>
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-0.5">Target Request URI</span>
            <span id="modalUri" class="font-mono font-bold text-slate-700 truncate block">/api</span>
          </div>
          <div>
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-0.5">Client User Agent</span>
            <span id="modalBrowser" class="font-bold text-slate-700 truncate block">Browser</span>
          </div>
        </div>

        <!-- Full Pre / Post JSON Payload comparison -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block">Pre-Mutation JSON Payload</span>
            <pre class="bg-slate-900 text-slate-200 p-3.5 rounded-xl text-[10px] font-mono overflow-x-auto leading-relaxed border border-slate-800 shadow-inner max-h-40 custom-scrollbar"><code id="modalOldJson">null</code></pre>
          </div>
          <div class="space-y-1.5">
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block">Post-Mutation JSON Payload</span>
            <pre class="bg-slate-900 text-slate-200 p-3.5 rounded-xl text-[10px] font-mono overflow-x-auto leading-relaxed border border-slate-800 shadow-inner max-h-40 custom-scrollbar"><code id="modalNewJson">{}</code></pre>
          </div>
        </div>

      </div>

      <!-- Modal Footer -->
      <div class="px-6 py-4 border-t border-slate-200/80 bg-slate-50 flex items-center justify-end gap-3 shrink-0">
        <button onclick="closeMutationModal()" class="px-4 py-2 border border-slate-200 text-slate-700 hover:bg-slate-100 font-bold rounded-xl text-xs tracking-wide transition cursor-pointer focus:outline-none">
          Close Inspector
        </button>
        <button onclick="printData()" class="px-4 py-2 bg-[#0f172a] hover:bg-[#1e3a8a] text-white font-bold rounded-xl text-xs tracking-wide transition flex items-center gap-1.5 cursor-pointer focus:outline-none shadow-sm">
          <i class="fa-solid fa-print"></i>
          Print Audit Trail
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
<script src="../../assets/js/audit/data-changes/api.js"></script>
<script src="../../assets/js/audit/data-changes/ui.js"></script>
<script src="../../assets/js/audit/data-changes/filters.js"></script>
<script src="../../assets/js/audit/data-changes/modal.js"></script>
<script src="../../assets/js/audit/data-changes/events.js"></script>
<script src="../../assets/js/audit/data-changes/app.js"></script>

<?php include '../../includes/footer.php'; ?>
