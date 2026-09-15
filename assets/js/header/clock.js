// Realtime Header Clock
function updateHeaderClock() {
  const clockEl = document.getElementById('headerClock');
  if (!clockEl) return;
  const now = new Date();
  const options = { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true };
  clockEl.textContent = now.toLocaleDateString('en-US', options);
}
