// CITIZEN ACCOUNT MODALS

function openModal(id) {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.classList.remove('opacity-0', 'pointer-events-none');
  modal.classList.add('opacity-100', 'pointer-events-auto');
  const card = modal.querySelector('.transform');
  if (card) {
    card.classList.remove('scale-95');
    card.classList.add('scale-100');
  }
}

function closeModal(id) {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.classList.remove('opacity-100', 'pointer-events-auto');
  modal.classList.add('opacity-0', 'pointer-events-none');
  const card = modal.querySelector('.transform');
  if (card) {
    card.classList.remove('scale-100');
    card.classList.add('scale-95');
  }
}

// C. AUDIT TRAIL TIMELINE MODAL DISPLAY
function openTimelineModal(citId) {
  const cit = ctrlCitizens.find(c => c.id === citId);
  if (!cit) return;

  const mName = document.getElementById('timelineCitizenName');
  const mId = document.getElementById('timelineCitizenId');
  if (mName) mName.innerText = cit.name;
  if (mId) mId.innerText = cit.id;

  const tContainer = document.getElementById('timelineContainer');
  if (tContainer) {
    tContainer.innerHTML = '';
    cit.timeline.forEach(log => {
      tContainer.innerHTML += `
        <div class="relative pl-5 pb-3.5">
          <div class="absolute -left-[21px] top-1.5 h-3.5 w-3.5 rounded-full border bg-white border-slate-300 flex items-center justify-center text-[7px] font-black text-slate-400">
            <i class="fa-solid fa-circle text-[5px]"></i>
          </div>
          <div class="font-extrabold text-slate-800 tracking-tight text-[11px]">${log.action}</div>
          <div class="text-[10px] text-slate-400 font-medium mt-0.5">${log.date} by <span class="text-slate-600 font-bold">${log.admin}</span></div>
          <p class="text-[11.5px] text-slate-500 mt-1.5 italic bg-slate-50 p-2 rounded-lg border border-slate-100">Reason: "${log.reason}"</p>
        </div>
      `;
    });
  }

  openModal('timelineModal');
}
