<?php
$basePath = '../../';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<main class="flex-1 p-6 md:p-8 w-full space-y-6 overflow-y-auto">

  <!-- BREADCRUMB & HEADER -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200/60 pb-5">
    <div class="space-y-1">
      <div class="flex items-center space-x-2 text-xs font-bold uppercase tracking-wider text-slate-400">
        <span>My Account</span>
        <i class="fa-solid fa-chevron-right text-[8px] opacity-60"></i>
        <a href="<?php echo $basePath; ?>pages/profile/settings.php" class="hover:text-brand-dark transition">Account Settings</a>
        <i class="fa-solid fa-chevron-right text-[8px] opacity-60"></i>
        <span class="text-brand-dark">Security & Password</span>
      </div>
      <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2 mt-4">
        <i class="fa-solid fa-lock text-brand-dark"></i>
        Change Password & Security
      </h1>
      <p class="text-xs text-slate-500 max-w-2xl leading-relaxed">
        Update your login credentials securely to maintain system integrity and account safety.
      </p>
    </div>

    <!-- Security Session Badge -->
    <div class="shrink-0 flex items-center gap-2 bg-emerald-50 border border-emerald-200/80 text-emerald-700 text-[10px] font-black uppercase tracking-wider px-3.5 py-2.5 rounded-xl shadow-xs">
      <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
      <span>2FA Verified Session</span>
    </div>
  </div>

  <!-- 2-COLUMN BALANCED WORKSPACE GRID -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

    <!-- LEFT COLUMN: Security Context & Password Policy (5 cols) -->
    <div class="lg:col-span-5 space-y-5">

      <!-- ACCOUNT SECURITY PROFILE CARD -->
      <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs space-y-5">
        <div class="flex items-center gap-4 border-b border-slate-100 pb-5">
          <div class="relative">
            <img
              id="pwAccountAvatar"
              src="https://ui-avatars.com/api/?name=User&background=EEF5FF&color=176B87&bold=true&size=128"
              alt="User Profile"
              class="w-14 h-14 rounded-full object-cover border-2 border-white ring-2 ring-brand-border shadow-sm"
            >
            <div class="absolute -bottom-1 -right-1 h-5 w-5 bg-emerald-500 rounded-full border-2 border-white flex items-center justify-center text-white text-[9px]">
              <i class="fa-solid fa-shield"></i>
            </div>
          </div>
          <div class="space-y-0.5">
            <p id="pwAccountName" class="text-sm font-black text-slate-800 tracking-tight">Loading profile...</p>
            <span id="pwAccountRole" class="inline-block text-[9px] font-black uppercase tracking-widest text-brand-dark bg-brand-light border border-brand-border/60 px-2 py-0.5 rounded-full">...</span>
            <p id="pwAccountEmail" class="text-[10px] text-slate-400 font-semibold pt-0.5">...</p>
          </div>
        </div>

        <div class="space-y-3 text-xs">
          <div class="flex items-center justify-between">
            <span class="text-slate-400 font-semibold">Account Designation</span>
            <span id="pwAccountDept" class="font-black text-slate-700">General Staff</span>
          </div>
          <div class="border-t border-slate-100"></div>
          <div class="flex items-center justify-between">
            <span class="text-slate-400 font-semibold">Credential Health</span>
            <span class="inline-flex items-center gap-1.5 text-emerald-700 bg-emerald-50 border border-emerald-200/80 font-black px-2 py-0.5 rounded-full text-[9px] uppercase tracking-wider">
              <i class="fa-solid fa-shield-check text-[10px] text-emerald-500"></i> Protected
            </span>
          </div>
          <div class="border-t border-slate-100"></div>
          <div class="flex items-center justify-between">
            <span class="text-slate-400 font-semibold">Policy Renewal</span>
            <span class="font-bold text-slate-600">Every 90 Days</span>
          </div>
        </div>
      </div>

      <!-- PASSWORD POLICY TIP CARD -->
      <div class="bg-gradient-to-br from-slate-900 via-slate-850 to-slate-950 rounded-2xl p-6 space-y-4 shadow-md text-white border border-slate-800">
        <div class="flex items-center gap-3">
          <div class="h-9 w-9 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center shrink-0">
            <i class="fa-solid fa-vault text-brand-medium text-sm"></i>
          </div>
          <div>
            <h3 class="text-xs font-black tracking-tight text-white">CIVENTRAL Password Standard</h3>
            <p class="text-[10px] text-slate-400 font-semibold">Enforced by Municipal Security Directives</p>
          </div>
        </div>

        <p class="text-[11px] text-slate-300 font-medium leading-relaxed">
          Passwords must meet all complexity checks to protect government databases against unauthorized intrusion or privilege escalation.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-2 border-t border-white/10">
          <div class="flex items-center gap-2 text-[10px] text-slate-300 font-bold">
            <i class="fa-solid fa-circle-check text-emerald-400 text-[10px]"></i>
            <span>8+ Characters</span>
          </div>
          <div class="flex items-center gap-2 text-[10px] text-slate-300 font-bold">
            <i class="fa-solid fa-circle-check text-emerald-400 text-[10px]"></i>
            <span>Uppercase (A-Z)</span>
          </div>
          <div class="flex items-center gap-2 text-[10px] text-slate-300 font-bold">
            <i class="fa-solid fa-circle-check text-emerald-400 text-[10px]"></i>
            <span>Lowercase (a-z)</span>
          </div>
          <div class="flex items-center gap-2 text-[10px] text-slate-300 font-bold">
            <i class="fa-solid fa-circle-check text-emerald-400 text-[10px]"></i>
            <span>Number (0-9)</span>
          </div>
          <div class="col-span-1 sm:col-span-2 flex items-center gap-2 text-[10px] text-slate-300 font-bold">
            <i class="fa-solid fa-circle-check text-emerald-400 text-[10px]"></i>
            <span>Special Character (!@#$&*)</span>
          </div>
        </div>
      </div>

      <!-- SECURITY WARNING NOTICE -->
      <div class="flex items-start gap-3 bg-amber-50/70 border border-amber-200/80 rounded-2xl p-4.5">
        <i class="fa-solid fa-triangle-exclamation text-amber-500 text-sm shrink-0 mt-0.5"></i>
        <div class="space-y-1">
          <p class="text-[10px] font-black text-amber-800 uppercase tracking-wider">Security Awareness</p>
          <p class="text-[10px] text-amber-800/80 font-medium leading-relaxed">
            Never disclose your credentials to anyone. If you notice unexpected activity, contact <span class="font-black text-amber-900">it-support@caloocan.gov.ph</span>.
          </p>
        </div>
      </div>

    </div>

    <!-- RIGHT COLUMN: CHANGE PASSWORD FORM CARD (7 cols) -->
    <div class="lg:col-span-7 space-y-6">

      <div class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">

        <!-- Card Header -->
        <div class="bg-slate-50 border-b border-slate-200/60 px-6 py-4 flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="h-7 w-7 rounded-lg bg-brand-light border border-brand-border flex items-center justify-center">
              <i class="fa-solid fa-key text-brand-dark text-xs"></i>
            </div>
            <div>
              <span class="text-xs font-black text-slate-700 block leading-none tracking-tight">Credential Security Update</span>
              <span class="text-[9px] text-slate-400 font-semibold uppercase tracking-wider">Verify current password and set your new credentials</span>
            </div>
          </div>
          <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-lg">Encrypted</span>
        </div>

        <!-- Form Body -->
        <div class="p-6 space-y-6">

          <!-- Current Password -->
          <div class="space-y-2">
            <label for="currentPassword" class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">
              Current Password
            </label>
            <div class="relative">
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                <i class="fa-solid fa-lock text-sm"></i>
              </span>
              <input
                type="password"
                id="currentPassword"
                placeholder="Enter your current account password"
                autocomplete="current-password"
                class="pl-10 pr-11 py-3 border border-slate-200 bg-white rounded-xl text-xs w-full font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition placeholder:text-slate-300 tracking-widest"
              >
              <button
                type="button"
                onclick="toggleVisibility('currentPassword', 'eyeCurrentPassword')"
                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition cursor-pointer focus:outline-none"
                tabindex="-1">
                <i id="eyeCurrentPassword" class="fa-solid fa-eye text-sm"></i>
              </button>
            </div>
          </div>

          <div class="border-t border-slate-100"></div>

          <!-- New Password -->
          <div class="space-y-2">
            <label for="newPassword" class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">
              New Password
            </label>
            <div class="relative">
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                <i class="fa-solid fa-key text-sm"></i>
              </span>
              <input
                type="password"
                id="newPassword"
                placeholder="Create a strong new password"
                autocomplete="new-password"
                oninput="checkPasswordStrength()"
                class="pl-10 pr-11 py-3 border border-slate-200 bg-white rounded-xl text-xs w-full font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition placeholder:text-slate-300 tracking-widest"
              >
              <button
                type="button"
                onclick="toggleVisibility('newPassword', 'eyeNewPassword')"
                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition cursor-pointer focus:outline-none"
                tabindex="-1">
                <i id="eyeNewPassword" class="fa-solid fa-eye text-sm"></i>
              </button>
            </div>

            <!-- Password Strength Progress Bar -->
            <div class="pt-1 space-y-1.5">
              <div class="flex gap-1">
                <div id="strengthBar1" class="h-1 flex-1 rounded-full bg-slate-100 transition-all duration-300"></div>
                <div id="strengthBar2" class="h-1 flex-1 rounded-full bg-slate-100 transition-all duration-300"></div>
                <div id="strengthBar3" class="h-1 flex-1 rounded-full bg-slate-100 transition-all duration-300"></div>
                <div id="strengthBar4" class="h-1 flex-1 rounded-full bg-slate-100 transition-all duration-300"></div>
                <div id="strengthBar5" class="h-1 flex-1 rounded-full bg-slate-100 transition-all duration-300"></div>
              </div>
              <p id="strengthLabel" class="text-[10px] font-bold text-slate-300 leading-none tracking-wide">Enter a password to evaluate strength</p>
            </div>

            <!-- Live Checklist -->
            <div class="pt-3 bg-slate-50/70 border border-slate-150 rounded-xl p-4 space-y-2">
              <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-3">Live Validation Checks</p>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                <div id="rule-length" class="flex items-center gap-2 text-[10px] font-semibold text-slate-400 transition-all duration-200">
                  <span id="icon-length" class="h-4 w-4 rounded-full bg-slate-200 flex items-center justify-center shrink-0 transition-all duration-200">
                    <i class="fa-solid fa-minus text-[7px] text-slate-400"></i>
                  </span>
                  At least 8 characters
                </div>
                <div id="rule-upper" class="flex items-center gap-2 text-[10px] font-semibold text-slate-400 transition-all duration-200">
                  <span id="icon-upper" class="h-4 w-4 rounded-full bg-slate-200 flex items-center justify-center shrink-0 transition-all duration-200">
                    <i class="fa-solid fa-minus text-[7px] text-slate-400"></i>
                  </span>
                  One uppercase letter (A-Z)
                </div>
                <div id="rule-lower" class="flex items-center gap-2 text-[10px] font-semibold text-slate-400 transition-all duration-200">
                  <span id="icon-lower" class="h-4 w-4 rounded-full bg-slate-200 flex items-center justify-center shrink-0 transition-all duration-200">
                    <i class="fa-solid fa-minus text-[7px] text-slate-400"></i>
                  </span>
                  One lowercase letter (a-z)
                </div>
                <div id="rule-number" class="flex items-center gap-2 text-[10px] font-semibold text-slate-400 transition-all duration-200">
                  <span id="icon-number" class="h-4 w-4 rounded-full bg-slate-200 flex items-center justify-center shrink-0 transition-all duration-200">
                    <i class="fa-solid fa-minus text-[7px] text-slate-400"></i>
                  </span>
                  One number (0-9)
                </div>
                <div id="rule-special" class="flex items-center gap-2 text-[10px] font-semibold text-slate-400 transition-all duration-200 sm:col-span-2">
                  <span id="icon-special" class="h-4 w-4 rounded-full bg-slate-200 flex items-center justify-center shrink-0 transition-all duration-200">
                    <i class="fa-solid fa-minus text-[7px] text-slate-400"></i>
                  </span>
                  One special character (!@#$&*)
                </div>
              </div>
            </div>
          </div>

          <div class="border-t border-slate-100"></div>

          <!-- Confirm New Password -->
          <div class="space-y-2">
            <label for="confirmPassword" class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">
              Confirm New Password
            </label>
            <div class="relative">
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                <i class="fa-solid fa-check-double text-sm"></i>
              </span>
              <input
                type="password"
                id="confirmPassword"
                placeholder="Re-enter your new password"
                autocomplete="new-password"
                oninput="checkConfirmMatch()"
                class="pl-10 pr-11 py-3 border border-slate-200 bg-white rounded-xl text-xs w-full font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition placeholder:text-slate-300 tracking-widest"
              >
              <button
                type="button"
                onclick="toggleVisibility('confirmPassword', 'eyeConfirmPassword')"
                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition cursor-pointer focus:outline-none"
                tabindex="-1">
                <i id="eyeConfirmPassword" class="fa-solid fa-eye text-sm"></i>
              </button>
            </div>
            <!-- Match Feedback -->
            <div id="matchFeedback" class="hidden flex items-center gap-1.5 text-[10px] font-bold">
              <i id="matchFeedbackIcon" class="fa-solid fa-xmark text-red-500"></i>
              <span id="matchFeedbackText" class="text-red-500">Passwords do not match</span>
            </div>
          </div>

        </div>

        <!-- ACTION FOOTER -->
        <div class="bg-slate-50 border-t border-slate-200/60 px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-3">
          <p class="text-[10px] text-slate-400 font-semibold leading-relaxed">
            <i class="fa-solid fa-circle-info text-slate-300 mr-1"></i>
            Changes take effect immediately upon saving.
          </p>
          <div class="flex items-center gap-2.5 shrink-0">
            <a href="<?php echo $basePath; ?>pages/profile/settings.php"
              class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 font-bold px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 cursor-pointer transition">
              <i class="fa-solid fa-arrow-left text-[10px]"></i>
              Cancel
            </a>
            <button
              onclick="savePassword()"
              id="savePasswordBtn"
              class="bg-[#0f172a] hover:bg-slate-800 text-white font-bold px-5 py-2.5 rounded-xl text-xs flex items-center gap-2 cursor-pointer transition shadow-xs">
              <i class="fa-solid fa-floppy-disk text-[10px]"></i>
              Update Password
            </button>
          </div>
        </div>

      </div>

    </div>

  </div>

</main>

<!-- TOAST NOTIFICATION HUB -->
<div id="pwToast" class="fixed bottom-5 right-5 z-50 flex items-center gap-3 bg-slate-900 text-white text-xs font-bold px-5 py-3.5 rounded-xl shadow-2xl transform translate-y-4 opacity-0 pointer-events-none transition-all duration-300 max-w-xs">
  <div id="pwToastIcon" class="h-6 w-6 rounded-full bg-emerald-500 flex items-center justify-center text-white text-[11px] shrink-0">
    <i class="fa-solid fa-check"></i>
  </div>
  <div class="flex flex-col leading-tight">
    <span id="pwToastMsg" class="tracking-wide">Password updated successfully!</span>
    <span id="pwToastSub" class="text-[9px] text-slate-400 font-semibold mt-0.5"></span>
  </div>
</div>



<?php include '../../includes/footer.php'; ?>
