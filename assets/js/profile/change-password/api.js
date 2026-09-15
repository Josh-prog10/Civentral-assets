
// Responsible for submitting the new password to the server

let userProfileData = null;

// FETCH LOGGED IN USER DATA
async function fetchUserAccountProfile() {
  const result = await civentralFetchProfile('../../api/employee/profile.php');
  if (result.status === 'success' && result.data) {
    userProfileData = result.data;
    
    const defaultAvatar = `https://ui-avatars.com/api/?name=${encodeURIComponent(userProfileData.full_name || 'User')}&background=EEF5FF&color=176B87&bold=true&size=128`;
    const avatarEl = document.getElementById('pwAccountAvatar');
    if (avatarEl) {
      let pic = userProfileData.profile_picture || 'default-avatar.png';
      if (pic !== 'default-avatar.png' && !pic.startsWith('http') && !pic.startsWith('data:')) {
        const bPath = window.civentralBasePath || '../../';
        pic = bPath + pic.replace(/^\/+/, '');
      } else if (pic === 'default-avatar.png') {
        pic = defaultAvatar;
      }
      avatarEl.src = pic;
    }

    const nameEl = document.getElementById('pwAccountName');
    if (nameEl) nameEl.innerText = userProfileData.full_name || 'System User';

    const roleEl = document.getElementById('pwAccountRole');
    if (roleEl) roleEl.innerText = userProfileData.role_name || 'Staff';

    const emailEl = document.getElementById('pwAccountEmail');
    if (emailEl) emailEl.innerText = userProfileData.email || '';

    const deptEl = document.getElementById('pwAccountDept');
    if (deptEl) deptEl.innerText = userProfileData.department_name || 'General Office';
  }
}

// SAVE PASSWORD 
async function savePassword() {
  const currentInput = document.getElementById('currentPassword');
  const newPwInput = document.getElementById('newPassword');
  const confirmInput = document.getElementById('confirmPassword');
  const btn = document.getElementById('savePasswordBtn');

  const current = currentInput ? currentInput.value.trim() : '';
  const newPw = newPwInput ? newPwInput.value : '';
  const confirm = confirmInput ? confirmInput.value : '';

  if (!current) {
    showToast('Please enter your current password.', 'error');
    if (currentInput) currentInput.focus();
    return;
  }

  const allMet = rules.every(function(rule) { return rule.regex.test(newPw); });
  if (!allMet) {
    showToast('New password does not meet all requirements.', 'error', 'Check the security checklist below.');
    return;
  }

  if (newPw !== confirm) {
    showToast('Passwords do not match.', 'error', 'Please verify the Confirm Password field.');
    if (confirmInput) confirmInput.focus();
    return;
  }

  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin text-[10px]"></i> Saving...';
  }

  try {
    const response = await fetch('../../api/employee/change-password.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        current_password: current,
        new_password: newPw
      })
    });

    const result = await response.json();

    if (btn) {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-floppy-disk text-[10px]"></i> Update Password';
    }

    if (result.status === 'success') {
      if (currentInput) currentInput.value = '';
      if (newPwInput) newPwInput.value = '';
      if (confirmInput) confirmInput.value = '';
      
      checkPasswordStrength();
      checkConfirmMatch();

      showToast(result.message || 'Password updated successfully!', 'success', 'Your credentials have been changed IN DATABASE.');
    } else {
      showToast(result.message || 'Failed to update password.', 'error');
    }
  } catch (err) {
    console.error('Error changing password IN DATABASE:', err);
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-floppy-disk text-[10px]"></i> Update Password';
    }
    showToast('Network error saving new password TO DATABASE.', 'error');
  }
}


