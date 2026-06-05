<div x-data="docxBookApp()" class="max-w-4xl mx-auto">
    <div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-xl border border-white/20 overflow-hidden">
        <div class="p-4 sm:p-6 bg-[#795548] text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold Lao-font"><?= htmlspecialchars($info['title']) ?></h1>
                    <p class="text-white/70 text-sm mt-1"><?= $info['totalPages'] ?> ໜ້າ</p>
                </div>
                <a href="<?= url('/search-books') ?>" class="p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors text-white/70 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </a>
            </div>
        </div> 

        <div class="p-4 sm:p-6">
            <div class="relative mb-4">
                <input x-ref="searchInput"
                       x-model="query"
                       @input.debounce.300ms="doSearch()"
                       @keydown.enter="doSearch()"
                       type="search"
                       placeholder="ຄົ້ນຫາ..."
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#795548] focus:ring-2 focus:ring-[#795548]/20 outline-none text-base Lao-font transition-all">
                <button x-show="query" @click="query=''; results=[]" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div x-show="isSearching" class="flex justify-center py-12" x-cloak>
                <div class="loader-circle"></div>
            </div>

            <div x-show="!isSearching && query.length >= 2 && results.length === 0" class="py-12 text-center text-gray-500 Lao-font">
                ບໍ່ພົບຂໍ້ມູນສໍາລັບ "<span x-text="query" class="font-bold"></span>"
            </div>

            <div x-show="!isSearching && query.length >= 2 && results.length > 0" class="space-y-2">
                <p class="text-sm text-gray-400 mb-3">ພົບ <span x-text="results.length"></span> ຜົນການຄົ້ນຫາ</p>
                <template x-for="r in results" :key="r.page">
                    <div class="block p-4 rounded-xl hover:bg-[#795548]/10 transition-colors border border-transparent hover:border-[#795548]/20 cursor-default">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 rounded-lg bg-[#795548] text-white text-xs font-bold">ໜ້າ <span x-text="r.page"></span></span>
                            <span class="text-xs text-gray-400" x-text="r.matches + ' ຄັ້ງ'"></span>
                        </div>
                        <p class="result-snippet text-sm text-gray-600 Lao-font leading-relaxed line-clamp-3" x-html="highlightText(r.snippet, query)"></p>
                        <div class="mt-2 flex gap-2">
                            <button @click.stop="speakResult(r, $el.parentElement.parentElement)" class="p-1.5 rounded-lg bg-gray-100 hover:bg-[#795548]/20 transition-colors text-gray-400 hover:text-[#795548]" title="ອ່ານອອກສຽງ">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M17.95 6.05a8 8 0 010 11.9M11 5L6 9H2v6h4l5 4V5z" />
                                </svg>
                            </button>
                            <a :href="'<?= url('/search-books/' . $slug . '/page') ?>' + '/' + r.page + '?q=' + encodeURIComponent(query)"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-[#795548] text-white text-xs font-bold hover:bg-[#5E412D] transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                ເບິ່ງລາຍລະອຽດ
                            </a>
                        </div>
                    </div>
                </template>
            </div>

            <div x-show="query.length < 2 && !isSearching" class="py-8 text-center text-gray-400 Lao-font">
                <p>ພິມຄຳສຳຄັນເພື່ອຄົ້ນຫາ</p>
            </div>
        </div>
    </div>

    <style>
        .loader-circle {
            width: 40px; height: 40px;
            border: 4px solid rgba(121,85,72,.12);
            border-top-color: #795548;
            border-right-color: #b08870;
            border-radius: 50%;
            animation: spin-circle .7s cubic-bezier(.42,0,.58,1) infinite;
            box-shadow: 0 0 16px rgba(121,85,72,.08);
        }
        @keyframes spin-circle {
            to { transform: rotate(360deg); }
        }
    </style>
    <style>
        .tts-reading { box-shadow: 0 0 0 2px #79554840, 0 0 20px #79554820 !important; border-radius: 12px !important; transition: box-shadow .2s; }
        .tts-w { transition: background .15s, color .15s; border-radius: 2px; }
        .tts-w.tts-active { background: #fbbf24; color: #1a1a1a; box-shadow: 0 1px 3px #00000020; }
    </style>
    <script>
let docxTTSState = { playing: false, element: null, origHTML: null, audioCtx: null, source: null, interval: null };

function stopDocxTTS() {
    if (docxTTSState.source) { try { docxTTSState.source.stop(); } catch(e) {} docxTTSState.source = null; }
    if (docxTTSState.interval) { clearInterval(docxTTSState.interval); docxTTSState.interval = null; }
    docxTTSState.playing = false;
    if (docxTTSState.element) {
        docxTTSState.element.classList.remove('tts-reading');
        if (docxTTSState.origHTML) { var s = docxTTSState.element.querySelector('.result-snippet'); if (s) s.innerHTML = docxTTSState.origHTML; }
    }
    docxTTSState.element = null;
    docxTTSState.origHTML = null;
}
 
    function docxBookApp() {
        return {
            query: '',
            results: [],
            isSearching: false,

            speakResult(r, cardEl) {
                if (docxTTSState.playing && docxTTSState.element === cardEl) { stopDocxTTS(); return; }
                stopDocxTTS();

                var snippetEl = cardEl.querySelector('.result-snippet');
                if (!snippetEl) return;
                var text = snippetEl.textContent.replace(/\s+/g, ' ').trim();
                if (!text) return;
                docxTTSState.origHTML = snippetEl.innerHTML;
                docxTTSState.element = cardEl;
                cardEl.classList.add('tts-reading');
                snippetEl.innerHTML = snippetEl.innerHTML.replace(/(<mark[^>]*>.*?<\/mark>)|(\S+)|(\s+)/gi, function(m, mark) {
                    if (mark) return mark;
                    return '<span class="tts-w">' + m + '</span>';
                });
                var lao = (text.match(/[\u{0E80}-\u{0EFF}]/gu) || []).length;
                var thai = (text.match(/[\u{0E00}-\u{0E7F}]/gu) || []).length;
                var eng = (text.match(/[a-zA-Z]/g) || []).length;
                var lang = (lao > thai && lao > eng) ? 'lo-LA' : (thai > lao && thai > eng) ? 'th-TH' : 'en-US';

                if (!docxTTSState.audioCtx) docxTTSState.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                if (docxTTSState.audioCtx.state === 'suspended') docxTTSState.audioCtx.resume();

    // Start browser speech IMMEDIATELY (preserves user gesture for iOS speechSynthesis)
    speakBrowser({ text: text, lang: lang });

    var words = snippetEl.querySelectorAll('.tts-w');

    var ttsStarted = false;
    function doPlaySE(buffer, timepoints) {
        if (ttsStarted) return;
        ttsStarted = true;
        if (window.speechSynthesis) speechSynthesis.cancel();
        function start(audioBuffer) {
            docxTTSState.source = docxTTSState.audioCtx.createBufferSource();
            docxTTSState.source.buffer = audioBuffer;
            docxTTSState.source.connect(docxTTSState.audioCtx.destination);
            var tpIdx = 0;
            var startTime = docxTTSState.audioCtx.currentTime;
            docxTTSState.interval = setInterval(function() {
                if (!docxTTSState.playing) { clearInterval(docxTTSState.interval); return; }
                var elapsed = docxTTSState.audioCtx.currentTime - startTime;
                while (tpIdx < timepoints.length && elapsed >= timepoints[tpIdx].timeSeconds) {
                    words.forEach(function(w) { w.classList.remove('tts-active'); });
                    if (tpIdx < words.length) words[tpIdx].classList.add('tts-active');
                    tpIdx++;
                }
            }, 50);
            docxTTSState.source.onended = function() { clearInterval(docxTTSState.interval); stopDocxTTS(); };
            docxTTSState.source.start(0);
        }
        if (buffer instanceof AudioBuffer) { start(buffer); }
        else { docxTTSState.audioCtx.decodeAudioData(buffer, start, function() { stopDocxTTS(); }); }
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

            async doSearch() {
                stopDocxTTS();
                if (this.query.trim().length < 2) {
                    this.results = [];
                    return;
                }
                this.isSearching = true;
                try {
                    var res = await fetch('<?= url('/api/search-books/search') ?>?book=<?= $slug ?>&q=' + encodeURIComponent(this.query));
                    var data = await res.json();
                    this.results = data.results || [];
                } catch (e) {
                    console.error('Search error', e);
                    this.results = [];
                }
                this.isSearching = false;
            },

            highlightText(text, q) {
                if (!text || !q) return this.escapeHtml(text || '');
                var escaped = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                var re = new RegExp('(' + escaped + ')', 'gi');
                return this.escapeHtml(text).replace(re, '<mark class="pdf-highlight-snippet">$1</mark>');
            },

            escapeHtml(str) {
                var div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }
        };
    }
    </script>
</div>
