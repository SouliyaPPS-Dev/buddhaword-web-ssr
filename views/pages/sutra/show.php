<div x-data="{ 
    touchStartX: 0, 
    touchEndX: 0,
    isTurning: false,
    turnDirection: '',
    isLoading: true,
    theme: localStorage.getItem('buddhaword_theme') || 'light',
    sutra: {},
    init() {
        const dataEl = document.getElementById('sutra-data');
        let serverSutra = {};
        if (dataEl) {
            try {
                serverSutra = JSON.parse(dataEl.textContent);
            } catch (e) {
                console.error('Failed to parse server sutra', e);
            }
        }

        const cached = localStorage.getItem('buddhaword_sutras');
        if (cached) {
            try {
                const list = JSON.parse(cached);
                const found = list.find(s => s.ID == serverSutra.ID);
                this.sutra = found || serverSutra;
            } catch (e) {
                console.error('Failed to parse cached sutras', e);
                this.sutra = serverSutra;
            }
        } else {
            this.sutra = serverSutra;
        }

        this.$nextTick(() => { this.isLoading = false; });
        if (this.theme === 'dark') {
            document.documentElement.classList.add('dark');
        }
        window.addEventListener('sync-complete', () => {
            const freshCached = localStorage.getItem('buddhaword_sutras');
            if (freshCached) {
                try {
                    const list = JSON.parse(freshCached);
                    const found = list.find(s => s.ID == this.sutra.ID);
                    if (found) {
                        this.sutra = found;
                    }
                } catch (e) {
                    console.error('Failed to update sutra from sync', e);
                }
            }
        });
    },
    get hasAudio() {
        return this.sutra && this.sutra['ສຽງ'] && this.sutra['ສຽງ'] !== '/';
    },
    get isYoutube() {
        if (!this.hasAudio) return false;
        return this.sutra['ສຽງ'].includes('youtube.com') || this.sutra['ສຽງ'].includes('youtu.be');
    },
    favorites: JSON.parse(localStorage.getItem('buddhaword_favorites') || '[]'),
    get favIndex() {
        return this.favorites.findIndex(f => f.ID == this.sutra.ID);
    },
    get isInFavorites() {
        return this.favIndex !== -1;
    },
    get favPrevID() {
        return this.isInFavorites && this.favIndex > 0 ? this.favorites[this.favIndex - 1].ID : null;
    },
    get favNextID() {
        return this.isInFavorites && this.favIndex < this.favorites.length - 1 ? this.favorites[this.favIndex + 1].ID : null;
    },
    get hasFavPrev() {
        return !!this.favPrevID || !!'<?= $prevID ?>';
    },
    get hasFavNext() {
        return !!this.favNextID || !!'<?= $nextID ?>';
    },
    toggleTheme() {
        this.theme = this.theme === 'light' ? 'dark' : 'light';
        localStorage.setItem('buddhaword_theme', this.theme);
        if (this.theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    },
    handleTouchStart(e) {
        this.touchStartX = e.changedTouches[0].screenX;
    },
    handleTouchEnd(e) {
        this.touchEndX = e.changedTouches[0].screenX;
        this.handleSwipe();
    },
    handleSwipe() {
        const threshold = 80;
        const diff = this.touchStartX - this.touchEndX;
        if (Math.abs(diff) > threshold) {
            if (diff > 0) {
                this.navigate('next');
            } else {
                this.navigate('prev');
            }
        }
    },
    navigate(dir) {
        let targetID = this.isInFavorites
            ? ((dir === 'next') ? this.favNextID : this.favPrevID)
            : null;
        if (!targetID) {
            targetID = (dir === 'next') ? '<?= $nextID ?>' : '<?= $prevID ?>';
        }
        if (!targetID || this.isTurning) return;

        this.turnDirection = (dir === 'next') ? 'turn-next' : 'turn-prev';
        this.isTurning = true;
        
        setTimeout(() => {
            this.loadSutra(targetID);
        }, 500);
    },
    loadSutra(id) {
        stopTTS();
        var self = this;
        fetch('<?= url('/sutra/details/') ?>' + id)
            .then(function(r) { return r.text(); })
            .then(function(html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var newDataEl = doc.getElementById('sutra-data');
                if (newDataEl) {
                    try {
                        var newSutra = JSON.parse(newDataEl.textContent);
                        self.sutra = newSutra;
                        self.isTurning = false;
                        self.turnDirection = '';
                        history.pushState({}, '', '<?= url('/sutra/details/') ?>' + id);
                        var textEl = document.getElementById('sutraText');
                        if (textEl) textEl.style.fontSize = currentFontSize + 'px';

                        var newAudio = doc.querySelector('.audio-player');
                        var oldAudio = document.querySelector('.audio-player');
                        if (newAudio && oldAudio) {
                            oldAudio.outerHTML = newAudio.outerHTML;
                        }

                        var newNav = doc.querySelector('.sutra-nav');
                        var oldNav = document.querySelector('.sutra-nav');
                        if (newNav && oldNav) {
                            oldNav.innerHTML = newNav.innerHTML;
                        }
                    } catch(e) {
                        window.location.href = '<?= url('/sutra/details/') ?>' + id;
                    }
                } else {
                    window.location.href = '<?= url('/sutra/details/') ?>' + id;
                }
            })
            .catch(function() {
                window.location.href = '<?= url('/sutra/details/') ?>' + id;
            });
    }
}" 
@touchstart="handleTouchStart($event)" 
@touchend="handleTouchEnd($event)"
class="relative overflow-hidden min-h-screen pb-20">

    <!-- Sutra Data -->
    <script id="sutra-data" type="application/json">
        <?= json_encode($sutra, JSON_UNESCAPED_UNICODE) ?>
    </script>

    <style>
        .page-container {
            transition: transform 0.5s cubic-bezier(0.645, 0.045, 0.355, 1), opacity 0.4s ease;
            transform-origin: center;
            perspective: 1500px;
        }
        .turn-next {
            transform: rotateY(-20deg) translateX(-100%) scale(0.9);
            opacity: 0;
        }
        .turn-prev {
            transform: rotateY(20deg) translateX(100%) scale(0.9);
            opacity: 0;
        }
        .swipe-hint {
            position: fixed;
            top: 50%;
            transform: translateY(-50%);
            width: 50px;
            height: 50px;
            background: rgba(121, 85, 72, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 50;
        }
        .swipe-hint.visible { opacity: 1; }

        /* Time color */
        .time-display { transition: color 0.3s ease; }
        .time-display.playing { color: #795548; }

        /* Dark mode overrides - warm tones for eye comfort */
        .dark .sutra-card {
            background: rgba(28, 26, 30, 0.97) !important;
            backdrop-filter: blur(12px);
            border-color: rgba(255,255,255,0.06);
        }
        .dark .sutra-content {
            color: #E8DCC8 !important;
        }
        .dark .sutra-nav {
            background: rgba(40, 35, 38, 0.6) !important;
            border-color: rgba(255,255,255,0.05);
        }
        .dark .sutra-nav button {
            color: #C4A88A !important;
        }
        .dark .sutra-nav button:hover {
            color: #DDCFBC !important;
        }
        .dark .sutra-font-controls {
            background: rgba(28, 26, 30, 0.95) !important;
            border-color: rgba(255,255,255,0.08);
        }
        .dark .sutra-font-controls button:first-child {
            background: rgba(255,255,255,0.08) !important;
            color: #C4A88A !important;
        }
        .dark .sutra-font-controls button:first-child:hover {
            background: rgba(255,255,255,0.12) !important;
        }
        .dark .audio-player {
            background: rgba(40, 35, 38, 0.6) !important;
            border-color: rgba(255,255,255,0.05);
        }
        .dark .audio-player button:not(.bg-\\[\\#795548\\]) {
            color: #a8977a !important;
        }
        .dark .audio-player button:not(.bg-\\[\\#795548\\]):hover {
            color: #C4A88A !important;
        }
        .dark .progress-bg {
            background: rgba(255,255,255,0.1) !important;
        }
        .dark .time-display {
            color: #a8977a !important;
        }

        /* Fullscreen scroll support */
        article:fullscreen,
        article:-webkit-full-screen,
        article:-moz-full-screen {
            overflow-y: auto !important;
            width: 100% !important;
            max-width: 100% !important;
            padding: 1rem !important;
            background-color: inherit;
        }

        /* Dark body background override */
        html.dark body,
        html.dark main {
            background-color: #1a181c !important;
            background-image: none !important;
        }

        /* TTS word highlighting */
        .tts-w {
            transition: background-color 0.15s ease, color 0.15s ease;
            border-radius: 2px;
        }
        .tts-active {
            background-color: #795548;
            color: #fff;
            border-radius: 4px;
            padding: 0 2px;
        }
        .dark .tts-active {
            background-color: #a0896e;
            color: #1a181c;
        }
    </style>
 
    <article class="max-w-4xl mx-auto p-2 sm:p-6 page-container" :class="isTurning ? turnDirection : ''">
        <div class="bg-white/95 backdrop-blur-md rounded-2xl sm:rounded-3xl shadow-2xl overflow-hidden border border-white/20 ring-1 ring-black/5 sutra-card">
            <!-- Header -->
            <div class="p-4 sm:p-6 bg-[#795548] text-white">
                <div class="flex justify-between items-start gap-4">
                    <div class="min-w-0 flex-1">
                        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold leading-tight Lao-font" x-text="sutra['ຊື່ພຣະສູດ']"><?= htmlspecialchars($sutra['ຊື່ພຣະສູດ']) ?></h1>
                        <p class="text-white/80 mt-2 flex items-center gap-2 text-xs sm:text-base Lao-font">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            <span class="truncate" x-text="sutra['ໝວດທັມ']"><?= htmlspecialchars($sutra['ໝວດທັມ']) ?></span>
                        </p>
                    </div>
                </div>
                <div class="flex justify-between items-center mt-3">
                    <div class="flex items-center gap-1 sm:gap-2">
                        <button onclick="changeFontSize(-2)" class="px-2 sm:px-3 py-1.5 sm:py-2 rounded-lg sm:rounded-xl bg-white/10 hover:bg-white/20 text-white/70 hover:text-white font-bold transition-colors text-xs sm:text-sm">A-</button>
                        <button onclick="changeFontSize(2)" class="px-2 sm:px-3 py-1.5 sm:py-2 rounded-lg sm:rounded-xl bg-white/10 hover:bg-white/20 text-white/70 hover:text-white font-bold transition-colors text-xs sm:text-sm">A+</button>
                    </div>
                    <div class="flex items-center gap-1 sm:gap-2">
                        <!-- Theme Toggle -->
                        <button @click="toggleTheme()" class="p-1.5 sm:p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors text-white/70 hover:text-white">
                            <svg x-show="theme === 'light'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                            <svg x-show="theme === 'dark'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </button>

                        <!-- TTS Button -->
                        <button onclick="toggleTTS()" id="ttsBtn" class="p-1.5 sm:p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors text-white/70 hover:text-white" title="ອ່ານອອກສຽງ">
                            <svg id="ttsIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M17.95 6.05a8 8 0 010 11.9M11 5L6 9H2v6h4l5 4V5z" />
                            </svg>
                        </button>

                        <!-- Fullscreen Toggle -->
                        <button onclick="toggleSutraFullscreen()" id="fullscreenSutraBtn" class="p-1.5 sm:p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors text-white/70 hover:text-white">
                            <svg id="fullscreenSutraIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                            </svg>
                        </button>

                        <!-- Share Button -->
                        <button onclick="shareSutra(this)" class="p-1.5 sm:p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors text-white/70 hover:text-white" title="ແບ່ງປັນ">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                        </button>

                        <div x-data="{ 
                            isFavorite: JSON.parse(localStorage.getItem('buddhaword_favorites') || '[]').some(f => f.ID == sutra.ID),
                            toggleFavorite() {
                                let favorites = JSON.parse(localStorage.getItem('buddhaword_favorites') || '[]');
                                if (this.isFavorite) {
                                    favorites = favorites.filter(f => f.ID != sutra.ID);
                                } else {
                                    favorites.push(sutra);
                                }
                                localStorage.setItem('buddhaword_favorites', JSON.stringify(favorites));
                                this.isFavorite = !this.isFavorite;
                            }
                        }">
                            <button @click="toggleFavorite()" class="p-1.5 sm:p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors" :class="isFavorite ? 'text-red-400' : 'text-white/50'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" :fill="isFavorite ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /> 
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audio Player -->
            <div x-show="hasAudio" x-data="{ isLooping: false }" class="px-4 sm:px-6 py-3 sm:py-5 bg-[#DDCFBC]/50 border-b border-[#795548]/10 audio-player" x-transition>
                <div class="flex items-center justify-center gap-2 sm:gap-3 mb-3">
                    <!-- Skip Back -->
                    <button onclick="skipAudio(-10)" class="p-1.5 sm:p-2 rounded-full hover:bg-black/10 text-gray-500 hover:text-[#795548] transition-all" title="ກັບຄືນ 10 ວິ">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.334 4zM4.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0011 16V8a1 1 0 00-1.6-.8l-5.334 4z" />
                        </svg>
                    </button>

                    <!-- Loop -->
                    <div class="relative">
                        <button @click="isLooping = !isLooping; toggleLoop(isLooping)" 
                                :class="isLooping ? 'bg-[#795548] text-white shadow-md' : 'text-gray-500 hover:text-[#795548] hover:bg-black/10'"
                                class="p-2 sm:p-2.5 rounded-full transition-all duration-200" :title="isLooping ? 'ກຳລັງວົນຊ້ຳ' : 'ວົນຊ້ຳ'">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" :fill="isLooping ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                        <template x-if="isLooping">
                            <span class="absolute -top-1 -right-1 w-3.5 h-3.5 sm:w-4 sm:h-4 bg-green-500 border-2 border-white rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-2 w-2 sm:h-2.5 sm:w-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                        </template>
                    </div>

                    <!-- Play/Pause -->
                    <button id="playPauseBtn" onclick="toggleAudio()" data-url="<?= htmlspecialchars($sutra['ສຽງ'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center rounded-full bg-[#DDCFBC] text-[#795548] hover:bg-[#795548] hover:text-white transition-all">
                        <svg id="playIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg id="pauseIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>

                    <!-- Download -->
                    <a :href="sutra['ສຽງ']" download target="_blank" rel="noopener"
                       class="p-1.5 sm:p-2 rounded-full hover:bg-black/10 text-gray-500 hover:text-[#795548] transition-all" title="ດາວໂຫລດ">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </a>

                    <!-- Skip Forward -->
                    <button onclick="skipAudio(10)" class="p-1.5 sm:p-2 rounded-full hover:bg-black/10 text-gray-500 hover:text-[#795548] transition-all" title="ໄປໜ້າ 10 ວິ">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.934 12.8a1 1 0 000-1.6l-5.334-4A1 1 0 005 8v8a1 1 0 001.6.8l5.334-4zM19.934 12.8a1 1 0 000-1.6l-5.334-4A1 1 0 0013 8v8a1 1 0 001.6.8l5.334-4z" />
                        </svg>
                    </button>
                </div>

                <div class="flex-1">
                    <div class="h-1.5 sm:h-2 bg-gray-200 rounded-full overflow-hidden cursor-pointer relative mb-2 progress-bg" onclick="seekAudio(event)">
                        <div id="progress" class="h-full bg-gradient-to-r from-[#795548] to-[#b08870] w-0 transition-all duration-100"></div>
                    </div>
                    <div class="flex items-center justify-center gap-1 font-mono" id="timeDisplay">
                        <span id="currentTime" class="time-display text-xs sm:text-sm text-gray-400">0:00</span>
                        <span class="text-xs text-gray-300">/</span>
                        <span id="duration" class="time-display text-xs sm:text-sm text-gray-400">0:00</span>
                    </div>
                </div>
                
                <audio id="sutraAudio" class="hidden" :src="!isYoutube && hasAudio ? sutra['ສຽງ'] : ''" ontimeupdate="updateProgress()" onloadedmetadata="initAudio()" onended="onAudioEnded()"></audio>
            </div>

            <!-- Content -->
            <div class="p-4 sm:p-10 sutra-content text-lg sm:text-xl md:text-2xl leading-relaxed text-gray-800 space-y-4 Lao-font min-h-[300px]" id="sutraText">
                <div x-html="sutra['ພຣະສູດ'] ? sutra['ພຣະສູດ'].replace(/\n/g, '<br>') : ''">
                    <?= nl2br($sutra['ພຣະສູດ']) ?>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="px-6 py-4 flex justify-between items-center bg-gray-50/50 border-t border-gray-100 sutra-nav">
                <div class="flex-1">
                    <template x-if="isInFavorites ? hasFavPrev : '<?= $prevID ?>'">
                        <button @click="navigate('prev')" class="flex items-center gap-1 text-[#795548] font-bold Lao-font hover:underline group">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            ກ່ອນໜ້າ
                        </button>
                    </template>
                </div>
                <div class="flex-1 flex justify-end">
                    <template x-if="isInFavorites ? hasFavNext : '<?= $nextID ?>'">
                        <button @click="navigate('next')" class="flex items-center gap-1 text-[#795548] font-bold Lao-font hover:underline group text-right">
                            ຕໍ່ໄປ
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </template>
                </div>
            </div>

        </div>
    </article>

    <!-- Navigation Hints -->
    <div x-show="isInFavorites ? hasFavPrev : '<?= $prevID ?>'" class="swipe-hint left-4" :class="touchEndX > touchStartX && (touchEndX - touchStartX > 40) ? 'visible' : ''">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#795548]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </div>
    <div x-show="isInFavorites ? hasFavNext : '<?= $nextID ?>'" class="swipe-hint right-4" :class="touchStartX > touchEndX && (touchStartX - touchEndX > 40) ? 'visible' : ''">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#795548]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </div>

</section>

<div id="youtubePlayer" class="hidden"></div>

<script src="https://www.youtube.com/iframe_api" defer></script>
<script>
let currentFontSize = parseInt(localStorage.getItem('buddhaword_fontsize') || '20', 10);
let ytPlayer = null;
let loopEnabled = false;
let ytReady = false;
let ytVideoId = '';

function onYouTubeIframeAPIReady() {
    ytReady = true;
}

function setTimeColor(active) {
    document.querySelectorAll('.time-display').forEach(el => el.classList.toggle('playing', active));
}

function setBtnPlaying(isPlaying) {
    const btn = document.getElementById('playPauseBtn');
    if (!btn) return;
    btn.classList.toggle('bg-[#DDCFBC]', !isPlaying);
    btn.classList.toggle('text-[#795548]', !isPlaying);
    btn.classList.toggle('bg-[#795548]', isPlaying);
    btn.classList.toggle('text-white', isPlaying);
}

function toggleAudio() {
    const btn = document.getElementById('playPauseBtn');
    const url = btn?.dataset?.url;
    if (!url) return;

    const isYoutube = url.includes('youtube.com') || url.includes('youtu.be');

    if (isYoutube) {
        const ytId = extractYoutubeId(url);
        if (!ytId) return;

        if (ytPlayer) {
            const state = ytPlayer.getPlayerState();
            if (state === YT.PlayerState.PLAYING) {
                ytPlayer.pauseVideo();
            } else {
                ytPlayer.loadVideoById(ytId);
                ytPlayer.playVideo();
            }
            return;
        }

        if (!ytReady || typeof YT === 'undefined') {
            const check = setInterval(() => {
                if (ytReady && typeof YT !== 'undefined') {
                    clearInterval(check);
                    initYtPlayer(ytId);
                }
            }, 200);
            return;
        }

        initYtPlayer(ytId);
    } else {
        const audio = document.getElementById('sutraAudio');
        if (!audio) return;
        const playIcon = document.getElementById('playIcon');
        const pauseIcon = document.getElementById('pauseIcon');

        if (audio.paused) {
            if (audio.src !== url && url) {
                audio.src = url;
            }
            audio.play().catch(() => {});
            playIcon.classList.add('hidden');
            pauseIcon.classList.remove('hidden');
            setTimeColor(true);
            setBtnPlaying(true);
        } else {
            audio.pause();
            playIcon.classList.remove('hidden');
            pauseIcon.classList.add('hidden');
            setTimeColor(false);
            setBtnPlaying(false);
        }
    }
}

function initYtPlayer(ytId) {
    ytVideoId = ytId;
    const btn = document.getElementById('playPauseBtn');
    const originalContent = btn.innerHTML;
    const spinner = '<svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

    btn.disabled = true;
    btn.innerHTML = spinner;
    setBtnPlaying(true);

    ytPlayer = new YT.Player('youtubePlayer', {
        height: '0',
        width: '0',
        videoId: ytId,
        playerVars: { 'autoplay': 1, 'controls': 0, 'origin': window.location.origin },
        events: { 
            'onReady': (event) => {
                btn.disabled = false;
                btn.innerHTML = originalContent;
                setBtnPlaying(true);
                const playIcon = document.getElementById('playIcon');
                const pauseIcon = document.getElementById('pauseIcon');
                if (playIcon && pauseIcon) {
                    playIcon.classList.add('hidden');
                    pauseIcon.classList.remove('hidden');
                }
                event.target.playVideo();
                document.getElementById('duration').textContent = formatTime(event.target.getDuration());
            },
            'onStateChange': onPlayerStateChange,
            'onError': () => {
                btn.disabled = false;
                btn.innerHTML = originalContent;
                setBtnPlaying(false);
            }
        }
    });
} 
 
function onPlayerStateChange(event) {
    const playIcon = document.getElementById('playIcon');
    const pauseIcon = document.getElementById('pauseIcon');

    if (event.data == YT.PlayerState.PLAYING) {
        playIcon.classList.add('hidden');
        pauseIcon.classList.remove('hidden');
        setTimeColor(true);
        setBtnPlaying(true);

        if (!window.ytProgressInterval) {
            window.ytProgressInterval = setInterval(() => {
                if (ytPlayer && ytPlayer.getCurrentTime) {
                    const currentTime = ytPlayer.getCurrentTime();
                    const duration = ytPlayer.getDuration();
                    const percent = (currentTime / duration) * 100;
                    document.getElementById('progress').style.width = percent + '%';
                    document.getElementById('currentTime').textContent = formatTime(currentTime);
                    document.getElementById('duration').textContent = formatTime(duration);
                }
            }, 1000);
        }
    } else if (event.data == YT.PlayerState.ENDED && loopEnabled && ytVideoId) {
        ytPlayer.loadVideoById(ytVideoId);
        return;
    } else {
        playIcon.classList.remove('hidden');
        pauseIcon.classList.add('hidden');
        setTimeColor(false);
        setBtnPlaying(false);
        if (window.ytProgressInterval && (event.data == YT.PlayerState.PAUSED || event.data == YT.PlayerState.ENDED)) {
            clearInterval(window.ytProgressInterval);
            window.ytProgressInterval = null;
        }
    }
}

function extractYoutubeId(url) {
    if (!url) return '';
    const regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/;
    const match = url.match(regExp);
    return (match && match[7].length === 11) ? match[7] : '';
}

function initAudio() {
    const audio = document.getElementById('sutraAudio');
    if (audio) {
        document.getElementById('duration').textContent = formatTime(audio.duration);
    }
}

function formatTime(seconds) {
    if (isNaN(seconds) || seconds < 0) return '0:00';
    const min = Math.floor(seconds / 60);
    const sec = Math.floor(seconds % 60);
    return `${min}:${sec.toString().padStart(2, '0')}`;
}

function changeFontSize(delta) {
    currentFontSize = Math.min(Math.max(12, currentFontSize + delta), 40);
    const textEl = document.getElementById('sutraText');
    if (textEl) textEl.style.fontSize = currentFontSize + 'px';
    localStorage.setItem('buddhaword_fontsize', currentFontSize.toString());
}

function getSutraFullscreenElement() {
    return document.fullscreenElement ||
        document.webkitFullscreenElement ||
        document.mozFullScreenElement ||
        document.msFullscreenElement;
}

function requestSutraFullscreen(el) {
    if (el.requestFullscreen) return el.requestFullscreen();
    if (el.webkitRequestFullscreen) { el.webkitRequestFullscreen(); return Promise.resolve(); }
    if (el.mozRequestFullScreen) { el.mozRequestFullScreen(); return Promise.resolve(); }
    if (el.msRequestFullscreen) { el.msRequestFullscreen(); return Promise.resolve(); }
    return Promise.reject(new Error('Fullscreen not supported'));
}

function exitSutraFullscreen() {
    if (document.exitFullscreen) return document.exitFullscreen();
    if (document.webkitExitFullscreen) { document.webkitExitFullscreen(); return Promise.resolve(); }
    if (document.mozCancelFullScreen) { document.mozCancelFullScreen(); return Promise.resolve(); }
    if (document.msExitFullscreen) { document.msExitFullscreen(); return Promise.resolve(); }
    return Promise.reject(new Error('Fullscreen not supported'));
}

function toggleSutraFullscreen() {
    const container = document.querySelector('article');
    const icon = document.getElementById('fullscreenSutraIcon');
    if (!getSutraFullscreenElement()) {
        if (container) {
            requestSutraFullscreen(container).then(() => {
                container.style.maxWidth = '100%';
                container.style.overflowY = 'auto';
                icon.innerHTML = '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M6 18L18 6M6 6l12 12\" />';
            }).catch(() => {});
        }
    } else {
        exitSutraFullscreen().then(() => {
            if (container) {
                container.style.maxWidth = '';
                container.style.overflowY = '';
            }
            icon.innerHTML = '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4\" />';
        }).catch(() => {});
    }
}

function onSutraFullscreenChange() {
    const container = document.querySelector('article');
    const icon = document.getElementById('fullscreenSutraIcon');
    if (!getSutraFullscreenElement()) {
        if (container) {
            container.style.maxWidth = '';
            container.style.overflowY = '';
        }
        icon.innerHTML = '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4\" />';
    }
}
document.addEventListener('fullscreenchange', onSutraFullscreenChange);
document.addEventListener('webkitfullscreenchange', onSutraFullscreenChange);
document.addEventListener('mozfullscreenchange', onSutraFullscreenChange);
document.addEventListener('MSFullscreenChange', onSutraFullscreenChange);

(function() {
    const textEl = document.getElementById('sutraText');
    if (textEl) textEl.style.fontSize = currentFontSize + 'px';
})();

function skipAudio(seconds) {
    if (ytPlayer) {
        const currentTime = ytPlayer.getCurrentTime();
        ytPlayer.seekTo(currentTime + seconds, true);
    } else {
        const audio = document.getElementById('sutraAudio');
        if (audio) audio.currentTime += seconds;
    }
}

function toggleLoop(enabled) {
    loopEnabled = enabled;
    const audio = document.getElementById('sutraAudio');
    if (audio) audio.loop = enabled;
}

function seekAudio(event) {
    const bar = event.currentTarget;
    const rect = bar.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const clickedPercent = x / rect.width;

    if (ytPlayer) {
        ytPlayer.seekTo(ytPlayer.getDuration() * clickedPercent, true);
    } else {
        const audio = document.getElementById('sutraAudio');
        if (audio) audio.currentTime = audio.duration * clickedPercent;
    }
}

function onAudioEnded() {
    if (loopEnabled) return;
    const playIcon = document.getElementById('playIcon');
    const pauseIcon = document.getElementById('pauseIcon');
    if (playIcon && pauseIcon) {
        playIcon.classList.remove('hidden');
        pauseIcon.classList.add('hidden');
    }
    setTimeColor(false);
    setBtnPlaying(false);
}

function updateProgress() {
    const audio = document.getElementById('sutraAudio');
    if (!audio || audio.duration === 0) return;
    const percent = (audio.currentTime / audio.duration) * 100;
    document.getElementById('progress').style.width = percent + '%';
    document.getElementById('currentTime').textContent = formatTime(audio.currentTime);
}

function shareSutra(btn) {
    const titleEl = document.querySelector('h1');
    const title = titleEl?.textContent?.trim() || 'ພຣະສູດ';
    const url = window.location.href;
    const text = `ຟັງ ${title} ທີ່ ຄຳສອນພຸດທະ`;

    if (navigator.share) {
        navigator.share({ title, text, url }).catch(() => {});
    } else {
        navigator.clipboard.writeText(url).then(() => {
            if (btn) {
                const orig = btn.innerHTML;
                btn.innerHTML = '<svg xmlns=\"http://www.w3.org/2000/svg\" class=\"h-5 w-5 sm:h-6 sm:w-6\" fill=\"none\" viewBox=\"0 0 24 24\" stroke=\"currentColor\" stroke-width=\"2\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M5 13l4 4L19 7\" /></svg>';
                setTimeout(() => { btn.innerHTML = orig; }, 2000);
            }
        }).catch(() => {});
    }
}

window.addEventListener('popstate', function() {
    window.location.reload();
});

/* === TTS Speaking Voice === */
var ttsPlaying = false;
var ttsOrigHTML = null;
var ttsAudioCtx = null;
var ttsSource = null;
var ttsInterval = null;
var ttsTimeout = null;

function detectLanguage(text) {
    var laoCount = (text.match(/[\u{0E80}-\u{0EFF}]/gu) || []).length;
    var thaiCount = (text.match(/[\u{0E00}-\u{0E7F}]/gu) || []).length;
    var engCount = (text.match(/[a-zA-Z]/g) || []).length;
    if (laoCount > thaiCount && laoCount > engCount) return 'lo-LA';
    if (thaiCount > laoCount && thaiCount > engCount) return 'th-TH';
    return 'en-US';
}

function getSutraText() {
    var el = document.getElementById('sutraText');
    if (!el) return '';
    var text = el.innerText || el.textContent || '';
    // Skip navigation buttons text by excluding the nav/footer area
    return text.replace(/\s+/g, ' ').trim();
}

function stopTTS() {
    if (ttsTimeout) { clearTimeout(ttsTimeout); ttsTimeout = null; }
    window.__ttsStarted = false;
    if (ttsSource) { try { ttsSource.stop(); } catch(e) {} ttsSource = null; }
    if (ttsInterval) { clearInterval(ttsInterval); ttsInterval = null; }
    ttsPlaying = false;
    updateTTSIcon();
    var btn = document.getElementById('ttsBtn');
    if (btn) {
        btn.classList.remove('text-green-300', 'bg-green-500/20');
        btn.classList.add('text-white/70');
    }
    if (ttsOrigHTML) {
        var el = document.getElementById('sutraText');
        if (el) el.innerHTML = ttsOrigHTML;
        ttsOrigHTML = null;
    }
}

function toggleTTS() {
    if (ttsPlaying) {
        stopTTS();
        return;
    }
    var text = getSutraText();
    if (!text) return;

    stopTTS();

    var lang = detectLanguage(text);
    var textEl = document.getElementById('sutraText');
    if (!textEl) return;

    // Wrap words for highlighting
    ttsOrigHTML = textEl.innerHTML;
    textEl.innerHTML = textEl.innerHTML.replace(/(<[^>]+>)|(\S+)|(\s+)/gi, function(m, tag) {
        if (tag) return tag;
        return '<span class="tts-w">' + m + '</span>';
    });

    ttsPlaying = true;
    updateTTSIcon();
    var btn = document.getElementById('ttsBtn');
    if (btn) {
        btn.classList.remove('text-white/70');
        btn.classList.add('text-green-300', 'bg-green-500/20');
    }

    // Auto-stop if no audio received within 12 seconds
    if (ttsTimeout) clearTimeout(ttsTimeout);
    ttsTimeout = setTimeout(function() {
        if (ttsPlaying && !window.__ttsStarted) {
            stopTTS();
        }
    }, 12000);

    // Truncate long text
    if (text.length > 1800) {
        var idx = text.lastIndexOf('.', 1800);
        if (idx < 0) idx = text.lastIndexOf(' ', 1800);
        if (idx > 0) text = text.substring(0, idx + 1);
        else text = text.substring(0, 1800);
    }

    if (!ttsAudioCtx) ttsAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
    if (ttsAudioCtx.state === 'suspended') ttsAudioCtx.resume();

    var words = textEl.querySelectorAll('.tts-w');

    var ttsStarted = false;
    function doPlay(buffer, timepoints) {
        if (!ttsPlaying || ttsStarted) return;
        ttsStarted = true;
        window.__ttsStarted = true;
        if (ttsTimeout) { clearTimeout(ttsTimeout); ttsTimeout = null; }
        function start(audioBuffer) {
            ttsSource = ttsAudioCtx.createBufferSource();
            ttsSource.buffer = audioBuffer;
            ttsSource.connect(ttsAudioCtx.destination);
            var tpIdx = 0;
            var startTime = ttsAudioCtx.currentTime;
            ttsInterval = setInterval(function() {
                if (!ttsPlaying) { clearInterval(ttsInterval); return; }
                var elapsed = ttsAudioCtx.currentTime - startTime;
                while (tpIdx < timepoints.length && elapsed >= timepoints[tpIdx].timeSeconds) {
                    words.forEach(function(w) { w.classList.remove('tts-active'); });
                    if (tpIdx < words.length) words[tpIdx].classList.add('tts-active');
                    tpIdx++;
                }
            }, 50);
            ttsSource.onended = function() { clearInterval(ttsInterval); stopTTS(); };
            ttsSource.start(0);
        }
        if (buffer instanceof AudioBuffer) { start(buffer); }
        else { ttsAudioCtx.decodeAudioData(buffer, start, function() {}); }
    }

    function estimateTimepoints(duration) {
        var tps = [];
        var totalChars = 0;
        var re2 = /\S+/g;
        var m2;
        while ((m2 = re2.exec(text)) !== null) {
            tps.push({ markName: m2[0], timeSeconds: (totalChars / text.length) * duration });
            totalChars += m2[0].length + 1;
        }
        return tps;
    }

    // Call server API
    var apiUrl = document.getElementById('ttsApiUrl').value;
    fetch(apiUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ text: text, language: lang })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.fallback) return;
        if (data.error) { console.warn('Server TTS failed:', data); return; }
        var binary = atob(data.audioContent);
        var len = binary.length;
        var bytes = new Uint8Array(len);
        for (var i = 0; i < len; i++) bytes[i] = binary.charCodeAt(i);
        doPlay(bytes.buffer, data.timepoints || []);
    })
    .catch(function(e) { console.warn('Server TTS fetch failed:', e); });
}

function updateTTSIcon() {
    var icon = document.getElementById('ttsIcon');
    if (!icon) return;
    if (ttsPlaying) {
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />';
    } else {
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M17.95 6.05a8 8 0 010 11.9M11 5L6 9H2v6h4l5 4V5z" />';
    }
}
</script>

    