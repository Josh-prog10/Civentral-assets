// Settings Avatar
// Responsible for handling profile picture uploads, previews, and removals

function previewAvatar(event) {
  const file = event.target.files[0];
  if (!file) return;

  if (file.size > 2 * 1024 * 1024) {
    showToast('File too large! Maximum size is 2MB.', 'error');
    event.target.value = '';
    return;
  }

  if (!['image/jpeg', 'image/png'].includes(file.type)) {
    showToast('Invalid format. Please use JPG or PNG.', 'error');
    event.target.value = '';
    return;
  }

  const reader = new FileReader();
  reader.onload = function(e) {
    currentAvatarBase64 = e.target.result;
    const avatarImg = document.getElementById('avatarPreview');
    if (avatarImg) avatarImg.src = currentAvatarBase64;
    showToast('Avatar preview updated! Click Save Settings to apply.', 'info');
  };
  reader.readAsDataURL(file);
}

function removePhoto() {
  currentAvatarBase64 = 'default-avatar.png';
  const defaultAvatar = `https://ui-avatars.com/api/?name=${encodeURIComponent(userProfile ? userProfile.full_name : 'User')}&background=EEF5FF&color=176B87&bold=true&size=128&font-size=0.42`;
  const avatarImg = document.getElementById('avatarPreview');
  if (avatarImg) avatarImg.src = defaultAvatar;
  document.getElementById('avatarFileInput').value = '';
  showToast('Profile photo set to default. Click Save Settings to apply.', 'info');
}
