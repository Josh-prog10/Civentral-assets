// Settings API
// Responsible for all AJAX/fetch requests related to user settings

let userProfile = null;
let currentAvatarBase64 = null;

async function fetchProfileData() {
  const result = await civentralFetchProfile('../../api/employee/profile.php');
  if (result.status === 'success' && result.data) {
    userProfile = result.data;
    populateProfileUI();
  } else {
    showToast(result.message || 'Error loading account settings.', 'error');
  }
}

async function saveSettings() {
  const btn = document.getElementById('saveSettingsBtn');
  const phoneVal = document.getElementById('fieldPhone') ? document.getElementById('fieldPhone').value.trim() : '';
  const emailVal = document.getElementById('fieldEmail') ? document.getElementById('fieldEmail').value.trim() : '';
  const posVal = document.getElementById('fieldPosition') ? document.getElementById('fieldPosition').value.trim() : '';

  if (!emailVal) {
    showToast('Email address is required.', 'error');
    return;
  }

  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin text-[10px]"></i> Saving...';
  }

  const payload = {
    first_name: userProfile ? (userProfile.first_name || '') : '',
    last_name: userProfile ? (userProfile.last_name || '') : '',
    mobile_number: phoneVal,
    email: emailVal,
    position_name: posVal
  };

  if (currentAvatarBase64) {
    payload.profile_picture = currentAvatarBase64;
  }

  try {
    const response = await fetch('../../api/employee/profile.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const result = await response.json();

    if (btn) {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-floppy-disk text-[10px]"></i> Save Settings';
    }

    if (result.status === 'success') {
      showToast('Settings updated successfully!', 'success', 'Your profile changes have been saved TO DATABASE.');
      fetchProfileData();
    } else {
      showToast(result.message || 'Error updating settings.', 'error');
    }
  } catch (err) {
    console.error('Error saving settings TO DATABASE:', err);
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-floppy-disk text-[10px]"></i> Save Settings';
    }
    showToast('Network error saving settings TO DATABASE.', 'error');
  }
}
