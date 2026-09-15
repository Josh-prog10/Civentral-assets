// Live Clock Engine
    function updateClock() {
      const now = new Date();
      const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
      document.getElementById('liveClock').innerText = now.toLocaleDateString('en-US', options);
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Mobile Responsive Menu Drawer Trigger Controller
    function toggleMobileMenu() {
      const menu = document.getElementById('mobileMenu');
      const icon = document.getElementById('menuIcon');
      if (menu.classList.contains('hidden')) {
        menu.classList.remove('hidden');
        icon.className = 'fa-solid fa-xmark text-xl';
      } else {
        menu.classList.add('hidden');
        icon.className = 'fa-solid fa-bars text-xl';
      }
    }

    // Auto-Scrolling Slider Loop Mechanics
    let currentSlide = 0;
    const totalSlides = 2;
    function setSlide(index) {
      currentSlide = index;
      document.getElementById('carouselTrack').style.transform = `translateX(-${currentSlide * 100}%)`;
      const dots = document.querySelectorAll('.id-dot');
      dots.forEach((dot, i) => {
        dot.className = i === currentSlide ? "h-2 w-8 bg-white rounded-full transition-all duration-300 id-dot" : "h-2 w-2 bg-white/50 rounded-full transition-all duration-300 id-dot";
      });
    }
    setInterval(() => { setSlide((currentSlide + 1) % totalSlides); }, 5000);

    // Dynamic Bilingual Mapping System
    const languageMatrix = {
      en: {
        navTagline: "Caloocan City Portal",
        heroBadge: "Welcome to the Historic City of Caloocan",
        heroTitle: "Serving the Citizens with Excellence and Integrity",
        heroSubtitle: "Discover convenient digital public pathways, community updates, and localized transaction structures managed completely online.",
        heroBtnPrimary: "Explore Services",
        heroBtnSecondary: "Get Mobile App",
        leaderBadge: "City Leadership",
        leaderTitle: "The Local Government Executive Council",
        leaderSubtitle: "Guiding the sustainable development and growth of Caloocan through dedicated public service.",
        titleMayor: "City Mayor",
        titleViceMayor: "City Vice Mayor",
        titleCouncil: "Sangguniang Panlungsod",
        subtitleCouncil: "City Council Members",
        newsBadge: "Bulletin Board",
        newsTitle: "Active Circulars & Public Memos",
        servicesTitle: "Unified Department Public Services",
        appBadge: "Mobile Platform",
        appTitle: "Download Citizen Mobile App",
        appDesc: "Gain direct mobile access to local permit tracking fields, emergency notices, and service scheduling pipelines. Scan the security barcode vector to safely pull down the native Android installation package."
      },
      tl: {
        navTagline: "Lungsod ng Asenso",
        heroBadge: "Maligayang Pagdating sa Makasaysayang Lungsod ng Caloocan",
        heroTitle: "Paglilingkod sa Mamamayan nang may Kahusayan at Integridad",
        heroSubtitle: "Tuklasin ang mga maginhawang digital na serbisyong pampubliko, ulat ng komunidad, at mga transaksyong mabilis ma-access online.",
        heroBtnPrimary: "Tingnan ang Serbisyo",
        heroBtnSecondary: "I-download ang App",
        leaderBadge: "Pamunuan ng Lungsod",
        leaderTitle: "Ang Konseho Ehekutibo ng Pamahalaang Lokal",
        leaderSubtitle: "Pinupunuan ang maayos na pag-asenso at pag-unlad ng Caloocan sa pamamagitan ng tapat na serbisyo.",
        titleMayor: "Alkalde ng Lungsod",
        titleViceMayor: "Bise Alkalde ng Lungsod",
        titleCouncil: "Sangguniang Panlungsod",
        subtitleCouncil: "Mga Miyembro ng Konseho",
        newsBadge: "Pampublikong Pisara",
        newsTitle: "Mga Aktibong Kautusan at Memorandum",
        servicesTitle: "Pinag-isang mga Serbisyo ng Kagawaran",
        appBadge: "Platapormang Mobile",
        appTitle: "I-download ang Mobile App ng Mamamayan",
        appDesc: "Kumuha ng agarang access sa pagsubaybay ng permit, mga emergency advisory, at pag-iskedyul ng mga serbisyo. I-scan ang code upang ligtas na mai-configure ang Android application package."
      }
    };

    function changeLanguage(lang) {
      const translation = languageMatrix[lang];
      for (const key in translation) {
        const element = document.getElementById(key);
        if (element) element.innerText = translation[key];
      }
      document.getElementById('lang-en').className = lang === 'en' ? 'text-white hover:text-brand-medium transition cursor-pointer' : 'text-slate-400 hover:text-brand-medium transition cursor-pointer';
      document.getElementById('lang-tl').className = lang === 'tl' ? 'text-white hover:text-brand-medium transition cursor-pointer' : 'text-slate-400 hover:text-brand-medium transition cursor-pointer';
    }