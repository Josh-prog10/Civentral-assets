// Change Password Strength
// Responsible for calculating and rendering the password strength meter

function checkPasswordStrength() {
  const inputEl = document.getElementById('newPassword');
  if (!inputEl) return;

  const val = inputEl.value;
  let score = 0;

  rules.forEach(function(rule) {
    const met = rule.regex.test(val);
    const iconEl = document.getElementById('icon-' + rule.id);
    const ruleEl = document.getElementById('rule-' + rule.id);

    if (!iconEl || !ruleEl) return;

    if (met) {
      score++;
      iconEl.className = 'h-4 w-4 rounded-full bg-emerald-500 flex items-center justify-center shrink-0 transition-all duration-200';
      iconEl.innerHTML = '<i class="fa-solid fa-check text-[7px] text-white"></i>';
      ruleEl.classList.remove('text-slate-400', 'text-rose-500');
      ruleEl.classList.add('text-emerald-600');
    } else if (val.length > 0) {
      iconEl.className = 'h-4 w-4 rounded-full bg-rose-100 flex items-center justify-center shrink-0 transition-all duration-200';
      iconEl.innerHTML = '<i class="fa-solid fa-xmark text-[7px] text-rose-500"></i>';
      ruleEl.classList.remove('text-slate-400', 'text-emerald-600');
      ruleEl.classList.add('text-rose-500');
    } else {
      iconEl.className = 'h-4 w-4 rounded-full bg-slate-200 flex items-center justify-center shrink-0 transition-all duration-200';
      iconEl.innerHTML = '<i class="fa-solid fa-minus text-[7px] text-slate-400"></i>';
      ruleEl.classList.remove('text-emerald-600', 'text-rose-500');
      ruleEl.classList.add('text-slate-400');
    }
  });

  updateStrengthBar(score, val.length);
  checkConfirmMatch();
}

const barColors = ['bg-rose-400', 'bg-orange-400', 'bg-amber-400', 'bg-lime-500', 'bg-emerald-500'];
const barLabels = ['Very Weak', 'Weak', 'Fair', 'Strong', 'Very Strong'];
const labelColors = ['text-rose-500', 'text-orange-500', 'text-amber-500', 'text-lime-600', 'text-emerald-600'];

function updateStrengthBar(score, length) {
  const label = document.getElementById('strengthLabel');

  for (let i = 1; i <= 5; i++) {
    const bar = document.getElementById('strengthBar' + i);
    if (bar) {
      bar.className = 'h-1 flex-1 rounded-full transition-all duration-300 ' + (i <= score ? barColors[score - 1] : 'bg-slate-100');
    }
  }

  if (label) {
    if (length === 0) {
      label.textContent = 'Enter a password to evaluate strength';
      label.className = 'text-[10px] font-bold text-slate-300 leading-none tracking-wide';
    } else {
      label.textContent = barLabels[score - 1] || 'Too Weak';
      label.className = 'text-[10px] font-bold leading-none tracking-wide ' + (labelColors[score - 1] || 'text-rose-500');
    }
  }
}
