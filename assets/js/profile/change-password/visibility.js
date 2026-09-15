// Change Password Visibility
// Responsible for toggling the show/hide password text fields

// TOGGLE PASSWORD VISIBILITY
function toggleVisibility(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon  = document.getElementById(iconId);
  if (!input || !icon) return;

  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.replace('fa-eye', 'fa-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.replace('fa-eye-slash', 'fa-eye');
  }
}
