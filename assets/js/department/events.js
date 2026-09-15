// Setup Events
document.querySelectorAll('[id$="Modal"]').forEach(modal => {
  modal.addEventListener('mousedown', function(e) {
    if (e.target === this) closeModal(this.id);
  });
});
const deptSearchInput = document.getElementById('searchInput') || (typeof searchInput !== 'undefined' ? searchInput : null);
if (deptSearchInput) {
  deptSearchInput.addEventListener('input', filterDepts);
}
