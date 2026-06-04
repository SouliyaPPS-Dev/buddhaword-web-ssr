<section x-data="{
    allSutras: [],
    category: '',
    isLoading: true,
    init() {
        this.category = '<?= addslashes($category) ?>';

        const cached = localStorage.getItem('buddhaword_sutras');
        if (cached) {
            try {
                this.allSutras = JSON.parse(cached);
                if (!Array.isArray(this.allSutras)) this.allSutras = Object.values(this.allSutras);
                this.isLoading = false;
            } catch (e) {
                console.error('Failed to parse cached sutras', e);
            }
        }

        window.addEventListener('sync-complete', () => {
            const freshData = localStorage.getItem('buddhaword_sutras');
            if (freshData) {
                this.allSutras = JSON.parse(freshData);
                if (!Array.isArray(this.allSutras)) this.allSutras = Object.values(this.allSutras);
                this.isLoading = false;
                window.dispatchEvent(new CustomEvent('app-data-ready'));
            }
        });
    },
    get filteredSutras() {
        if (!this.isLoading && this.allSutras.length > 0) {
            return this.allSutras.filter(s => s['ໝວດທັມ'] === this.category);
        }
        return [];
    }
}" class="flex flex-col items-center justify-center mb-4 p-2 sm:p-4">
    <h1 class="text-xl sm:text-2xl font-bold text-[#795548] mb-6 bg-white/80 px-4 py-2 rounded-xl shadow-sm Lao-font"><?= htmlspecialchars($category) ?></h1>

    <!-- Server-rendered Sutra List (visible to search engines, shown while loading) -->
    <div x-show="isLoading" class="flex flex-col gap-3 sm:gap-4 w-full max-w-4xl">
        <?php foreach ($sutras as $sutra): ?>
            <div class="bg-white rounded-xl sm:rounded-2xl shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <div class="p-3 sm:p-4 flex justify-between items-center gap-3 sm:gap-4">
                    <a href="<?= url('/sutra/details/' . htmlspecialchars($sutra['ID'])) ?>" class="flex-1 min-w-0">
                        <h3 class="text-base sm:text-lg font-bold text-gray-800 leading-tight Lao-font truncate sm:whitespace-normal"><?= htmlspecialchars($sutra['ຊື່ພຣະສູດ'] ?? '') ?></h3>
                        <p class="text-[10px] sm:text-sm text-gray-500 mt-1 Lao-font"><?= htmlspecialchars($sutra['ໝວດທັມ'] ?? '') ?></p>
                    </a>
                    <div class="flex items-center gap-1 sm:gap-2 flex-shrink-0">
                        <?php if (!empty($sutra['ສຽງ']) && $sutra['ສຽງ'] !== '/'): ?>
                            <button onclick="playAudio('<?= htmlspecialchars($sutra['ສຽງ'], ENT_QUOTES) ?>', this)" class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-full bg-[#DDCFBC] hover:bg-[#795548] text-[#795548] hover:text-white transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>
                        <?php endif; ?>
                        <a href="<?= url('/sutra/details/' . htmlspecialchars($sutra['ID'])) ?>" class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Alpine-filtered Sutra List (shown when localStorage data is available after sync) -->
    <div x-show="!isLoading" style="display: none;" class="flex flex-col gap-3 sm:gap-4 w-full max-w-4xl">
        <template x-for="sutra in filteredSutras" :key="sutra.ID">
            <div class="bg-white rounded-xl sm:rounded-2xl shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <div class="p-3 sm:p-4 flex justify-between items-center gap-3 sm:gap-4">
                    <a :href="'<?= url('/sutra/details/') ?>' + sutra.ID" class="flex-1 min-w-0">
                        <h3 class="text-base sm:text-lg font-bold text-gray-800 leading-tight Lao-font truncate sm:whitespace-normal" x-text="sutra['ຊື່ພຣະສູດ']"></h3>
                        <p class="text-[10px] sm:text-sm text-gray-500 mt-1 Lao-font" x-text="sutra['ໝວດທັມ']"></p>
                    </a>
                    <div class="flex items-center gap-1 sm:gap-2 flex-shrink-0">
                        <template x-if="sutra['ສຽງ'] && sutra['ສຽງ'] !== '/'">
                            <button @click="playAudio(sutra['ສຽງ'], $el)" class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-full bg-[#DDCFBC] hover:bg-[#795548] text-[#795548] hover:text-white transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>
                        </template>
                        <a :href="'<?= url('/sutra/details/') ?>' + sutra.ID" class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </template>
    </div>
    <div id="youtubePlayer" class="hidden"></div>
</section>

<script src="https://www.youtube.com/iframe_api" defer></script>
<script>
let currentAudio = null;
let currentBtn = null;
let ytPlayer = null;
let currentYtUrl = null;
 
function onYouTubeIframeAPIReady() {
    /* API is ready */
}

function playAudio(url, btn) {
    const isYoutube = url.includes('youtube.com') || url.includes('youtu.be');

    /* Handle same audio toggle */
    if (isYoutube) {
        if (currentYtUrl === url && ytPlayer) {
            const state = ytPlayer.getPlayerState();
            if (state === YT.PlayerState.PLAYING) {
                ytPlayer.pauseVideo();
                updateBtnIcon(btn, false);
            } else {
                ytPlayer.playVideo();
                updateBtnIcon(btn, true);
            }
            return;
        }
    } else {
        if (currentAudio && currentAudio.src === url) {
            if (currentAudio.paused) {
                currentAudio.play();
                updateBtnIcon(btn, true);
            } else {
                currentAudio.pause();
                updateBtnIcon(btn, false);
            }
            return;
        }
    }

    /* Stop previous audio */
    if (currentAudio) {
        currentAudio.pause();
        updateBtnIcon(currentBtn, false);
        currentAudio = null;
    }
    if (ytPlayer && currentYtUrl) {
        ytPlayer.stopVideo();
        updateBtnIcon(currentBtn, false);
    }

    currentBtn = btn;

    if (isYoutube) {
        currentYtUrl = url;
        const videoId = extractYoutubeId(url);
        
        if (ytPlayer) {
            ytPlayer.loadVideoById(videoId);
            ytPlayer.playVideo();
        } else {
            ytPlayer = new YT.Player('youtubePlayer', {
                height: '0',
                width: '0',
                videoId: videoId,
                playerVars: { 
                    'autoplay': 1, 
                    'controls': 0,
                    'origin': window.location.origin
                },
                events: {
                    'onStateChange': (event) => {
                        if (event.data === YT.PlayerState.PLAYING) {
                            updateBtnIcon(currentBtn, true);
                        } else if (event.data === YT.PlayerState.PAUSED || event.data === YT.PlayerState.ENDED) {
                            updateBtnIcon(currentBtn, false);
                        }
                    }
                }
            });
        }
        updateBtnIcon(btn, true);
    } else {
        currentYtUrl = null;
        currentAudio = new Audio(url);
        currentAudio.play();
        updateBtnIcon(btn, true);

        currentAudio.onended = () => {
            updateBtnIcon(btn, false);
            currentAudio = null;
            currentBtn = null;
        };
    }
}

function extractYoutubeId(url) {
    if (!url) return null;
    const regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/;
    const match = url.match(regExp);
    return (match && match[7].length === 11) ? match[7] : null;
}

function updateBtnIcon(btn, isPlaying) {
    if (!btn) return;
    if (isPlaying) {
        btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>`;
    } else {
        btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>`;
    }
}
</script>


    