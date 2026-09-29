window.showToast = function(title, message) {
  const container = document.getElementById('toastContainer') || document.body;
  const toast = document.createElement('div');
  
  // Make sure it doesn't overlap if attached to body
  const positionClass = container === document.body ? 'fixed bottom-4 right-4 z-50 ' : '';
  
  toast.className = positionClass + "flex items-start gap-3 bg-slate-900 text-white border border-slate-800 shadow-xl rounded-xl p-4 min-w-[320px] max-w-sm transition-all duration-300 transform translate-y-2 opacity-0 mb-2";
  
  // Use escapeHtml if available, otherwise just use string
  const safeTitle = typeof window.escapeHtml === 'function' ? window.escapeHtml(title) : title;
  const safeMessage = typeof window.escapeHtml === 'function' ? window.escapeHtml(message) : message;

  toast.innerHTML = `
    <div class="h-6 w-6 shrink-0 rounded-lg bg-slate-800 flex items-center justify-center text-brand-medium">
      <i class="fa-solid fa-info text-[10px]"></i>
    </div>
    <div class="flex-1 space-y-0.5">
      <h4 class="text-xs font-bold text-white">${safeTitle}</h4>
      <p class="text-[10px] font-semibold text-slate-400 leading-relaxed">${safeMessage}</p>
    </div>
    <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white focus:outline-none cursor-pointer">
      <i class="fa-solid fa-xmark text-[10px]"></i>
    </button>
  `;
  
  container.appendChild(toast);
  
  setTimeout(() => {
    toast.classList.remove('translate-y-2', 'opacity-0');
    toast.classList.add('translate-y-0', 'opacity-100');
  }, 10);

  setTimeout(() => {
    toast.classList.remove('translate-y-0', 'opacity-100');
    toast.classList.add('translate-y-2', 'opacity-0');
    setTimeout(() => {
      toast.remove();
    }, 300);
  }, 4000);
};
