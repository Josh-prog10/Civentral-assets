<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Civentral</title>
  <link rel="icon" type="image/png" href="assets/images/logo.png">
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <!--  Palette -->
  <style type="text/tailwindcss">
    @theme {
      --color-brand-light: #EEF5FF;
      --color-brand-border: #B4D4FF;
      --color-brand-medium: #86B6F6;
      --color-brand-dark: #176B87;
    }
  </style>
  <!-- fonts -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-800">

  <!-- Real-time clock shit -->
  <div class="bg-slate-900 text-white text-xs px-4 sm:px-6 py-2.5 flex flex-col sm:flex-row items-center justify-between border-b border-slate-800 gap-2">
    <div class="flex items-center space-x-2 font-mono text-slate-300 text-center sm:text-left">
      <i class="fa-solid fa-calendar-day text-brand-medium"></i>
      <span id="liveClock">Loading Date & Time...</span>
    </div>
    <div class="flex items-center space-x-1 text-[11px] font-bold tracking-wider">
      <button onclick="changeLanguage('en')" id="lang-en" class="text-white hover:text-brand-medium transition cursor-pointer">ENGLISH</button>
      <span class="text-slate-600">|</span>
      <button onclick="changeLanguage('tl')" id="lang-tl" class="text-slate-400 hover:text-brand-medium transition cursor-pointer">TAGALOG</button>
    </div>
  </div>

  <!-- Header -->
  <nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-brand-border px-4 sm:px-6 py-4">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <img src="assets/images/logo.png" alt="Civentral Logo" class="h-10 w-auto object-contain">
        <div class="flex flex-col">
          <span class="text-xl font-black text-brand-dark tracking-wider uppercase leading-none">CIVENTRAL</span>
          <span class="text-[10px] font-bold text-brand-medium tracking-widest uppercase mt-0.5" id="navTagline">Caloocan City Portal</span>
        </div>
      </div>
      
      <!-- For desktop menu -->
      <div class="hidden lg:flex items-center space-x-6">
        <a href="#city-showcase" class="text-sm font-semibold text-slate-600 hover:text-brand-dark transition">Home</a>
        <a href="#leadership" class="text-sm font-semibold text-slate-600 hover:text-brand-dark transition">Officials</a>
        <a href="#features" class="text-sm font-semibold text-slate-600 hover:text-brand-dark transition">Services</a>
        <a href="#announcements" class="text-sm font-semibold text-slate-600 hover:text-brand-dark transition">News & Events</a>
        <a href="#download-app" class="text-sm font-semibold text-slate-600 hover:text-brand-dark transition">Mobile App</a>
      </div>

      <div class="flex items-center space-x-2">
        <a href="login.php" class="hidden lg:inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-brand-dark border border-brand-medium bg-brand-light hover:bg-brand-medium hover:text-white rounded-lg shadow-xs transition">
          Employee Portal <i class="fa-solid fa-arrow-right-to-bracket ml-2 text-xs"></i>
        </a>
        <!-- Mobile -->
        <button onclick="toggleMobileMenu()" class="lg:hidden text-slate-600 hover:text-brand-dark p-2 focus:outline-none cursor-pointer">
          <i id="menuIcon" class="fa-solid fa-bars text-xl"></i>
        </button>
      </div>
    </div>

    <!-- Dropdown Mobile Menu -->
    <div id="mobileMenu" class="hidden lg:hidden border-t border-slate-100 mt-4 pt-4 flex flex-col space-y-3 px-2">
      <a href="#city-showcase" onclick="toggleMobileMenu()" class="text-sm font-semibold text-slate-600 hover:text-brand-dark py-1">Home</a>
      <a href="#leadership" onclick="toggleMobileMenu()" class="text-sm font-semibold text-slate-600 hover:text-brand-dark py-1">Officials</a>
      <a href="#features" onclick="toggleMobileMenu()" class="text-sm font-semibold text-slate-600 hover:text-brand-dark py-1">Services</a>
      <a href="#announcements" onclick="toggleMobileMenu()" class="text-sm font-semibold text-slate-600 hover:text-brand-dark py-1">News & Events</a>
      <a href="#download-app" onclick="toggleMobileMenu()" class="text-sm font-semibold text-slate-600 hover:text-brand-dark py-1 mb-2">Mobile App</a>
      <a href="login.php" class="w-full text-center py-2.5 text-sm font-semibold text-brand-dark border border-brand-medium bg-brand-light rounded-lg">
        Employee Portal <i class="fa-solid fa-arrow-right-to-bracket ml-1 text-xs"></i>
      </a>
    </div>
  </nav>

  <!-- HERO SEC FOR  -->
  <header id="city-showcase" class="relative bg-slate-900 h-[400px] sm:h-[460px] md:h-[560px] overflow-hidden">
    <div class="absolute inset-0 flex transition-transform duration-1000 ease-in-out" id="carouselTrack">
      <div class="w-full h-full shrink-0 bg-cover bg-center relative bg-[url('assets/images/main-building.jpg')] bg-slate-800">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/40 to-transparent"></div>
      </div>
      <div class="w-full h-full shrink-0 bg-cover bg-center relative bg-[url('assets/images/park.jpg')] bg-slate-700">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/40 to-transparent"></div>
      </div>
    </div>

    <div class="absolute inset-0 z-10 flex items-center justify-center px-6 text-center">
      <div class="max-w-4xl space-y-4 sm:space-y-6">
        <span class="text-[10px] sm:text-xs font-bold tracking-[0.2em] sm:tracking-[0.3em] uppercase text-brand-medium bg-white/10 backdrop-blur-xs px-3 sm:px-4 py-1.5 rounded-full border border-white/20" id="heroBadge">
          Welcome to the Historic City of Caloocan
        </span>
        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-tight sm:leading-none" id="heroTitle">
          Serving the Citizens with Excellence and Integrity
        </h1>
        <p class="text-xs sm:text-base text-slate-300 max-w-2xl mx-auto leading-relaxed" id="heroSubtitle">
          Discover convenient digital public pathways, community updates, and localized transaction structures managed completely online.
        </p>
        <div class="pt-2 sm:pt-4 flex flex-wrap justify-center gap-3">
          <a href="#features" class="px-4 sm:px-5 py-2.5 sm:py-3 bg-brand-medium text-white font-semibold rounded-lg text-xs sm:text-sm transition shadow-md hover:bg-opacity-90" id="heroBtnPrimary">Explore Services</a>
          <a href="#download-app" class="px-4 sm:px-5 py-2.5 sm:py-3 bg-white/10 hover:bg-white/20 text-white font-semibold border border-white/30 rounded-lg text-xs sm:text-sm transition" id="heroBtnSecondary">Get Mobile App</a>
        </div>
      </div>
    </div>

    <div class="absolute bottom-6 left-0 right-0 z-20 flex justify-center space-x-2">
      <button onclick="setSlide(0)" class="h-2 w-8 bg-white rounded-full transition-all duration-300 id-dot cursor-pointer"></button>
      <button onclick="setSlide(1)" class="h-2 w-2 bg-white/50 rounded-full transition-all duration-300 id-dot cursor-pointer"></button>
    </div>
  </header>

  <!-- Leader -->
  <section id="leadership" class="py-16 sm:py-24 px-4 sm:px-6 max-w-7xl mx-auto space-y-12">
    <div class="text-center space-y-3 max-w-2xl mx-auto">
      <span class="text-xs font-bold uppercase tracking-wider text-brand-medium" id="leaderBadge">City Leadership</span>
      <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight" id="leaderTitle">The Local Government Executive Council</h2>
      <p class="text-xs sm:text-sm text-slate-500" id="leaderSubtitle">
        Guiding the sustainable development and growth of Caloocan through dedicated public service.
      </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
      <!-- Mayor -->
      <div class="bg-white border border-slate-200 rounded-2xl p-6 text-center space-y-4 shadow-xs">
        <div class="h-28 w-28 rounded-full border-2 border-brand-medium mx-auto overflow-hidden bg-slate-100">
          <img src="assets/images/mayor.jpg" alt="Mayor Along Malapitan" class="w-full h-full object-cover object-top">
        </div>
        <div>
          <h4 class="font-extrabold text-slate-900 text-base">Hon. Dale Gonzalo "Along" Malapitan</h4>
          <p class="text-xs font-bold text-brand-dark uppercase tracking-widest mt-0.5" id="titleMayor">City Mayor</p>
        </div>
      </div>
      <!-- Vice Mayor -->
      <div class="bg-white border border-slate-200 rounded-2xl p-6 text-center space-y-4 shadow-xs">
        <div class="h-28 w-28 rounded-full border-2 border-brand-medium mx-auto overflow-hidden bg-slate-100">
          <img src="assets/images/Vice.jpg" alt="Vice Mayor Karina Teh" class="w-full h-full object-cover object-top">
        </div>
        <div>
          <h4 class="font-extrabold text-slate-900 text-base">Hon. Karina Teh-Limsico</h4>
          <p class="text-xs font-bold text-brand-dark uppercase tracking-widest mt-0.5" id="titleViceMayor">City Vice Mayor</p>
        </div>
      </div>
      <!-- Sangguniang Panlungsod wala ako malagay -->
      <div class="bg-white border border-slate-200 rounded-2xl p-6 text-center space-y-4 shadow-xs">
        <div class="h-24 w-24 rounded-full bg-brand-light border-2 border-brand-medium mx-auto overflow-hidden flex items-center justify-center text-brand-dark">
          <i class="fa-solid fa-users text-3xl"></i>
        </div>
        <div>
          <h4 class="font-extrabold text-slate-900 text-sm sm:text-base" id="titleCouncil">Sangguniang Panlungsod</h4>
          <p class="text-[11px] font-bold text-brand-dark uppercase tracking-widest mt-0.5" id="subtitleCouncil">City Council Members</p>
        </div>
      </div>
    </div>

    <!-- All Officials -->
    <div class="text-center pt-2">
      <a href="officials.html" class="inline-flex items-center space-x-2 text-xs font-bold text-brand-dark bg-white border border-brand-border px-5 py-2.5 rounded-lg shadow-xs hover:bg-brand-light transition">
        <span>View Full Organizational Directory</span>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
      </a>
    </div>
  </section>

  <!-- Announcements Section (Simple Picture Top, Caption Bottom) -->
  <section id="announcements" class="py-16 sm:py-24 bg-slate-100/80 border-y border-slate-200/80 px-4 sm:px-6">
    <div class="max-w-7xl mx-auto space-y-10 sm:space-y-12">
      <!-- Section Header -->
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200/60 pb-6">
        <div class="space-y-2">
          <span class="text-xs font-bold uppercase tracking-widest text-brand-dark bg-white border border-brand-border/60 px-3.5 py-1 rounded-full shadow-2xs" id="newsBadge">Bulletin Board</span>
          <h2 class="text-2xl sm:text-4xl font-black text-slate-900 tracking-tight" id="newsTitle">Active Circulars & Public Memos</h2>
          <p class="text-xs sm:text-sm text-slate-500 max-w-2xl leading-relaxed">
            Official municipal announcements, weather advisories, emergency alerts, tax payment deadlines, and public notices.
          </p>
        </div>
        <div class="shrink-0">
          <span class="inline-flex items-center gap-2 text-xs font-extrabold text-slate-700 bg-white border border-slate-200 px-4 py-2 rounded-xl shadow-2xs">
            <span class="relative flex h-2.5 w-2.5">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
            </span>
            Live LGU Announcements
          </span>
        </div>
      </div>

      <!-- Announcements Cards Grid (Picture Top, Caption Bottom) -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        
        <!-- Announcement 1: Weather Advisory & Class Suspension -->
        <div class="bg-white border border-slate-200/90 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col group">
          <!-- Picture (Top) -->
          <div class="relative overflow-hidden bg-slate-900 aspect-video">
            <img src="assets/images/weather_advisory.png" alt="Weather Advisory Announcement" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            <span class="absolute top-3 left-3 bg-red-600 text-white text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-md shadow-md animate-pulse">
              <i class="fa-solid fa-triangle-exclamation mr-1"></i> Weather Advisory
            </span>
          </div>

          <!-- Caption (Bottom) -->
          <div class="p-6 space-y-3 flex-1 flex flex-col justify-between">
            <div class="space-y-2">
              <div class="flex items-center justify-between text-[11px] text-slate-400 font-semibold">
                <span class="text-red-600 font-bold flex items-center gap-1"><i class="fa-solid fa-cloud-showers-heavy"></i> Heavy Rainfall Warning</span>
                <span>Jul 26, 2026</span>
              </div>
              <h3 class="font-extrabold text-slate-900 text-base leading-snug group-hover:text-red-600 transition-colors">
                Suspension of Classes Due to Heavy Rains & Monsoon Weather
              </h3>
              <p class="text-xs text-slate-500 leading-relaxed">
                Classes in ALL LEVELS (Public & Private) in Caloocan City are suspended today due to heavy rainfall and thunderstorm advisories. Stay safe indoors and monitor official LGU channels.
              </p>
            </div>
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-red-600">
              <span>Emergency Hotline: 911</span>
              <i class="fa-solid fa-phone text-xs"></i>
            </div>
          </div>
        </div>

        <!-- Announcement 2: Business Permit Renewal -->
        <div class="bg-white border border-slate-200/90 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col group">
          <!-- Picture (Top) -->
          <div class="relative overflow-hidden bg-slate-900 aspect-video">
            <img src="assets/images/bplo_advisory.png" alt="Business Permit Renewal Announcement" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            <span class="absolute top-3 left-3 bg-blue-600 text-white text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-md shadow-md">
              <i class="fa-solid fa-file-invoice-dollar mr-1"></i> BPLO Tax Notice
            </span>
          </div>

          <!-- Caption (Bottom) -->
          <div class="p-6 space-y-3 flex-1 flex flex-col justify-between">
            <div class="space-y-2">
              <div class="flex items-center justify-between text-[11px] text-slate-400 font-semibold">
                <span class="text-blue-600 font-bold flex items-center gap-1"><i class="fa-solid fa-briefcase"></i> Business Permits</span>
                <span>Jul 25, 2026</span>
              </div>
              <h3 class="font-extrabold text-slate-900 text-base leading-snug group-hover:text-blue-600 transition-colors">
                2026 Business Permit Renewal & Tax Clearance Schedule
              </h3>
              <p class="text-xs text-slate-500 leading-relaxed">
                Online business permit renewal and tax assessment filing is open via the Civentral portal. File early to avail of early-bird municipal tax discounts.
              </p>
            </div>
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-blue-600">
              <span>View Online Clearance</span>
              <i class="fa-solid fa-arrow-right text-xs"></i>
            </div>
          </div>
        </div>

        <!-- Announcement 3: AICS Social Assistance Payout -->
        <div class="bg-white border border-slate-200/90 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col group">
          <!-- Picture (Top) -->
          <div class="relative overflow-hidden bg-slate-900 aspect-video">
            <img src="assets/images/aics_advisory.png" alt="AICS Aid Announcement" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            <span class="absolute top-3 left-3 bg-emerald-600 text-white text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-md shadow-md">
              <i class="fa-solid fa-hand-holding-dollar mr-1"></i> Social Welfare
            </span>
          </div>

          <!-- Caption (Bottom) -->
          <div class="p-6 space-y-3 flex-1 flex flex-col justify-between">
            <div class="space-y-2">
              <div class="flex items-center justify-between text-[11px] text-slate-400 font-semibold">
                <span class="text-emerald-600 font-bold flex items-center gap-1"><i class="fa-solid fa-heart"></i> CSWDO Aid</span>
                <span>Jul 24, 2026</span>
              </div>
              <h3 class="font-extrabold text-slate-900 text-base leading-snug group-hover:text-emerald-600 transition-colors">
                AICS Educational & Medical Financial Aid Distribution
              </h3>
              <p class="text-xs text-slate-500 leading-relaxed">
                Verified beneficiary payout schedules for District 1, 2, and 3 residents are active. Check your portal profile for schedule slots and claim details.
              </p>
            </div>
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-600">
              <span>View Payout Venue</span>
              <i class="fa-solid fa-arrow-right text-xs"></i>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- Services -->
  <section id="features" class="py-16 sm:py-24 px-4 sm:px-6 max-w-7xl mx-auto space-y-12">
    <div class="text-center space-y-3 max-w-2xl mx-auto">
      <span class="text-xs font-bold uppercase tracking-widest text-brand-dark bg-brand-light px-3.5 py-1 rounded-full border border-brand-border/60">Digital Municipal Services</span>
      <h2 class="text-2xl sm:text-4xl font-black text-slate-900 tracking-tight" id="servicesTitle">Unified Department Public Services</h2>
      <p class="text-xs sm:text-sm text-slate-500 max-w-xl mx-auto leading-relaxed">
        Integrated municipal digital pathways providing transparent service accessibility, online tracking, and streamlined departmental processing for all Caloocan residents.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <!-- 1. Citizen Identity Registry -->
      <div class="bg-white border border-slate-200/80 hover:border-brand-medium/60 rounded-2xl p-6 flex flex-col justify-between space-y-4 shadow-xs hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300">
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <div class="h-10 w-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shadow-2xs">
              <i class="fa-solid fa-id-card text-sm"></i>
            </div>
            <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200/60">Civil Registry</span>
          </div>
          <h4 class="font-extrabold text-slate-900 text-base">Citizen Identity Registry</h4>
          <p class="text-xs text-slate-500 leading-relaxed">Population profiling setups, official civic information master databases, and digital residency verification.</p>
        </div>
        <div class="pt-2 border-t border-slate-100 flex flex-wrap gap-1.5">
          <span class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">Digital ID</span>
          <span class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">Instant Verification</span>
        </div>
      </div>

      <!-- 2. Permit Clearance Controls -->
      <div class="bg-white border border-slate-200/80 hover:border-emerald-500/60 rounded-2xl p-6 flex flex-col justify-between space-y-4 shadow-xs hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300">
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <div class="h-10 w-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shadow-2xs">
              <i class="fa-solid fa-file-signature text-sm"></i>
            </div>
            <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60">BPLO & Permits</span>
          </div>
          <h4 class="font-extrabold text-slate-900 text-base">Permit Clearance Controls</h4>
          <p class="text-xs text-slate-500 leading-relaxed">Online assessment processing tracks for structural development and commercial business licensing.</p>
        </div>
        <div class="pt-2 border-t border-slate-100 flex flex-wrap gap-1.5">
          <span class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">Business Permit</span>
          <span class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">Clearance Filing</span>
        </div>
      </div>

      <!-- 3. Social Welfare System -->
      <div class="bg-white border border-slate-200/80 hover:border-amber-500/60 rounded-2xl p-6 flex flex-col justify-between space-y-4 shadow-xs hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300">
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <div class="h-10 w-10 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shadow-2xs">
              <i class="fa-solid fa-hand-holding-heart text-sm"></i>
            </div>
            <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-full bg-amber-50 text-amber-800 border border-amber-200/60">CSWDO Aid</span>
          </div>
          <h4 class="font-extrabold text-slate-900 text-base">Social Welfare System</h4>
          <p class="text-xs text-slate-500 leading-relaxed">AICS financial assistance coordination paths alongside senior citizen & PWD support services management.</p>
        </div>
        <div class="pt-2 border-t border-slate-100 flex flex-wrap gap-1.5">
          <span class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">AICS Assistance</span>
          <span class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">Senior & PWD Aid</span>
        </div>
      </div>

      <!-- 4. Health & Sanitation Management -->
      <div class="bg-white border border-slate-200/80 hover:border-rose-500/60 rounded-2xl p-6 flex flex-col justify-between space-y-4 shadow-xs hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300">
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <div class="h-10 w-10 rounded-xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 shadow-2xs">
              <i class="fa-solid fa-heart-pulse text-sm"></i>
            </div>
            <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-200/60">City Health</span>
          </div>
          <h4 class="font-extrabold text-slate-900 text-base">Health & Sanitation</h4>
          <p class="text-xs text-slate-500 leading-relaxed">Municipal health center documentation networks and sanitary inspection clearance registry systems.</p>
        </div>
        <div class="pt-2 border-t border-slate-100 flex flex-wrap gap-1.5">
          <span class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">Health Permits</span>
          <span class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">Sanitary Clearance</span>
        </div>
      </div>

      <!-- 5. Education & Scholarships -->
      <div class="bg-white border border-slate-200/80 hover:border-indigo-500/60 rounded-2xl p-6 flex flex-col justify-between space-y-4 shadow-xs hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300">
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <div class="h-10 w-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shadow-2xs">
              <i class="fa-solid fa-graduation-cap text-sm"></i>
            </div>
            <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200/60">Scholarships</span>
          </div>
          <h4 class="font-extrabold text-slate-900 text-base">Education & Scholarships</h4>
          <p class="text-xs text-slate-500 leading-relaxed">Application verification portals for students and institutional support asset allocation metrics.</p>
        </div>
        <div class="pt-2 border-t border-slate-100 flex flex-wrap gap-1.5">
          <span class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">Academic Aid</span>
          <span class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">Student Grants</span>
        </div>
      </div>

      <!-- 6. Disaster Risk Reduction (DRRM) -->
      <div class="bg-white border border-slate-200/80 hover:border-red-500/60 rounded-2xl p-6 flex flex-col justify-between space-y-4 shadow-xs hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300">
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <div class="h-10 w-10 rounded-xl bg-red-50 border border-red-100 flex items-center justify-center text-red-600 shadow-2xs">
              <i class="fa-solid fa-shield-halved text-sm"></i>
            </div>
            <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-full bg-red-50 text-red-700 border border-red-200/60">DRRMO Command</span>
          </div>
          <h4 class="font-extrabold text-slate-900 text-base">Disaster Risk Reduction</h4>
          <p class="text-xs text-slate-500 leading-relaxed">Real-time emergency broadcast notification paths, weather updates, and hazard layout registers.</p>
        </div>
        <div class="pt-2 border-t border-slate-100 flex flex-wrap gap-1.5">
          <span class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">911 Emergency</span>
          <span class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">Hazard Alerts</span>
        </div>
      </div>

      <!-- 7. Urban Planning & Zoning -->
      <div class="bg-white border border-slate-200/80 hover:border-cyan-500/60 rounded-2xl p-6 flex flex-col justify-between space-y-4 shadow-xs hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300">
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <div class="h-10 w-10 rounded-xl bg-cyan-50 border border-cyan-100 flex items-center justify-center text-cyan-700 shadow-2xs">
              <i class="fa-solid fa-map-location-dot text-sm"></i>
            </div>
            <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-full bg-cyan-50 text-cyan-800 border border-cyan-200/60">Zoning & CPDO</span>
          </div>
          <h4 class="font-extrabold text-slate-900 text-base">Urban Planning & Zoning</h4>
          <p class="text-xs text-slate-500 leading-relaxed">Zoning assessment structural matrices and infrastructure project development coordination.</p>
        </div>
        <div class="pt-2 border-t border-slate-100 flex flex-wrap gap-1.5">
          <span class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">Locational Clearance</span>
          <span class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">Site Inspection</span>
        </div>
      </div>

      <!-- 8. Treasury & Revenue Processing -->
      <div class="bg-white border border-slate-200/80 hover:border-teal-500/60 rounded-2xl p-6 flex flex-col justify-between space-y-4 shadow-xs hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300">
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <div class="h-10 w-10 rounded-xl bg-teal-50 border border-teal-100 flex items-center justify-center text-teal-700 shadow-2xs">
              <i class="fa-solid fa-cash-register text-sm"></i>
            </div>
            <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-full bg-teal-50 text-teal-800 border border-teal-200/60">City Treasury</span>
          </div>
          <h4 class="font-extrabold text-slate-900 text-base">Treasury & Revenue</h4>
          <p class="text-xs text-slate-500 leading-relaxed">Digital bookkeeping for tax collection loops, real property tax, and electronic fee processing.</p>
        </div>
        <div class="pt-2 border-t border-slate-100 flex flex-wrap gap-1.5">
          <span class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">Real Property Tax</span>
          <span class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">Online Payments</span>
        </div>
      </div>

      <!-- 9. Transport & Mobility Control -->
      <div class="bg-white border border-slate-200/80 hover:border-purple-500/60 rounded-2xl p-6 flex flex-col justify-between space-y-4 shadow-xs hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300">
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <div class="h-10 w-10 rounded-xl bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-700 shadow-2xs">
              <i class="fa-solid fa-bus text-sm"></i>
            </div>
            <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-full bg-purple-50 text-purple-800 border border-purple-200/60">Traffic & CPO</span>
          </div>
          <h4 class="font-extrabold text-slate-900 text-base">Transport & Mobility</h4>
          <p class="text-xs text-slate-500 leading-relaxed">Tricycle franchise database registries and municipal traffic regulation tracking structures.</p>
        </div>
        <div class="pt-2 border-t border-slate-100 flex flex-wrap gap-1.5">
          <span class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">Tricycle Franchise</span>
          <span class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">Traffic Memos</span>
        </div>
      </div>

      <!-- 10. Public Asset Management -->
      <div class="bg-white border border-slate-200/80 hover:border-sky-500/60 rounded-2xl p-6 flex flex-col justify-between space-y-4 shadow-xs hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300">
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <div class="h-10 w-10 rounded-xl bg-sky-50 border border-sky-100 flex items-center justify-center text-sky-700 shadow-2xs">
              <i class="fa-solid fa-warehouse text-sm"></i>
            </div>
            <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-full bg-sky-50 text-sky-800 border border-sky-200/60">Public Assets</span>
          </div>
          <h4 class="font-extrabold text-slate-900 text-base">Public Asset Management</h4>
          <p class="text-xs text-slate-500 leading-relaxed">Facility configuration reservation grids and municipal infrastructure inventory tracker setups.</p>
        </div>
        <div class="pt-2 border-t border-slate-100 flex flex-wrap gap-1.5">
          <span class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">Venue Reservation</span>
          <span class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">Public Grounds</span>
        </div>
      </div>
    </div>
  </section>

  <!-- Mobile app showcase -->
  <section id="download-app" class="py-12 bg-slate-50 px-4 sm:px-6">
    <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
      <div class="md:col-span-8 space-y-3 text-center md:text-left">
        <span class="text-xs font-bold uppercase tracking-wider text-brand-dark bg-brand-light px-3 py-1 rounded-md" id="appBadge">Mobile Platform</span>
        <h3 class="text-2xl font-black text-slate-900 tracking-tight" id="appTitle">Download Citizen Mobile App</h3>
        <p class="text-xs sm:text-sm text-slate-500 leading-relaxed max-w-xl" id="appDesc">
          Gain direct mobile access to local permit tracking fields, emergency notices, and service scheduling pipelines. Scan the security barcode vector to safely pull down the native Android installation package.
        </p>
        <div class="pt-1 flex flex-wrap justify-center md:justify-start gap-2">
          <span class="text-[10px] font-mono font-bold uppercase px-2 py-0.5 bg-slate-200 text-slate-600 rounded">APK Build</span>
          <span class="text-[10px] font-mono font-bold uppercase px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded">Verified Secure</span>
        </div>
      </div>
      <div class="md:col-span-4 flex flex-col items-center justify-center space-y-2 shrink-0">
        <div class="p-3 bg-white rounded-2xl shadow-sm border border-slate-200">
          <img src="assets/images/qr.jpg" alt="QR Asset" class="h-28 w-28 object-contain">
        </div>
        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Scan to Install</span>
      </div>
    </div>
  </section>

  <!-- Contact -->
  <section id="contacts-registry" class="py-16 bg-slate-100 border-t border-slate-200 px-4 sm:px-6">
    <div class="max-w-7xl mx-auto space-y-8">
      <div class="space-y-2">
        <span class="text-xs font-bold uppercase tracking-wider text-brand-medium">LGU Communications Desk</span>
        <h3 class="text-2xl font-black text-slate-900 tracking-tight">Direct Department Communications Matrix</h3>
      </div>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <!-- Mayor -->
        <div class="p-4 bg-white border border-slate-200 rounded-xl space-y-1 shadow-xs">
          <h5 class="text-xs font-black text-slate-900 uppercase tracking-wide">Office of the City Mayor</h5>
          <p class="text-xs font-semibold text-slate-700"><i class="fa-solid fa-phone mr-1.5 opacity-60"></i> (02) 8332-7711</p>
          <p class="text-[11px] text-brand-dark font-medium"><i class="fa-solid fa-envelope mr-1.5 opacity-60"></i> mayor@caloocancity.gov.ph</p>
        </div>
        <!-- Vice Mayor -->
        <div class="p-4 bg-white border border-slate-200 rounded-xl space-y-1 shadow-xs">
          <h5 class="text-xs font-black text-slate-900 uppercase tracking-wide">Office of the Vice Mayor</h5>
          <p class="text-xs font-semibold text-slate-700"><i class="fa-solid fa-phone mr-1.5 opacity-60"></i> (02) 8332-7712</p>
          <p class="text-[11px] text-brand-dark font-medium"><i class="fa-solid fa-envelope mr-1.5 opacity-60"></i> vicemayor@caloocancity.gov.ph</p>
        </div>
        <!-- Sangguniang Panlungsod -->
        <div class="p-4 bg-white border border-slate-200 rounded-xl space-y-1 shadow-xs">
          <h5 class="text-xs font-black text-slate-900 uppercase tracking-wide">Sangguniang Panlungsod</h5>
          <p class="text-xs font-semibold text-slate-700"><i class="fa-solid fa-phone mr-1.5 opacity-60"></i> (02) 8332-8841</p>
          <p class="text-[11px] text-brand-dark font-medium"><i class="fa-solid fa-envelope mr-1.5 opacity-60"></i> council@caloocancity.gov.ph</p>
        </div>
        <!-- Registry -->
        <div class="p-4 bg-white border border-slate-200 rounded-xl space-y-1 shadow-xs">
          <h5 class="text-xs font-black text-slate-900 uppercase tracking-wide">Civil Registry Department</h5>
          <p class="text-xs font-semibold text-slate-700"><i class="fa-solid fa-phone mr-1.5 opacity-60"></i> (02) 8332-1122</p>
          <p class="text-[11px] text-brand-dark font-medium"><i class="fa-solid fa-envelope mr-1.5 opacity-60"></i> registry@caloocancity.gov.ph</p>
        </div>
        <!-- BPLO -->
        <div class="p-4 bg-white border border-slate-200 rounded-xl space-y-1 shadow-xs">
          <h5 class="text-xs font-black text-slate-900 uppercase tracking-wide">Business Permits & Licensing</h5>
          <p class="text-xs font-semibold text-slate-700"><i class="fa-solid fa-phone mr-1.5 opacity-60"></i> (02) 8332-4455</p>
          <p class="text-[11px] text-brand-dark font-medium"><i class="fa-solid fa-envelope mr-1.5 opacity-60"></i> bplo@caloocancity.gov.ph</p>
        </div>
        <!-- Welfare -->
        <div class="p-4 bg-white border border-slate-200 rounded-xl space-y-1 shadow-xs">
          <h5 class="text-xs font-black text-slate-900 uppercase tracking-wide">Social Welfare & Devt (CSWDO)</h5>
          <p class="text-xs font-semibold text-slate-700"><i class="fa-solid fa-phone mr-1.5 opacity-60"></i> (02) 8332-6677</p>
          <p class="text-[11px] text-brand-dark font-medium"><i class="fa-solid fa-envelope mr-1.5 opacity-60"></i> cswdo@caloocancity.gov.ph</p>
        </div>
        <!-- Health -->
        <div class="p-4 bg-white border border-slate-200 rounded-xl space-y-1 shadow-xs">
          <h5 class="text-xs font-black text-slate-900 uppercase tracking-wide">City Health Department</h5>
          <p class="text-xs font-semibold text-slate-700"><i class="fa-solid fa-phone mr-1.5 opacity-60"></i> (02) 8332-1024</p>
          <p class="text-[11px] text-brand-dark font-medium"><i class="fa-solid fa-envelope mr-1.5 opacity-60"></i> health@caloocancity.gov.ph</p>
        </div>
        <!-- Treasury -->
        <div class="p-4 bg-white border border-slate-200 rounded-xl space-y-1 shadow-xs">
          <h5 class="text-xs font-black text-slate-900 uppercase tracking-wide">City Treasury Office</h5>
          <p class="text-xs font-semibold text-slate-700"><i class="fa-solid fa-phone mr-1.5 opacity-60"></i> (02) 8332-5512</p>
          <p class="text-[11px] text-brand-dark font-medium"><i class="fa-solid fa-envelope mr-1.5 opacity-60"></i> treasury@caloocancity.gov.ph</p>
        </div>
        <!-- Planning -->
        <div class="p-4 bg-white border border-slate-200 rounded-xl space-y-1 shadow-xs">
          <h5 class="text-xs font-black text-slate-900 uppercase tracking-wide">Urban Planning & Zoning</h5>
          <p class="text-xs font-semibold text-slate-700"><i class="fa-solid fa-phone mr-1.5 opacity-60"></i> (02) 8332-9900</p>
          <p class="text-[11px] text-brand-dark font-medium"><i class="fa-solid fa-envelope mr-1.5 opacity-60"></i> planning@caloocancity.gov.ph</p>
        </div>
        <!-- Traffic -->
        <div class="p-4 bg-white border border-slate-200 rounded-xl space-y-1 shadow-xs">
          <h5 class="text-xs font-black text-slate-900 uppercase tracking-wide">Transport & Traffic Management</h5>
          <p class="text-xs font-semibold text-slate-700"><i class="fa-solid fa-phone mr-1.5 opacity-60"></i> (02) 8332-3344</p>
          <p class="text-[11px] text-brand-dark font-medium"><i class="fa-solid fa-envelope mr-1.5 opacity-60"></i> traffic@caloocancity.gov.ph</p>
        </div>
        <!-- DRRMO -->
        <div class="p-4 bg-brand-light/40 border border-brand-border/60 rounded-xl space-y-1 shadow-xs sm:col-span-2">
          <h5 class="text-xs font-black text-red-700 uppercase tracking-wide flex items-center gap-1.5"><i class="fa-solid fa-circle-exclamation text-[10px] animate-pulse"></i> DRRMO Emergency Command Center</h5>
          <p class="text-xs font-bold text-slate-800"><i class="fa-solid fa-phone mr-1.5 text-red-500"></i> Hotline 911 / 8288-2323</p>
          <p class="text-[11px] text-slate-600 font-medium"><i class="fa-solid fa-envelope mr-1.5 opacity-60"></i> drrmo@caloocancity.gov.ph</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-brand-dark text-white/80 border-t-4 border-brand-medium pt-16 pb-12 px-4 sm:px-6">
    <div class="max-w-7xl mx-auto space-y-12">
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 border-b border-white/10 pb-12">
        <div class="space-y-4">
          <div class="flex items-center space-x-2 text-white">
            <img src="assets/images/logo.png" alt="Civentral" class="h-8 w-auto object-contain brightness-200">
            <span class="text-lg font-black tracking-wide uppercase">Caloocan City</span>
          </div>
          <p class="text-xs leading-relaxed text-brand-light/70">
            Providing accessible, smart, and comprehensive administrative tracking environments for all municipal districts.
          </p>
        </div>
        <div class="space-y-3">
          <h5 class="text-xs font-bold uppercase text-white tracking-wider">Public Services</h5>
          <ul class="text-xs space-y-2 text-brand-light/70">
            <li><a href="#features" class="hover:text-white transition">Business Registrations</a></li>
            <li><a href="#features" class="hover:text-white transition">Real Property Tax Inquiries</a></li>
            <li><a href="#features" class="hover:text-white transition">Civil Registry Trackers</a></li>
          </ul>
        </div>
        <div class="space-y-3">
          <h5 class="text-xs font-bold uppercase text-white tracking-wider">Government Links</h5>
          <ul class="text-xs space-y-2 text-brand-light/70">
            <li><a href="#" class="hover:text-white transition">Transparency Board</a></li>
            <li><a href="#" class="hover:text-white transition">Municipal Code Regulations</a></li>
            <li><a href="login.php" class="hover:text-white text-brand-medium font-semibold transition">Administrative Login Portal</a></li>
          </ul>
        </div>
        <div class="space-y-3">
          <h5 class="text-xs font-bold uppercase text-white tracking-wider">City Hall Contact</h5>
          <p class="text-xs leading-relaxed text-brand-light/70">
             City Hall Complex, A. Mabini St., <br>
            Caloocan City, Metro Manila, <br>
            Philippines
          </p>
        </div>
      </div>

      <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0 text-center md:text-left">
        <div class="space-y-1 text-xs">
          <p class="text-brand-light font-medium">&copy; 2026 City Government of Caloocan. All Rights Reserved.</p>
          <p class="text-brand-light/50">Regulated under data infrastructure protection parameters.</p>
        </div>
        <div class="text-[10px] font-bold text-slate-800 tracking-wider max-w-sm border border-brand-border/40 rounded-lg p-3 bg-brand-light">
          DEPT ACCESS ONLY - UNAUTHORIZED USE IS LOGGED & PROSECUTABLE UNDER RA 8792
        </div>
      </div>

    </div>
  </footer>

  <script src="assets/js/index.js"></script>
</body>
</html>