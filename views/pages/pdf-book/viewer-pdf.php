<div x-data="pdfBookApp()" class="max-w-7xl mx-auto">
    <div class="flex flex-col lg:flex-row gap-4 min-h-[80vh]">
        <!-- Search Panel --> 
        <div class="w-full lg:w-96 flex-shrink-0">
            <div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-xl border border-white/20 overflow-hidden sticky top-[70px]">
                <div class="p-4 bg-[#795548] text-white">
                    <h2 class="text-lg font-bold Lao-font"><?= htmlspecialchars($info['title']) ?></h2>
                    <p class="text-white/70 text-sm"><?= $info['totalPages'] ?> ໜ້າ</p>
                </div>

                <div class="p-3">
                    <div class="relative">
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
                </div>

                <!-- Loading -->
                <div x-show="isSearching" class="flex justify-center p-6" x-cloak>
                    <div class="loader-circle"></div>
                </div>

                <!-- Results -->
                <div x-show="!isSearching && query.length >= 2 && results.length === 0" class="p-6 text-center text-gray-500 text-sm Lao-font">
                    ບໍ່ພົບຂໍ້ມູນສໍາລັບ "<span x-text="query" class="font-bold"></span>"
                </div>

                <div x-show="!isSearching && query.length >= 2 && results.length > 0" class="px-2 pb-2">
                    <div class="text-xs text-gray-400 px-2 py-1">
                        ພົບ <span x-text="results.length"></span> ຜົນການຄົ້ນຫາ
                    </div>
                    <div class="overflow-y-auto max-h-[60vh] space-y-1">
                        <template x-for="r in results" :key="r.page">
                            <div class="w-full p-3 rounded-xl hover:bg-[#795548]/10 transition-colors border border-transparent hover:border-[#795548]/20 cursor-pointer"
                                 :class="currentPage === r.page ? 'bg-[#795548]/10 border-[#795548]/30' : ''"
                                 @click="goToPage(r.page)">
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
                                    <a :href="'<?= url('/search-books') ?>' + '/' + '<?= $slug ?>' + '/page/' + r.page + '?q=' + encodeURIComponent(query)"
                                       @click.stop
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-[#795548] text-white text-xs font-bold hover:bg-[#5E412D] transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        ເບິ່ງລາຍລະອຽດ
                                    </a>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- PDF Viewer -->
        <div class="flex-1 min-w-0">
            <!-- Page Controls -->
            <div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-xl border border-white/20 overflow-hidden mb-4">
                <div class="p-3 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <button @click="prevPage()" :disabled="currentPage <= 1" class="p-2 rounded-lg hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition-colors" :class="currentPage <= 1 ? 'text-gray-300' : 'text-[#795548]'">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <span class="text-sm font-bold text-gray-700 whitespace-nowrap">
                            ໜ້າ <span x-text="currentPage"></span> / <span x-text="totalPages"></span>
                        </span>
                        <button @click="nextPage()" :disabled="currentPage >= totalPages" class="p-2 rounded-lg hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition-colors" :class="currentPage >= totalPages ? 'text-gray-300' : 'text-[#795548]'">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <input x-model.number="pageInput" @keydown.enter="goToPage(pageInput)" type="number" class="w-16 text-center px-2 py-1 rounded-lg border border-gray-200 text-sm outline-none focus:border-[#795548]" min="1" :max="totalPages">
                        <button @click="goToPage(pageInput)" class="px-3 py-1 rounded-lg bg-[#795548] text-white text-sm font-bold hover:bg-[#5E412D] transition-colors">ໄປ</button>
                    </div>
                    <a :href="pdfUrl" target="_blank" class="p-2 rounded-lg hover:bg-gray-100 text-gray-500 transition-colors" title="ດາວໂຫລດ PDF">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </a>
                </div>
            </div>

            <!-- PDF Canvas -->
            <div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-xl border border-white/20 overflow-hidden">
                <div x-show="isLoading" class="flex justify-center items-center py-32" x-cloak>
                    <div class="loader-circle"></div>
                </div>
                <div id="pdfContainer" class="relative flex justify-center bg-gray-100 min-h-[400px]" :class="isLoading ? 'hidden' : ''">
                    <canvas id="pdfCanvas" class="shadow-xl max-w-full"></canvas>
                    <div id="highlightLayer" class="absolute inset-0 pointer-events-none" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>

    <style>
        #pdfContainer { position: relative; }
        #pdfCanvas { display: block; }
        .pdf-highlight {
            position: absolute;
            background: rgba(255, 230, 0, 0.45);
            border-radius: 2px;
            pointer-events: none;
            mix-blend-mode: multiply;
        }
        mark.pdf-highlight-snippet {
            background: #fde68a;
            color: inherit;
            padding: 0 2px;
            border-radius: 2px;
        }
        .tts-reading { box-shadow: 0 0 0 2px #79554840, 0 0 20px #79554820 !important; border-radius: 12px !important; transition: box-shadow .2s; }
        .tts-w { transition: background .15s, color .15s; border-radius: 2px; }
        .tts-w.tts-active { background: #fbbf24; color: #1a1a1a; box-shadow: 0 1px 3px #00000020; }
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
</div>
 
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.0.379/pdf.min.js"></script>
<script>
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.0.379/pdf.worker.min.js';

let pdfTTSState = { playing: false, element: null, origHTML: null, audioCtx: null, source: null, interval: null };

function stopPdfTTS() {
    if (pdfTTSState.source) { try { pdfTTSState.source.stop(); } catch(e) {} pdfTTSState.source = null; }
    if (pdfTTSState.interval) { clearInterval(pdfTTSState.interval); pdfTTSState.interval = null; }
    pdfTTSState.playing = false;
    if (pdfTTSState.element) {
        pdfTTSState.element.classList.remove('tts-reading');
        if (pdfTTSState.origHTML) { var s = pdfTTSState.element.querySelector('.result-snippet'); if (s) s.innerHTML = pdfTTSState.origHTML; }
    }
    pdfTTSState.element = null;
    pdfTTSState.origHTML = null;
}

function pdfBookApp() {
    return {
        query: '',
        results: [],
        isSearching: false,
        currentPage: 1,
        totalPages: <?= $info['totalPages'] ?>,
        pageInput: 1,
        pdfUrl: '<?= isset($info['pdfFile']) ? url($info['pdfFile']) : '' ?>',
        pdfDoc: null,
        isLoading: false,
        scale: 1.5,
        searchQuery: '',

        speakResult(r, cardEl) {
            if (pdfTTSState.playing && pdfTTSState.element === cardEl) { stopPdfTTS(); return; }
            stopPdfTTS();

            var snippetEl = cardEl.querySelector('.result-snippet');
            if (!snippetEl) return;
            var text = snippetEl.textContent.replace(/\s+/g, ' ').trim();
            if (!text) return;
            pdfTTSState.origHTML = snippetEl.innerHTML;
            pdfTTSState.element = cardEl;
            cardEl.classList.add('tts-reading');
            snippetEl.innerHTML = snippetEl.innerHTML.replace(/(<mark[^>]*>.*?<\/mark>)|(\S+)|(\s+)/gi, function(m, mark) {
                if (mark) return mark;
                return '<span class="tts-w">' + m + '</span>';
            });
            var lao = (text.match(/[\u{0E80}-\u{0EFF}]/gu) || []).length;
            var thai = (text.match(/[\u{0E00}-\u{0E7F}]/gu) || []).length;
            var eng = (text.match(/[a-zA-Z]/g) || []).length;
            var lang = (lao > thai && lao > eng) ? 'lo-LA' : (thai > lao && thai > eng) ? 'th-TH' : 'en-US';

            if (!pdfTTSState.audioCtx) pdfTTSState.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if (pdfTTSState.audioCtx.state === 'suspended') pdfTTSState.audioCtx.resume();

    // Start browser speech IMMEDIATELY (preserves user gesture for iOS speechSynthesis)
    speakBrowser({ text: text, lang: lang });

    var words = snippetEl.querySelectorAll('.tts-w');

    var ttsStarted = false;
    function doPlaySE(buffer, timepoints) {
        if (ttsStarted) return;
        ttsStarted = true;
        if (window.speechSynthesis) speechSynthesis.cancel();
        function start(audioBuffer) {
            pdfTTSState.source = pdfTTSState.audioCtx.createBufferSource();
            pdfTTSState.source.buffer = audioBuffer;
            pdfTTSState.source.connect(pdfTTSState.audioCtx.destination);
            var tpIdx = 0;
            var startTime = pdfTTSState.audioCtx.currentTime;
            pdfTTSState.interval = setInterval(function() {
                if (!pdfTTSState.playing) { clearInterval(pdfTTSState.interval); return; }
                var elapsed = pdfTTSState.audioCtx.currentTime - startTime;
                while (tpIdx < timepoints.length && elapsed >= timepoints[tpIdx].timeSeconds) {
                    words.forEach(function(w) { w.classList.remove('tts-active'); });
                    if (tpIdx < words.length) words[tpIdx].classList.add('tts-active');
                    tpIdx++;
                }
            }, 50);
            pdfTTSState.source.onended = function() { clearInterval(pdfTTSState.interval); stopPdfTTS(); };
            pdfTTSState.source.start(0);
        }
        if (buffer instanceof AudioBuffer) { start(buffer); }
        else { pdfTTSState.audioCtx.decodeAudioData(buffer, start, function() { stopPdfTTS(); }); }
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

    // 3. StreamElements client-side fallback (only if API key configured)
    var seVoices = { 'lo-LA': 'lo-LA-KeomanyNeural', 'th-TH': 'th-TH-NiwatNeural', 'en-US': 'en-US-GuyNeural' };
    var seVoice = seVoices[lang] || 'en-US-GuyNeural';
    var seApiKey = document.getElementById('seApiKey') ? document.getElementById('seApiKey').value : '';
    if (seApiKey) {
        var seHeaders = { 'Authorization': 'Bearer ' + seApiKey };
        var chunks = [];
        for (var si = 0; si < text.length;) {
            var end = Math.min(si + 150, text.length);
            if (end < text.length) { var sp = text.lastIndexOf(' ', end); if (sp > si) end = sp; }
            chunks.push(text.substring(si, end));
            si = end;
        }
        var seBuffers = [];
        var seChain = Promise.resolve();
        chunks.forEach(function(chunk) {
            seChain = seChain.then(function() {
                return fetch('https://api.streamelements.com/kappa/v2/speech?voice=' + encodeURIComponent(seVoice) + '&text=' + encodeURIComponent(chunk), { headers: seHeaders })
                    .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.arrayBuffer(); })
                    .then(function(b) { seBuffers.push(b); })
                    .catch(function(e) { throw e; });
            });
        });
        seChain.then(function() {
            if (seBuffers.length === 0) return;
            var totalLen = seBuffers.reduce(function(s, b) { return s + b.byteLength; }, 0);
            if (totalLen < 100) return;
            var decoded = [];
            var decChain = Promise.resolve();
            seBuffers.forEach(function(buf) {
                decChain = decChain.then(function() {
                    return new Promise(function(resolve) {
                        try { pdfTTSState.audioCtx.decodeAudioData(buf, function(b) { decoded.push(b); resolve(); }, function() { resolve(); }); }
                        catch(e) { resolve(); }
                    });
                });
            });
            decChain.then(function() {
                if (decoded.length === 0) return;
                var totalLen2 = decoded.reduce(function(s, b) { return s + b.length; }, 0);
                var merged = pdfTTSState.audioCtx.createBuffer(decoded[0].numberOfChannels, totalLen2, decoded[0].sampleRate);
                var offset = 0;
                for (var di = 0; di < decoded.length; di++) {
                    for (var ch = 0; ch < merged.numberOfChannels; ch++) {
                        var src = decoded[di].getChannelData(ch);
                        merged.getChannelData(ch).set(src, offset);
                    }
                    offset += decoded[di].length;
                }
                doPlaySE(merged, estimateTimepointsSE(merged.duration));
            });
        })
        .catch(function() {});
    }
        },

        init() {
            this.loadPDF();
            this.calculateScale();
            window.addEventListener('resize', () => this.calculateScale());
        },

        calculateScale() {
            var container = document.getElementById('pdfContainer');
            if (!container) return;
            var w = container.clientWidth - 40;
            this.scale = Math.max(1, w / 612);
        },

        async loadPDF() {
            try {
                this.pdfDoc = await pdfjsLib.getDocument(this.pdfUrl).promise;
                this.renderPage(this.currentPage);
            } catch (e) {
                console.error('PDF load error', e);
            }
        },

        async renderPage(pageNum) {
            if (!this.pdfDoc) return;
            this.isLoading = true;
            this.currentPage = pageNum;
            this.pageInput = pageNum;

            try {
                var page = await this.pdfDoc.getPage(pageNum);
                var viewport = page.getViewport({ scale: this.scale });
                var canvas = document.getElementById('pdfCanvas');
                var ctx = canvas.getContext('2d');
                canvas.width = viewport.width;
                canvas.height = viewport.height;

                await page.render({ canvasContext: ctx, viewport: viewport }).promise;

                this.isLoading = false;

                if (this.searchQuery) {
                    await this.renderHighlights(pageNum, this.searchQuery, viewport);
                } else {
                    document.getElementById('highlightLayer').style.display = 'none';
                }
            } catch (e) {
                console.error('Render error', e);
                this.isLoading = false;
            }
        },

        async renderHighlights(pageNum, query, viewport) {
            var layer = document.getElementById('highlightLayer');
            if (!layer) return;
            layer.style.display = 'block';
            layer.style.width = viewport.width + 'px';
            layer.style.height = viewport.height + 'px';
            layer.innerHTML = '';

            try {
                var res = await fetch('<?= url('/api/search-books/page') ?>?book=<?= $slug ?>&n=' + pageNum + '&q=' + encodeURIComponent(query));
                var data = await res.json();
                if (!data.highlightWords || !data.highlightWords.length) return;

                var s = this.scale;
                var pageHeight = viewport.height;

                data.highlightWords.forEach(function(w) {
                    var el = document.createElement('div');
                    el.className = 'pdf-highlight';
                    el.style.left = (w.x0 * s) + 'px';
                    el.style.top = (pageHeight - w.y1 * s) + 'px';
                    el.style.width = Math.max(4, (w.x1 - w.x0) * s) + 'px';
                    el.style.height = (w.y1 - w.y0) * s + 'px';
                    layer.appendChild(el);
                });
            } catch (e) {
                console.error('Highlight error', e);
            }
        },

        async goToPage(n) {
            n = parseInt(n) || 1;
            if (n < 1) n = 1;
            if (n > this.totalPages) n = this.totalPages;
            this.searchQuery = this.query;
            await this.renderPage(n);
        },

        prevPage() {
            if (this.currentPage > 1) this.goToPage(this.currentPage - 1);
        },

        nextPage() {
            if (this.currentPage < this.totalPages) this.goToPage(this.currentPage + 1);
        },

        async doSearch() {
            stopPdfTTS();
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
