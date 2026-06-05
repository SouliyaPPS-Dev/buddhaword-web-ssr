<section x-data="searchBooksApp()" class="flex flex-col items-center justify-center mb-5 page-enter">
    <!-- Sticky Search (matches /book page style) -->
    <div class="sticky z-20 px-4 py-2 w-full max-w-lg mx-auto"
         :class="navbarHidden ? 'top-0' : 'top-[60px]'">
        <div class="relative"> 
            <input x-ref="searchInput"
                   x-model="query"
                   @input.debounce.250ms="doSearch()"
                   @keydown.enter="doSearch()"
                   type="search"
                   placeholder="ຄົ້ນຫາ..."
                   class="w-full bg-white/90 backdrop-blur-md border-none rounded-lg py-2.5 pl-9 pr-3 text-sm shadow-lg focus:ring-2 focus:ring-brown-500 outline-none transition-all Lao-font">
            <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Loading -->
    <div x-show="isSearching" class="flex justify-center py-16" x-cloak>
        <div class="loader-circle"></div>
    </div>

    <!-- No results -->
    <div x-show="!isSearching && query.length >= 2 && results.length === 0"
         class="bg-white/95 backdrop-blur-md rounded-2xl shadow-xl border border-white/20 p-8 text-center" x-cloak>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-gray-500 Lao-font">ບໍ່ພົບຂໍ້ມູນສຳລັບ "<span x-text="query" class="font-bold text-[#795548]"></span>"</p>
    </div>

    <!-- Global Results -->
    <div x-show="!isSearching && query.length >= 2 && results.length > 0" class="space-y-3" x-cloak>
        <div class="flex items-center gap-2 text-sm text-gray-400 px-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            ພົບ <span x-text="results.length" class="font-bold text-[#795548]"></span> ຜົນການຄົ້ນຫາ
        </div>
        <template x-for="r in results" :key="r.slug + '-' + r.page">
            <div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-xl border border-white/20 overflow-hidden hover:shadow-lg transition-all page-enter">
                <a :href="'<?= url('/search-books') ?>' + '/' + r.slug + '/page/' + r.page + '?q=' + encodeURIComponent(query)"
                   class="block p-4 sm:p-5">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-0.5 rounded-lg bg-[#795548] text-white text-xs font-bold">ໜ້າ <span x-text="r.page"></span></span>
                        <span class="text-xs text-gray-400" x-text="r.matches + ' ຄັ້ງ'"></span>
                        <span class="text-xs px-2 py-0.5 rounded-full" :class="r.bookType === 'pdf' ? 'bg-blue-50 text-blue-600' : 'bg-green-50 text-green-600'" x-text="r.bookTitle"></span>
                    </div>
                    <p class="result-snippet text-sm sm:text-base text-gray-600 Lao-font leading-relaxed line-clamp-3" x-html="highlightText(r.snippet, query)"></p>
                    <div class="mt-2 flex items-center gap-1 sm:gap-2">
                        <button @click.stop="speakResult(r, $el.parentElement.parentElement)" class="p-1.5 sm:p-2 rounded-full bg-gray-100 hover:bg-[#795548]/20 transition-colors text-gray-400 hover:text-[#795548]" title="ອ່ານອອກສຽງ">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M17.95 6.05a8 8 0 010 11.9M11 5L6 9H2v6h4l5 4V5z" />
                            </svg>
                        </button>
                        <a :href="'<?= url('/search-books') ?>' + '/' + r.slug + '/page/' + r.page + '?q=' + encodeURIComponent(query)" class="inline-flex items-center gap-1 text-xs text-[#795548] font-bold group">
                            ເບິ່ງລາຍລະອຽດ
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </a>
            </div>
        </template>
    </div>

    <!-- Book Listing (shown when not searching) -->
    <div x-show="query.length < 2" class="grid gap-4 grid-cols-3 sm:grid-cols-5 md:grid-cols-5 lg:grid-cols-5 mb-20 w-full max-w-5xl px-2" x-cloak>
        <?php foreach ($books as $book): ?>
            <?php
                $coverDir = __DIR__ . '/../../../public/assets/' . $book['slug'];
                $coverFile = '';
                $hasCover = false;
                foreach (['png', 'jpg', 'jpeg'] as $ext) {
                    $path = $coverDir . '/cover.' . $ext;
                    if (file_exists($path)) {
                        $coverFile = 'cover.' . $ext;
                        $hasCover = true;
                        break;
                    }
                }
                $coverPath = $coverFile ? url('/assets/' . $book['slug'] . '/' . $coverFile) : url('assets/images/book-placeholder.svg');
            ?>
            <div class="flex flex-col items-center" style="margin-bottom: -2rem;">
                <a href="<?= url('/search-books/' . $book['slug'] . '/page/1') ?>"
                   class="z-10 flex flex-col justify-between items-center cursor-pointer group">
                    <div class="w-[115px] sm:w-[130px] md:w-[140px] lg:w-[185px] h-[205px] sm:h-[205px] md:h-[255px] lg:h-[305px] flex-shrink-0 mx-2 shadow-xl transition-transform duration-300 transform group-hover:-translate-y-2 overflow-hidden flex flex-col relative"
                         style="border-radius: 0; background: transparent; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2), 0 4px 6px -2px rgba(0,0,0,0.1), inset 0 -3px 6px rgba(0,0,0,0.2);">
                        <div class="flex-grow" style="margin-top: 1rem;">
                            <p class="text-white text-center text-xs sm:text-sm Lao-font leading-tight px-1 truncate"><?= htmlspecialchars($book['title']) ?></p>
                        </div>
                        <img src="<?= $hasCover ? $coverPath : url('assets/images/book-placeholder.svg') ?>"
                             alt="<?= htmlspecialchars($book['title']) ?>"
                             loading="lazy"
                             class="z-0 object-fill transition-opacity duration-300 mt-1 h-[165px] sm:h-[200px] md:h-[220px] lg:h-[270px] w-full"
                             style="border-radius: 0;"
                             onerror="this.src='<?= url('assets/images/book-placeholder.svg') ?>'; this.style.objectFit='contain'; this.style.padding='8px'; this.style.backgroundColor='#6B553A'; this.style.opacity='0.6'">
                    </div>
                    <div class="w-[90%] h-4 mt-[-1.5rem] bg-gray-900 opacity-25 blur-md"></div>
                </a>
                <div class="relative w-full mt-[-0rem] h-6 sm:h-5 md:h-6 lg:h-8 z-1" style="width: 115%;">
                    <div class="absolute top-0 left-0 w-full h-1 sm:h-3 md:h-4 bg-[#B96A44] shadow-lg"></div>
                    <div class="absolute top-1 left-0 w-full h-2 sm:h-1.5 bg-[#E0895C] shadow mb-0"></div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-[#A65D3B] shadow-inner mb-4"></div>
                    <div class="absolute bottom-0 left-0 w-full h-1 sm:h-2 bg-[#B96A44] shadow-inner mb-3"></div>
                    <div class="absolute top-0 left-0 w-full h-4 bg-[#E0895C] opacity-50"></div>
                    <div class="absolute -top-2 left-2 w-[96%] h-4 bg-black opacity-10 blur-md"></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <style>
        .tts-reading { box-shadow: 0 0 0 2px #79554840, 0 0 20px #79554820 !important; transition: box-shadow .2s; }
        .tts-w { transition: background .15s, color .15s; border-radius: 2px; }
        .tts-w.tts-active { background: #fbbf24; color: #1a1a1a; box-shadow: 0 1px 3px #00000020; }
        .loader-circle {
            width: 44px;
            height: 44px;
            border: 4px solid rgba(121,85,72,.12);
            border-top-color: #795548;
            border-right-color: #b08870;
            border-radius: 50%;
            animation: spin-circle .7s cubic-bezier(.42,0,.58,1) infinite;
            box-shadow: 0 0 20px rgba(121,85,72,.08);
        }
        @keyframes spin-circle {
            to { transform: rotate(360deg); }
        }
        .page-enter {
            animation: fadeUp .35s ease-out both;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</section>

<script>
let ttsState = { playing: false, element: null, origHTML: null, audioCtx: null, source: null, interval: null };

function detectLanguage(text) {
    var laoCount = (text.match(/[\u{0E80}-\u{0EFF}]/gu) || []).length;
    var thaiCount = (text.match(/[\u{0E00}-\u{0E7F}]/gu) || []).length;
    var engCount = (text.match(/[a-zA-Z]/g) || []).length;
    if (laoCount > thaiCount && laoCount > engCount) return 'lo-LA';
    if (thaiCount > laoCount && thaiCount > engCount) return 'th-TH';
    return 'en-US';
}

function stopSearchTTS() {
    if (ttsState.source) { try { ttsState.source.stop(); } catch(e) {} ttsState.source = null; }
    if (ttsState.interval) { clearInterval(ttsState.interval); ttsState.interval = null; }
    ttsState.playing = false;
    if (ttsState.element) {
        ttsState.element.classList.remove('tts-reading');
        if (ttsState.origHTML) { var s = ttsState.element.querySelector('.result-snippet'); if (s) s.innerHTML = ttsState.origHTML; }
    }
    ttsState.element = null;
    ttsState.origHTML = null;
}
 
function searchBooksApp() {
    return {
        query: '',
        results: [],
        isSearching: false,
        navbarHidden: false,
        speakResult(r, cardEl) {
            if (ttsState.playing && ttsState.element === cardEl) { stopSearchTTS(); return; }
            stopSearchTTS();

            var snippetEl = cardEl.querySelector('.result-snippet');
            if (!snippetEl) return;
            var text = snippetEl.textContent.replace(/\s+/g, ' ').trim();
            if (!text) return;
            ttsState.origHTML = snippetEl.innerHTML;
            ttsState.element = cardEl;
            cardEl.classList.add('tts-reading');
            snippetEl.innerHTML = snippetEl.innerHTML.replace(/(<mark[^>]*>.*?<\/mark>)|(\S+)|(\s+)/gi, function(m, mark) {
                if (mark) return mark;
                return '<span class="tts-w">' + m + '</span>';
            });
            var lang = detectLanguage(text);

            if (!ttsState.audioCtx) ttsState.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if (ttsState.audioCtx.state === 'suspended') ttsState.audioCtx.resume();

    // Start browser speech IMMEDIATELY (preserves user gesture for iOS speechSynthesis)
    speakBrowser({ text: text, lang: lang });

    var words = snippetEl.querySelectorAll('.tts-w');

    var ttsStarted = false;
    function doPlaySE(buffer, timepoints) {
        if (ttsStarted) return;
        ttsStarted = true;
        if (window.speechSynthesis) speechSynthesis.cancel();
        function start(audioBuffer) {
            ttsState.source = ttsState.audioCtx.createBufferSource();
            ttsState.source.buffer = audioBuffer;
            ttsState.source.connect(ttsState.audioCtx.destination);
            var tpIdx = 0;
            var startTime = ttsState.audioCtx.currentTime;
            ttsState.interval = setInterval(function() {
                if (!ttsState.playing) { clearInterval(ttsState.interval); return; }
                var elapsed = ttsState.audioCtx.currentTime - startTime;
                while (tpIdx < timepoints.length && elapsed >= timepoints[tpIdx].timeSeconds) {
                    words.forEach(function(w) { w.classList.remove('tts-active'); });
                    if (tpIdx < words.length) words[tpIdx].classList.add('tts-active');
                    tpIdx++;
                }
            }, 50);
            ttsState.source.onended = function() { clearInterval(ttsState.interval); stopSearchTTS(); };
            ttsState.source.start(0);
        }
        if (buffer instanceof AudioBuffer) { start(buffer); }
        else { ttsState.audioCtx.decodeAudioData(buffer, start, function() { stopSearchTTS(); }); }
    }

    function estimateTimepointsSE(duration) {
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

    var apiUrl = document.getElementById('ttsApiUrl').value;
    fetch(apiUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ text: text, lang: lang })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.fallback) return;
        if (data.error) { console.warn('Server TTS failed:', data); return; }
        var binary = atob(data.audioContent);
        var len = binary.length;
        var bytes = new Uint8Array(len);
        for (var i = 0; i < len; i++) bytes[i] = binary.charCodeAt(i);
        doPlaySE(bytes.buffer, data.timepoints || []);
    })
    .catch(function(e) { console.warn('Server TTS fetch failed:', e); });

    // 2. Try browser WebSocket Edge TTS (Microsoft, no API key needed)
    browserEdgeTTS(text, lang).then(function(result) {
        if (ttsStarted) return;
        ttsStarted = true;
        speechSynthesis.cancel();
        doPlaySE(result.audio, result.timepoints);
    }).catch(function(e) { console.warn('EdgeTTS error:', e); });


        },
        init() {
            window.addEventListener('scroll', () => {
                const navbar = document.getElementById('navbarWrapper');
                this.navbarHidden = navbar?.classList.contains('-translate-y-full') ?? false;
            }, { passive: true });
        },

        async doSearch() {
            stopSearchTTS();
            if (this.query.trim().length < 2) {
                this.results = [];
                return;
            }
            this.isSearching = true;
            try {
                var res = await fetch('<?= url('/api/search-books/all') ?>?q=' + encodeURIComponent(this.query));
                var data = await res.json();
                this.results = data.results || [];
            } catch (e) {
                console.error('Search error', e);
                this.results = [];
            }
            this.isSearching = false;
        },

        clearSearch() {
            this.query = '';
            this.results = [];
            this.$refs.searchInput.focus();
        },

        highlightText(text, q) {
            if (!text || !q) return this.escapeHtml(text || '');
            var escaped = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            var re = new RegExp('(' + escaped + ')', 'gi');
            return this.escapeHtml(text).replace(re, '<mark class="bg-yellow-200 text-inherit px-0.5 rounded">$1</mark>');
        },

        escapeHtml(str) {
            var div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    };
}
</script> 
 