<?php 
include '../includes/header.php';
include '../includes/sidebar.php';

$isSuperAdmin = !empty($headerUser['is_superadmin']) || !empty($headerUser['is_global_access']);
$basePathResolver = $basePath ?? '../';
?>

<!-- Load Custom Dashboard Stylesheet -->
<link rel="stylesheet" href="<?php echo $basePathResolver; ?>assets/css/dashboard-analytics.css">

    <main class="flex-1 p-6 md:p-8 max-w-7xl mx-auto space-y-6 overflow-y-auto">
      
      <!-- DASHBOARD LOADING SKELETON -->
      <div id="dashboardSkeleton" class="space-y-6 transition-all duration-500 opacity-100 pointer-events-auto">
        
        <!-- 4 Metric Cards Skeleton -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
          <?php for($i = 0; $i < 4; $i++): ?>
          <div class="glass-panel rounded-2xl p-5 flex items-center justify-between dark:bg-slate-900/85 dark:border-slate-800/80">
            <div class="space-y-2.5 w-full">
              <div class="skeleton-loader h-3 w-32 rounded-md"></div>
              <div class="flex items-center gap-2">
                <div class="skeleton-loader h-7 w-24 rounded-lg"></div>
                <div class="skeleton-loader h-4 w-12 rounded-full"></div>
              </div>
              <div class="skeleton-loader h-2.5 w-40 rounded-md"></div>
            </div>
            <div class="skeleton-loader h-11 w-11 rounded-xl shrink-0 ml-3"></div>
          </div>
          <?php endfor; ?>
        </div>

        <!-- Charts & Activity Grid Skeleton -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          
          <!-- Left Column Skeleton (2/3) -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Area Chart Skeleton -->
            <div class="glass-panel rounded-2xl p-6 space-y-4 dark:bg-slate-900/85 dark:border-slate-800/80">
              <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="space-y-2">
                  <div class="skeleton-loader h-4 w-56 rounded-md"></div>
                  <div class="skeleton-loader h-3 w-72 rounded-md"></div>
                </div>
                <div class="skeleton-loader h-7 w-32 rounded-lg"></div>
              </div>
              <div class="skeleton-loader h-72 w-full rounded-xl"></div>
            </div>

            <!-- Activity Feed Skeleton -->
            <div class="glass-panel rounded-2xl p-6 space-y-4 dark:bg-slate-900/85 dark:border-slate-800/80">
              <div class="flex items-center justify-between">
                <div class="space-y-2">
                  <div class="skeleton-loader h-4 w-40 rounded-md"></div>
                  <div class="skeleton-loader h-3 w-60 rounded-md"></div>
                </div>
                <div class="skeleton-loader h-5 w-20 rounded-md"></div>
              </div>
              <div class="space-y-3 pt-2">
                <?php for($k = 0; $k < 3; $k++): ?>
                <div class="flex items-center justify-between gap-4 py-2 border-b border-slate-100/60 dark:border-slate-800/60 last:border-0">
                  <div class="flex items-center gap-3 w-full">
                    <div class="skeleton-loader h-8.5 w-8.5 rounded-lg shrink-0"></div>
                    <div class="space-y-1.5 w-full">
                      <div class="skeleton-loader h-3.5 w-48 rounded-md"></div>
                      <div class="skeleton-loader h-2.5 w-64 rounded-md"></div>
                    </div>
                  </div>
                  <div class="skeleton-loader h-3 w-16 rounded-md shrink-0"></div>
                </div>
                <?php endfor; ?>
              </div>
            </div>
          </div>

          <!-- Right Column Skeleton (1/3) -->
          <div class="space-y-6">
            <!-- Doughnut Chart Skeleton -->
            <div class="glass-panel rounded-2xl p-6 space-y-4 dark:bg-slate-900/85 dark:border-slate-800/80">
              <div class="space-y-2">
                <div class="skeleton-loader h-4 w-40 rounded-md"></div>
                <div class="skeleton-loader h-3 w-52 rounded-md"></div>
              </div>
              <div class="skeleton-loader h-48 w-full rounded-xl"></div>
            </div>

            <!-- Radar Chart Skeleton -->
            <div class="glass-panel rounded-2xl p-6 space-y-4 dark:bg-slate-900/85 dark:border-slate-800/80">
              <div class="space-y-2">
                <div class="skeleton-loader h-4 w-40 rounded-md"></div>
                <div class="skeleton-loader h-3 w-52 rounded-md"></div>
              </div>
              <div class="skeleton-loader h-56 w-full rounded-xl"></div>
            </div>

            <!-- Progress KPIs Skeleton -->
            <div class="glass-panel rounded-2xl p-6 space-y-4 dark:bg-slate-900/85 dark:border-slate-800/80">
              <div class="space-y-2">
                <div class="skeleton-loader h-4 w-44 rounded-md"></div>
                <div class="skeleton-loader h-3 w-56 rounded-md"></div>
              </div>
              <div class="space-y-4 pt-2">
                <?php for($j = 0; $j < 3; $j++): ?>
                <div class="space-y-1.5">
                  <div class="flex justify-between">
                    <div class="skeleton-loader h-3 w-32 rounded-md"></div>
                    <div class="skeleton-loader h-3 w-8 rounded-md"></div>
                  </div>
                  <div class="skeleton-loader h-2 w-full rounded-full"></div>
                </div>
                <?php endfor; ?>
              </div>
            </div>

          </div>
        </div>

      </div>

      <!-- DASHBOARD REAL CONTENT (Initially hidden with opacity-0) -->
      <div id="dashboardRealContent" class="space-y-6 hidden opacity-0 transition-all duration-700 ease-out transform translate-y-2">
        
        <!-- Glassmorphic Glowing Widgets Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
          
          <!-- Widget 1: Registered Citizens -->
          <div class="glass-panel glow-card-navy rounded-2xl p-5 flex items-center justify-between group cursor-pointer dark:bg-slate-900/85 dark:border-slate-800/80">
            <div class="space-y-1">
              <span class="text-[10px] font-black uppercase tracking-wider text-slate-455 dark:text-slate-400">Total Registered Citizens</span>
              <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">482,918</span>
                <span class="text-[10px] font-extrabold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 px-2 py-0.5 rounded-full flex items-center gap-0.5">
                  <i class="fa-solid fa-caret-up text-[9px]"></i> 12.4%
                </span>
              </div>
              <p class="text-[9px] text-slate-400 dark:text-slate-500 font-medium">Based on recent Caloocan census entries</p>
            </div>
            <div class="h-11 w-11 rounded-xl bg-brand-light dark:bg-slate-800 text-brand-dark dark:text-brand-medium border border-brand-border/40 dark:border-slate-700/60 flex items-center justify-center shadow-xs transition duration-300 group-hover:bg-brand-dark group-hover:text-white dark:group-hover:bg-brand-medium">
              <i class="fa-solid fa-users text-sm"></i>
            </div>
          </div>

          <!-- Widget 2: Verification Rate -->
          <div class="glass-panel glow-card-teal rounded-2xl p-5 flex items-center justify-between group cursor-pointer dark:bg-slate-900/85 dark:border-slate-800/80">
            <div class="space-y-1">
              <span class="text-[10px] font-black uppercase tracking-wider text-slate-455 dark:text-slate-400">KYC Verification Rate</span>
              <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">94.2%</span>
                <span class="text-[10px] font-extrabold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 px-2 py-0.5 rounded-full flex items-center gap-0.5">
                  <i class="fa-solid fa-caret-up text-[9px]"></i> 2.1%
                </span>
              </div>
              <p class="text-[9px] text-slate-400 dark:text-slate-500 font-medium">455,016 verified resident profiles</p>
            </div>
            <div class="h-11 w-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100/60 dark:border-emerald-900/40 flex items-center justify-center shadow-xs transition duration-300 group-hover:bg-emerald-600 group-hover:text-white">
              <i class="fa-solid fa-circle-check text-sm"></i>
            </div>
          </div>

          <!-- Widget 3: Active Staff Sessions -->
          <div class="glass-panel glow-card-purple rounded-2xl p-5 flex items-center justify-between group cursor-pointer dark:bg-slate-900/85 dark:border-slate-800/80">
            <div class="space-y-1">
              <span class="text-[10px] font-black uppercase tracking-wider text-slate-455 dark:text-slate-400">Active Staff Sessions</span>
              <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">1,842</span>
                <span class="text-[10px] font-extrabold text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-950/30 px-2 py-0.5 rounded-full flex items-center gap-0.5">
                  <i class="fa-solid fa-caret-up text-[9px]"></i> 8.7%
                </span>
              </div>
              <p class="text-[9px] text-slate-400 dark:text-slate-500 font-medium">Caloocan portal personnel online</p>
            </div>
            <div class="h-11 w-11 rounded-xl bg-purple-50 dark:bg-purple-950/20 text-purple-750 dark:text-purple-400 border border-purple-100/60 dark:border-purple-900/40 flex items-center justify-center shadow-xs transition duration-300 group-hover:bg-purple-600 group-hover:text-white">
              <i class="fa-solid fa-network-wired text-sm"></i>
            </div>
          </div>

          <!-- Widget 4: Recent Applications -->
          <div class="glass-panel glow-card-amber rounded-2xl p-5 flex items-center justify-between group cursor-pointer dark:bg-slate-900/85 dark:border-slate-800/80">
            <div class="space-y-1">
              <span class="text-[10px] font-black uppercase tracking-wider text-slate-455 dark:text-slate-400">Recent Applications</span>
              <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">24,198</span>
                <span class="text-[10px] font-extrabold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 px-2 py-0.5 rounded-full flex items-center gap-0.5">
                  <i class="fa-solid fa-caret-up text-[9px]"></i> 15.3%
                </span>
              </div>
              <p class="text-[9px] text-slate-400 dark:text-slate-500 font-medium">Permits, Scholarships & Social services</p>
            </div>
            <div class="h-11 w-11 rounded-xl bg-amber-50 dark:bg-amber-950/20 text-amber-705 dark:text-amber-450 border border-amber-100/60 dark:border-amber-900/40 flex items-center justify-center shadow-xs transition duration-300 group-hover:bg-amber-600 group-hover:text-white">
              <i class="fa-solid fa-arrow-right-arrow-left text-sm"></i>
            </div>
          </div>

        </div>

        <!-- Advanced Analytics Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          
          <!-- Area Chart Column (2/3 width) -->
          <div class="lg:col-span-2 space-y-6">
            
            <!-- Glowing Area Chart Panel -->
            <div class="glass-panel rounded-2xl p-6 shadow-xs space-y-4 dark:bg-slate-900/85 dark:border-slate-800/80">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
                <div>
                  <h3 class="font-extrabold text-slate-800 dark:text-white text-xs sm:text-sm tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-chart-area text-brand-dark dark:text-brand-medium"></i> Citizen Registration & Activity Trends
                  </h3>
                  <p class="text-[10px] text-slate-400 dark:text-slate-550 font-medium">Monthly transaction activity logs spanning the Caloocan LGU sub-sectors</p>
                </div>
                <div class="flex items-center gap-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700 px-3 py-1.5 rounded-lg text-[9px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wide shrink-0">
                  <div class="flex items-center gap-1">
                    <span class="inline-block h-2 w-2 rounded-full bg-brand-dark dark:bg-brand-medium"></span> 
                    <span>Citizens</span>
                  </div>
                  <div class="flex items-center gap-1">
                    <span class="inline-block h-2 w-2 rounded-full bg-amber-500"></span> 
                    <span>Applications</span>
                  </div>
                </div>
              </div>
              <div class="relative h-72 w-full">
                <canvas id="trendsChart"></canvas>
              </div>
            </div>
            
            <!-- Recent Activities Feed -->
            <div class="glass-panel rounded-2xl p-6 shadow-xs space-y-4 dark:bg-slate-900/85 dark:border-slate-800/80">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="font-extrabold text-slate-800 dark:text-white text-xs sm:text-sm tracking-tight">Recent Activity Feed</h3>
                  <p class="text-[10px] text-slate-400 dark:text-slate-550 font-medium">Real-time gateway logs of verified system events across departments</p>
                </div>
                <span class="inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-450 px-2 py-0.5 rounded text-[9px] font-bold">
                  <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 pulse-glow-green"></span> Live Stream
                </span>
              </div>
              <div class="divide-y divide-slate-100 dark:divide-slate-800">
                
                <!-- Activity 1 -->
                <div class="py-3.5 flex items-center justify-between gap-4 text-xs transition hover:bg-slate-50/50 dark:hover:bg-slate-850/50 px-2 rounded-lg -mx-2">
                  <div class="flex items-center gap-3 min-w-0">
                    <div class="h-8.5 w-8.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-100 dark:border-emerald-900/30">
                      <i class="fa-solid fa-user-check text-xs"></i>
                    </div>
                    <div class="min-w-0 space-y-0.5">
                      <p class="font-bold text-slate-700 dark:text-slate-200 truncate text-[11px]">KYC Profile Verification Approved</p>
                      <p class="text-[9px] text-slate-450 dark:text-slate-400 font-medium">Citizen: Maria Santos • Dept: Health & Sanitation</p>
                    </div>
                  </div>
                  <span class="text-[9px] font-black text-slate-400 dark:text-slate-550 shrink-0">3 mins ago</span>
                </div>
                
                <!-- Activity 2 -->
                <div class="py-3.5 flex items-center justify-between gap-4 text-xs transition hover:bg-slate-50/50 dark:hover:bg-slate-850/50 px-2 rounded-lg -mx-2">
                  <div class="flex items-center gap-3 min-w-0">
                    <div class="h-8.5 w-8.5 rounded-lg bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400 flex items-center justify-center shrink-0 border border-amber-100 dark:border-amber-900/30">
                      <i class="fa-solid fa-file-invoice text-xs"></i>
                    </div>
                    <div class="min-w-0 space-y-0.5">
                      <p class="font-bold text-slate-700 dark:text-slate-200 truncate text-[11px]">New Business Permit Registered</p>
                      <p class="text-[9px] text-slate-450 dark:text-slate-400 font-medium">Merchant: Caloocan Food Hub • Dept: Licensing</p>
                    </div>
                  </div>
                  <span class="text-[9px] font-black text-slate-400 dark:text-slate-550 shrink-0">12 mins ago</span>
                </div>
                
                <!-- Activity 3 -->
                <div class="py-3.5 flex items-center justify-between gap-4 text-xs transition hover:bg-slate-50/50 dark:hover:bg-slate-850/50 px-2 rounded-lg -mx-2">
                  <div class="flex items-center gap-3 min-w-0">
                    <div class="h-8.5 w-8.5 rounded-lg bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-450 flex items-center justify-center shrink-0 border border-blue-100 dark:border-blue-900/30">
                      <i class="fa-solid fa-graduation-cap text-xs"></i>
                    </div>
                    <div class="min-w-0 space-y-0.5">
                      <p class="font-bold text-slate-700 dark:text-slate-200 truncate text-[11px]">Scholarship Application Submitted</p>
                      <p class="text-[9px] text-slate-450 dark:text-slate-400 font-medium">Applicant: Juan Dela Cruz • Dept: Education</p>
                    </div>
                  </div>
                  <span class="text-[9px] font-black text-slate-400 dark:text-slate-550 shrink-0">45 mins ago</span>
                </div>

              </div>
            </div>
          </div>

          <!-- Right Column (1/3 width) -->
          <div class="space-y-6">
            
            <!-- Doughnut Chart: Demographics -->
            <div class="glass-panel rounded-2xl p-6 shadow-xs space-y-4 dark:bg-slate-900/85 dark:border-slate-800/80">
              <div>
                <h3 class="font-extrabold text-slate-800 dark:text-white text-xs sm:text-sm tracking-tight flex items-center gap-2">
                  <i class="fa-solid fa-chart-pie text-brand-dark dark:text-brand-medium"></i> Citizen Demographics
                </h3>
                <p class="text-[10px] text-slate-400 dark:text-slate-550 font-medium">Distribution of registered residents by classification</p>
              </div>
              <div class="relative h-48 w-full flex items-center justify-center">
                <canvas id="demographicsChart"></canvas>
              </div>
            </div>

            <!-- Radar Chart: Municipal Service Workload -->
            <div class="glass-panel rounded-2xl p-6 shadow-xs space-y-4 dark:bg-slate-900/85 dark:border-slate-800/80">
              <div>
                <h3 class="font-extrabold text-slate-800 dark:text-white text-xs sm:text-sm tracking-tight flex items-center gap-2">
                  <i class="fa-solid fa-chart-line text-brand-dark dark:text-brand-medium"></i> Municipal Service Load
                </h3>
                <p class="text-[10px] text-slate-400 dark:text-slate-550 font-medium">Workload distribution ratios across LGU sectors</p>
              </div>
              <div class="relative h-56 w-full flex items-center justify-center">
                <canvas id="workloadRadarChart"></canvas>
              </div>
            </div>

            <!-- Municipal Progress KPIs -->
            <div class="glass-panel rounded-2xl p-6 shadow-xs space-y-4 dark:bg-slate-900/85 dark:border-slate-800/80">
              <div>
                <h3 class="font-extrabold text-slate-800 dark:text-white text-xs sm:text-sm tracking-tight">Municipal Progress KPI</h3>
                <p class="text-[10px] text-slate-400 dark:text-slate-550 font-medium">Completion metrics across verified LGU programs</p>
              </div>
              <div class="space-y-4 pt-1">
                
                <!-- KPI 1 -->
                <div class="space-y-1.5">
                  <div class="flex justify-between text-[10px] font-black uppercase text-slate-500 dark:text-slate-400">
                    <span>Universal Health Coverage</span>
                    <span class="text-brand-dark dark:text-brand-medium">82%</span>
                  </div>
                  <div class="h-2 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-brand-medium to-brand-dark rounded-full" style="width: 82%"></div>
                  </div>
                </div>
                
                <!-- KPI 2 -->
                <div class="space-y-1.5">
                  <div class="flex justify-between text-[10px] font-black uppercase text-slate-500 dark:text-slate-400">
                    <span>Scholarship Distribution</span>
                    <span class="text-amber-650 dark:text-amber-400">65%</span>
                  </div>
                  <div class="h-2 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-amber-400 to-amber-600 rounded-full" style="width: 65%"></div>
                  </div>
                </div>
                
                <!-- KPI 3 -->
                <div class="space-y-1.5">
                  <div class="flex justify-between text-[10px] font-black uppercase text-slate-500 dark:text-slate-400">
                    <span>Barangay Sanitation Verification</span>
                    <span class="text-emerald-600 dark:text-emerald-400">91%</span>
                  </div>
                  <div class="h-2 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-full" style="width: 91%"></div>
                  </div>
                </div>

              </div>
            </div>

          </div>
        </div>
      </div>
    </main>

    <!-- Load ChartJS Library & External Analytics Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="<?php echo $basePathResolver; ?>assets/js/dashboard-analytics.js"></script>
    
<?php include '../includes/footer.php'; ?>