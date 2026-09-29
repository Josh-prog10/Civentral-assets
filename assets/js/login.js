function togglePasswordVisibility() {
      const passwordInput = document.getElementById('password');
      const passwordIcon = document.getElementById('passwordIcon');
      
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordIcon.classList.remove('fa-eye-slash');
        passwordIcon.classList.add('fa-eye');
      } else {
        passwordInput.type = 'password';
        passwordIcon.classList.remove('fa-eye');
        passwordIcon.classList.add('fa-eye-slash');
      }
    }

      // FOR THE FUCKING MODALS 
    function showStatusAlert(type, customMessage = "") {
      const modal = document.getElementById('statusModal');
      const icon = document.getElementById('modalIcon');
      const msgText = document.getElementById('modalMessage');

      modal.classList.remove('hidden');
      modal.className = "mt-4 border rounded-lg p-4 flex items-start space-x-3 transition-all duration-300";

      if (type === 'success') {
        modal.classList.add('border-green-200', 'bg-green-50', 'text-green-700');
        icon.innerHTML = '<img src="assets/images/spinner.svg" class="h-6 w-6 shrink-0" alt="Loading...">';
        msgText.textContent = customMessage || "Login successful. Redirecting...";
      } else if (type === 'error') {
        modal.classList.add('border-red-200', 'bg-red-50', 'text-red-700');
        icon.innerHTML = '<i class="fa-solid fa-circle-exclamation text-xl"></i>';
        msgText.textContent = customMessage || "Login failed. Please check your credentials.";
      } else if (type === 'maintenance') {
        modal.classList.add('border-brand-border', 'bg-brand-light', 'text-brand-dark');
        icon.innerHTML = '<i class="fa-solid fa-circle-info text-xl"></i>';
        msgText.textContent = customMessage || "System maintenance is scheduled for Sunday, 11:00 PM–1:00 AM. Save drafts before then.";
      }
    }

    async function handleLogin(event) {
      event.preventDefault();
      const id = document.getElementById('employeeId').value.trim();
      const pass = document.getElementById('password').value.trim();
      const submitBtn = event.target.querySelector('button[type="submit"]');
      const originalBtnHtml = submitBtn ? submitBtn.innerHTML : 'Sign in';

      if (!id || !pass) {
        showStatusAlert('error', 'Please fill in both Employee ID / Email and Password.');
        return;
      }
      
      if (id.toLowerCase() === 'maintenance') {
        showStatusAlert('maintenance');
        return;
      }

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="inline-flex items-center justify-center gap-2"><img src="assets/images/spinner.svg" class="h-4 w-4 inline" alt="loading"> Authenticating...</span>';
      }

      try {
        const response = await fetch('api/employee/login.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ employeeId: id, password: pass })
        });

        const data = await response.json();

        if (data.status === 'otp_required') {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
          }
          openOtpModal(data.email || '');
        } else if (data.status === 'success') {
          showStatusAlert('success', data.message || 'Login successful! Redirecting to dashboard...');
          if (submitBtn) {
            submitBtn.innerHTML = '<span class="inline-flex items-center justify-center gap-2"><img src="assets/images/spinner.svg" class="h-4 w-4 inline" alt="loading"> Entering Dashboard...</span>';
          }
          setTimeout(() => {
            window.location.href = 'pages/dashboard.php';
          }, 1200);
        } else if (data.status === 'maintenance') {
          showStatusAlert('maintenance', data.message);
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
          }
        } else {
          showStatusAlert('error', data.message || 'Login failed. Please check your credentials.');
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
          }
        }
      } catch (err) {
        // Fallback for direct offline / static testing
        const validIds = ['SADM-2026-001', 'EMP-1111-ADMIN-2026', 'ADMIN', 'SUPERADMIN@CIVENTRAL.GOV.PH'];
        if (validIds.includes(id.toUpperCase()) && pass === '1234') {
          showStatusAlert('success', 'Login successful! Redirecting to dashboard...');
          if (submitBtn) {
            submitBtn.innerHTML = '<span class="inline-flex items-center justify-center gap-2"><img src="assets/images/spinner.svg" class="h-4 w-4 inline" alt="loading"> Entering Dashboard...</span>';
          }
          setTimeout(() => {
            window.location.href = 'pages/dashboard.php';
          }, 1200);
        } else {
          showStatusAlert('error', 'Login failed. Invalid credentials or network error.');
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
          }
        }
      }
    }

    // OTP MODAL CONTROLLERS & EVENT HANDLERS
    let resendCooldownTimer = null;

    function openOtpModal(email = '') {
      const modal = document.getElementById('otpModal');
      const emailEl = document.getElementById('otpMaskedEmail');
      const otpInputs = document.querySelectorAll('.otp-input');

      if (emailEl && email) {
        emailEl.textContent = email;
      }

      // Reset inputs & alerts
      otpInputs.forEach(input => input.value = '');
      showOtpAlert('');

      modal.classList.remove('hidden');
      setTimeout(() => {
        modal.classList.remove('opacity-0');
        modal.querySelector('.transform').classList.remove('scale-95');
        modal.querySelector('.transform').classList.add('scale-100');
        if (otpInputs[0]) otpInputs[0].focus();
      }, 10);
    }

    function closeOtpModal() {
      const modal = document.getElementById('otpModal');
      modal.classList.add('opacity-0');
      modal.querySelector('.transform').classList.remove('scale-100');
      modal.querySelector('.transform').classList.add('scale-95');
      setTimeout(() => {
        modal.classList.add('hidden');
      }, 200);
    }

    function showOtpAlert(message, isError = true) {
      const alertEl = document.getElementById('otpAlert');
      if (!alertEl) return;
      if (!message) {
        alertEl.classList.add('hidden');
        alertEl.textContent = '';
        return;
      }
      alertEl.classList.remove('hidden', 'bg-red-50', 'text-red-700', 'border-red-200', 'bg-green-50', 'text-green-700', 'border-green-200');
      if (isError) {
        alertEl.classList.add('bg-red-50', 'text-red-700', 'border-red-200');
      } else {
        alertEl.classList.add('bg-green-50', 'text-green-700', 'border-green-200');
      }
      alertEl.textContent = message;
    }

    // Auto-advance & paste handler for 6 OTP boxes
    document.addEventListener('DOMContentLoaded', () => {
      const otpInputs = document.querySelectorAll('.otp-input');
      otpInputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
          const val = e.target.value;
          if (val && index < otpInputs.length - 1) {
            otpInputs[index + 1].focus();
          }
        });

        input.addEventListener('keydown', (e) => {
          if (e.key === 'Backspace' && !e.target.value && index > 0) {
            otpInputs[index - 1].focus();
          }
        });

        input.addEventListener('paste', (e) => {
          e.preventDefault();
          const pastedData = (e.clipboardData || window.clipboardData).getData('text').trim();
          if (/^\d{6}$/.test(pastedData)) {
            pastedData.split('').forEach((char, i) => {
              if (otpInputs[i]) otpInputs[i].value = char;
            });
            otpInputs[5].focus();
          }
        });
      });
    });

    async function handleVerifyOTP(event) {
      event.preventDefault();
      const otpInputs = document.querySelectorAll('.otp-input');
      const otpCode = Array.from(otpInputs).map(i => i.value.trim()).join('');

      if (otpCode.length !== 6 || !/^\d{6}$/.test(otpCode)) {
        showOtpAlert('Please enter a complete 6-digit OTP code.');
        return;
      }

      const verifyBtn = document.getElementById('btnVerifyOtp');
      const originalBtnText = verifyBtn ? verifyBtn.innerHTML : 'Verify';

      if (verifyBtn) {
        verifyBtn.disabled = true;
        verifyBtn.innerHTML = '<span class="inline-flex items-center gap-2"><img src="assets/images/spinner.svg" class="h-4 w-4 inline" alt="loading"> Verifying...</span>';
      }

      try {
        const response = await fetch('api/employee/verify-otp.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ otp: otpCode })
        });
        const data = await response.json();

        if (data.status === 'success') {
          showOtpAlert('OTP verified! Entering Dashboard...', false);
          if (verifyBtn) {
            verifyBtn.innerHTML = '<span class="inline-flex items-center gap-2"><img src="assets/images/spinner.svg" class="h-4 w-4 inline" alt="loading"> Entering Dashboard...</span>';
          }
          setTimeout(() => {
            window.location.href = 'pages/dashboard.php';
          }, 1000);
        } else {
          showOtpAlert(data.message || 'Verification failed.');
          if (verifyBtn) {
            verifyBtn.disabled = false;
            verifyBtn.innerHTML = originalBtnText;
          }
        }
      } catch (err) {
        showOtpAlert('Network error verifying OTP code.');
        if (verifyBtn) {
          verifyBtn.disabled = false;
          verifyBtn.innerHTML = originalBtnText;
        }
      }
    }

    async function handleResendOTP() {
      const resendBtn = document.getElementById('btnResendOtp');
      const timerText = document.getElementById('resendTimerText');
      if (resendBtn.disabled) return;

      resendBtn.disabled = true;
      showOtpAlert('Resending verification code...', false);

      try {
        const response = await fetch('api/employee/resend-otp.php', { method: 'POST' });
        const data = await response.json();

        if (data.status === 'success') {
          showOtpAlert(data.message || 'A new OTP code has been sent to your email.', false);
          startResendCooldown(60);
        } else {
          showOtpAlert(data.message || 'Failed to resend code.');
          resendBtn.disabled = false;
        }
      } catch (err) {
        showOtpAlert('Network error resending OTP code.');
        resendBtn.disabled = false;
      }
    }

    function startResendCooldown(seconds) {
      const resendBtn = document.getElementById('btnResendOtp');
      const timerText = document.getElementById('resendTimerText');
      let countdown = seconds;

      resendBtn.disabled = true;
      resendBtn.classList.add('opacity-50', 'cursor-not-allowed');
      timerText.classList.remove('hidden');
      timerText.textContent = `(${countdown}s)`;

      if (resendCooldownTimer) clearInterval(resendCooldownTimer);

      resendCooldownTimer = setInterval(() => {
        countdown--;
        if (countdown <= 0) {
          clearInterval(resendCooldownTimer);
          resendBtn.disabled = false;
          resendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
          timerText.classList.add('hidden');
          timerText.textContent = '';
        } else {
          timerText.textContent = `(${countdown}s)`;
        }
      }, 1000);
    }

    // ==============================================================
    //  FORGOT PASSWORD MODAL — open / close / step navigation
    // ==============================================================

    let fpResendCooldownTimer = null;

    function openForgotModal() {
      const modal = document.getElementById('forgotModal');

      // Reset to step 1 and clear all fields / alerts
      fpGoToStep(1);
      document.getElementById('fpIdentifier').value = '';
      document.getElementById('fpNewPass').value   = '';
      document.getElementById('fpConfirmPass').value = '';
      ['fpAlert1','fpAlert2','fpAlert3'].forEach(id => {
        const el = document.getElementById(id);
        if (el) { el.classList.add('hidden'); el.textContent = ''; }
      });
      document.querySelectorAll('.fp-otp-input').forEach(i => i.value = '');
      fpUpdateStrength('');

      modal.classList.remove('hidden');
      setTimeout(() => {
        modal.classList.remove('opacity-0');
        modal.querySelector('.transform').classList.remove('scale-95');
        modal.querySelector('.transform').classList.add('scale-100');
        const id = document.getElementById('fpIdentifier');
        if (id) id.focus();
      }, 10);
    }

    function closeForgotModal() {
      const modal = document.getElementById('forgotModal');
      modal.classList.add('opacity-0');
      modal.querySelector('.transform').classList.remove('scale-100');
      modal.querySelector('.transform').classList.add('scale-95');
      setTimeout(() => modal.classList.add('hidden'), 200);
      if (fpResendCooldownTimer) clearInterval(fpResendCooldownTimer);
    }

    function fpGoToStep(step) {
      [1, 2, 3, 4].forEach(n => {
        const el = document.getElementById(`fpStep${n}`);
        if (el) el.classList.toggle('hidden', n !== step);
      });
    }

    // ==============================================================
    //  STEP 1 — Send reset code
    // ==============================================================
    async function handleForgotStep1(event) {
      event.preventDefault();
      const identifier = document.getElementById('fpIdentifier').value.trim();
      const btn        = document.getElementById('fpBtn1');
      const alert      = document.getElementById('fpAlert1');

      if (!identifier) {
        fpShowAlert('fpAlert1', 'Please enter your Employee ID or email.', true);
        return;
      }

      btn.disabled = true;
      btn.innerHTML = '<span class="inline-flex items-center gap-2"><img src="assets/images/spinner.svg" class="h-4 w-4 inline" alt="loading"> Sending...</span>';
      fpHideAlert('fpAlert1');

      try {
        const res  = await fetch('api/employee/forgot-password.php', {
          method:  'POST',
          headers: { 'Content-Type': 'application/json' },
          body:    JSON.stringify({ identifier })
        });
        const data = await res.json();

        if (res.status === 429) {
          fpShowAlert('fpAlert1', data.message || 'Too many requests. Please wait before trying again.', true);
          btn.disabled = false;
          btn.innerHTML = '<span>Send Reset Code</span>';
          return;
        }

        // Both success (account found) and "account not found" return success=true
        // Advance to OTP step regardless
        fpGoToStep(2);
        // Focus first OTP box
        const first = document.querySelector('.fp-otp-input');
        if (first) setTimeout(() => first.focus(), 50);

        // Start resend cooldown immediately
        fpStartResendCooldown(60);

      } catch (err) {
        fpShowAlert('fpAlert1', 'Network error. Please check your connection.', true);
        btn.disabled = false;
        btn.innerHTML = '<span>Send Reset Code</span>';
      }
    }

    // ==============================================================
    //  STEP 2 — Verify OTP  (auto-advance / paste / backspace)
    // ==============================================================
    document.addEventListener('DOMContentLoaded', () => {
      const fpInputs = document.querySelectorAll('.fp-otp-input');
      fpInputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
          // Keep only digits
          e.target.value = e.target.value.replace(/\D/g, '');
          if (e.target.value && index < fpInputs.length - 1) {
            fpInputs[index + 1].focus();
          }
        });
        input.addEventListener('keydown', (e) => {
          if (e.key === 'Backspace' && !e.target.value && index > 0) {
            fpInputs[index - 1].focus();
          }
        });
        input.addEventListener('paste', (e) => {
          e.preventDefault();
          const pasted = (e.clipboardData || window.clipboardData).getData('text').trim();
          if (/^\d{6}$/.test(pasted)) {
            pasted.split('').forEach((ch, i) => { if (fpInputs[i]) fpInputs[i].value = ch; });
            fpInputs[5].focus();
          }
        });
      });

      // Real-time strength meter
      const passInput = document.getElementById('fpNewPass');
      if (passInput) {
        passInput.addEventListener('input', () => fpUpdateStrength(passInput.value));
      }
    });

    async function handleForgotStep2(event) {
      event.preventDefault();
      const fpInputs = document.querySelectorAll('.fp-otp-input');
      const otpCode  = Array.from(fpInputs).map(i => i.value.trim()).join('');

      if (otpCode.length !== 6 || !/^\d{6}$/.test(otpCode)) {
        fpShowAlert('fpAlert2', 'Please enter a complete 6-digit code.', true);
        return;
      }

      const btn = document.getElementById('fpBtn2');
      btn.disabled = true;
      btn.innerHTML = '<span class="inline-flex items-center gap-2"><img src="assets/images/spinner.svg" class="h-4 w-4 inline" alt="loading"> Verifying...</span>';
      fpHideAlert('fpAlert2');

      try {
        const res  = await fetch('api/employee/verify-reset-otp.php', {
          method:  'POST',
          headers: { 'Content-Type': 'application/json' },
          body:    JSON.stringify({ otp: otpCode })
        });
        const data = await res.json();

        if (data.status === 'success') {
          if (fpResendCooldownTimer) clearInterval(fpResendCooldownTimer);
          fpGoToStep(3);
          setTimeout(() => {
            const np = document.getElementById('fpNewPass');
            if (np) np.focus();
          }, 50);
        } else {
          fpShowAlert('fpAlert2', data.message || 'Invalid verification code.', true);
          btn.disabled = false;
          btn.innerHTML = '<span>Verify Code</span>';
        }
      } catch (err) {
        fpShowAlert('fpAlert2', 'Network error. Please try again.', true);
        btn.disabled = false;
        btn.innerHTML = '<span>Verify Code</span>';
      }
    }

    // ==============================================================
    //  STEP 3 — Set new password
    // ==============================================================
    async function handleForgotStep3(event) {
      event.preventDefault();
      const newPass     = document.getElementById('fpNewPass').value;
      const confirmPass = document.getElementById('fpConfirmPass').value;

      if (newPass !== confirmPass) {
        fpShowAlert('fpAlert3', 'Passwords do not match.', true);
        return;
      }

      // Client-side complexity mirror (matches backend rules)
      const complexityOk = newPass.length >= 8 &&
        /[A-Z]/.test(newPass) && /[a-z]/.test(newPass) &&
        /[0-9]/.test(newPass) && /[!@#$&*^%\-_+=?<>]/.test(newPass);

      if (!complexityOk) {
        fpShowAlert('fpAlert3', 'Password must be 8+ characters with uppercase, lowercase, number, and special character.', true);
        return;
      }

      const btn = document.getElementById('fpBtn3');
      btn.disabled = true;
      btn.innerHTML = '<span class="inline-flex items-center gap-2"><img src="assets/images/spinner.svg" class="h-4 w-4 inline" alt="loading"> Resetting...</span>';
      fpHideAlert('fpAlert3');

      try {
        const res  = await fetch('api/employee/reset-password.php', {
          method:  'POST',
          headers: { 'Content-Type': 'application/json' },
          body:    JSON.stringify({ new_password: newPass })
        });
        const data = await res.json();

        if (data.status === 'success') {
          fpGoToStep(4);
        } else {
          fpShowAlert('fpAlert3', data.message || 'Failed to reset password. Please try again.', true);
          btn.disabled = false;
          btn.innerHTML = '<span>Reset Password</span>';
        }
      } catch (err) {
        fpShowAlert('fpAlert3', 'Network error. Please try again.', true);
        btn.disabled = false;
        btn.innerHTML = '<span>Reset Password</span>';
      }
    }

    // ==============================================================
    //  Resend reset code (step 2) — 60-second cooldown
    // ==============================================================
    async function handleForgotResend() {
      const resendBtn  = document.getElementById('fpResendBtn');
      if (resendBtn.disabled) return;

      resendBtn.disabled = true;
      fpShowAlert('fpAlert2', 'Resending reset code...', false);

      // Re-send using the identifier already stored in the session server-side.
      // The forgot-password endpoint handles the rate-limit / OTP rotation.
      const identifier = document.getElementById('fpIdentifier').value.trim();

      try {
        const res  = await fetch('api/employee/forgot-password.php', {
          method:  'POST',
          headers: { 'Content-Type': 'application/json' },
          body:    JSON.stringify({ identifier })
        });
        const data = await res.json();

        if (res.status === 429) {
          fpShowAlert('fpAlert2', data.message || 'Please wait before requesting another code.', true);
          resendBtn.disabled = false;
          return;
        }

        fpShowAlert('fpAlert2', 'A new reset code has been sent to your email.', false);
        // Clear OTP boxes
        document.querySelectorAll('.fp-otp-input').forEach(i => i.value = '');
        document.querySelector('.fp-otp-input')?.focus();
        fpStartResendCooldown(60);

      } catch (err) {
        fpShowAlert('fpAlert2', 'Network error. Please try again.', true);
        resendBtn.disabled = false;
      }
    }

    function fpStartResendCooldown(seconds) {
      const resendBtn  = document.getElementById('fpResendBtn');
      const timerEl    = document.getElementById('fpResendTimer');
      let countdown    = seconds;

      if (!resendBtn || !timerEl) return;
      resendBtn.disabled = true;
      resendBtn.classList.add('opacity-50', 'cursor-not-allowed');
      timerEl.classList.remove('hidden');
      timerEl.textContent = `(${countdown}s)`;

      if (fpResendCooldownTimer) clearInterval(fpResendCooldownTimer);
      fpResendCooldownTimer = setInterval(() => {
        countdown--;
        if (countdown <= 0) {
          clearInterval(fpResendCooldownTimer);
          resendBtn.disabled = false;
          resendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
          timerEl.classList.add('hidden');
          timerEl.textContent = '';
        } else {
          timerEl.textContent = `(${countdown}s)`;
        }
      }, 1000);
    }

    // ==============================================================
    //  Helpers
    // ==============================================================
    function fpShowAlert(alertId, message, isError) {
      const el = document.getElementById(alertId);
      if (!el) return;
      el.classList.remove(
        'hidden',
        'bg-red-50','text-red-700','border-red-200',
        'bg-green-50','text-green-700','border-green-200',
        'bg-blue-50','text-blue-700','border-blue-200'
      );
      if (isError) {
        el.classList.add('bg-red-50','text-red-700','border-red-200');
      } else {
        el.classList.add('bg-blue-50','text-blue-700','border-blue-200');
      }
      el.textContent = message;
    }

    function fpHideAlert(alertId) {
      const el = document.getElementById(alertId);
      if (el) { el.classList.add('hidden'); el.textContent = ''; }
    }

    function toggleFpPass(inputId, iconId) {
      const input = document.getElementById(inputId);
      const icon  = document.getElementById(iconId);
      if (!input || !icon) return;
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
      } else {
        input.type = 'password';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
      }
    }

    function fpUpdateStrength(password) {
      const bar   = document.getElementById('fpStrengthBar');
      const label = document.getElementById('fpStrengthLabel');
      if (!bar || !label) return;

      let score = 0;
      if (password.length >= 8)              score++;
      if (/[A-Z]/.test(password))            score++;
      if (/[a-z]/.test(password))            score++;
      if (/[0-9]/.test(password))            score++;
      if (/[!@#$&*^%\-_+=?<>]/.test(password)) score++;

      const levels = [
        { w: '0%',   color: '',                   text: '' },
        { w: '20%',  color: 'bg-red-400',         text: 'Very Weak' },
        { w: '40%',  color: 'bg-orange-400',      text: 'Weak' },
        { w: '60%',  color: 'bg-yellow-400',      text: 'Fair' },
        { w: '80%',  color: 'bg-blue-400',        text: 'Strong' },
        { w: '100%', color: 'bg-green-500',       text: 'Very Strong' },
      ];

      const lvl = levels[score] || levels[0];
      bar.style.width = password.length ? lvl.w : '0%';
      bar.className   = `h-full rounded-full transition-all duration-300 ${lvl.color}`;
      label.textContent = password.length ? lvl.text : '';
    }