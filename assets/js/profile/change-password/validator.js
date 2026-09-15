// Change Password Validator
// Responsible for validating password length, requirements, and confirmation match

// PASSWORD STRENGTH & LIVE CHECKLIST REQUIREMENTS
const rules = [
  { id: 'length',  regex: /.{8,}/,              label: 'At least 8 characters'      },
  { id: 'upper',   regex: /[A-Z]/,              label: 'One uppercase letter (A-Z)'  },
  { id: 'lower',   regex: /[a-z]/,              label: 'One lowercase letter (a-z)'  },
  { id: 'number',  regex: /[0-9]/,              label: 'One number (0-9)'            },
  { id: 'special', regex: /[!@#$&*^%\-_+=?<>]/, label: 'One special character'      }
];

// CONFIRM PASSWORD MATCH CHECKER
function checkConfirmMatch() {
  const newPwEl = document.getElementById('newPassword');
  const confirmPwEl = document.getElementById('confirmPassword');
  const feedback = document.getElementById('matchFeedback');
  const icon = document.getElementById('matchFeedbackIcon');
  const text = document.getElementById('matchFeedbackText');

  if (!newPwEl || !confirmPwEl || !feedback) return;

  const newPw = newPwEl.value;
  const confirmPw = confirmPwEl.value;

  if (confirmPw.length === 0) {
    feedback.classList.add('hidden');
    confirmPwEl.classList.remove('border-rose-400', 'border-emerald-400');
    return;
  }

  feedback.classList.remove('hidden');

  if (newPw === confirmPw) {
    if (icon) icon.className = 'fa-solid fa-circle-check text-emerald-500';
    if (text) {
      text.textContent = 'Passwords match';
      text.className = 'text-emerald-600';
    }
    confirmPwEl.classList.remove('border-rose-400');
    confirmPwEl.classList.add('border-emerald-400');
  } else {
    if (icon) icon.className = 'fa-solid fa-xmark text-rose-500';
    if (text) {
      text.textContent = 'Passwords do not match';
      text.className = 'text-rose-500';
    }
    confirmPwEl.classList.remove('border-emerald-400');
    confirmPwEl.classList.add('border-rose-400');
  }
}
