<article class="max-w-4xl mx-auto p-2 sm:p-6"> 
    <div class="bg-white/95 backdrop-blur-md rounded-2xl sm:rounded-3xl shadow-2xl overflow-hidden border border-white/20 ring-1 ring-black/5" style="touch-action:pan-y"> 
        <!-- Header --> 
        <div class="p-4 sm:p-6 bg-[#795548] text-white">
            <div class="flex justify-between items-start gap-4">
                <div class="min-w-0 flex-1">
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-bold leading-tight Lao-font">
                        <?= htmlspecialchars($info['title']) ?>
                    </h1>
                    <p class="text-white/80 mt-2 flex items-center gap-1 sm:gap-2 text-xs sm:text-base Lao-font flex-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg> 
                        <span>ໜ້າ</span>
                        <select id="pageSelector" class="bg-white/20 text-white border border-white/30 rounded px-1 py-0.5 text-xs sm:text-sm cursor-pointer focus:outline-none focus:ring-1 focus:ring-white/50 appearance-none">
                            <?php for ($i = 1; $i <= $info['totalPages']; $i++): ?>
                            <option value="<?= url('/search-books/' . $slug . '/page/' . $i) . ($query ? '?q=' . urlencode($query) : '') ?>" class="text-gray-800" <?= $i === $page['page'] ? 'selected' : '' ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                        <span>/ <?= $info['totalPages'] ?></span>
                    </p>
                </div>
            </div>
            <div class="flex justify-between items-center mt-3">
                <div class="flex items-center gap-1 sm:gap-2">
                    <button onclick="changeFontSize(-2)" class="px-2 sm:px-3 py-1.5 sm:py-2 rounded-lg sm:rounded-xl bg-white/10 hover:bg-white/20 text-white/70 hover:text-white font-bold transition-colors text-xs sm:text-sm">A-</button>
                    <button onclick="changeFontSize(2)" class="px-2 sm:px-3 py-1.5 sm:py-2 rounded-lg sm:rounded-xl bg-white/10 hover:bg-white/20 text-white/70 hover:text-white font-bold transition-colors text-xs sm:text-sm">A+</button>
                </div>
                <div class="flex items-center gap-1 sm:gap-2">
                    <?php if (isset($info['pdfFile'])): ?>
                    <a href="<?= url($info['pdfFile']) ?>" target="_blank" class="p-1.5 sm:p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors text-white/70 hover:text-white" title="ດາວໂຫລດ PDF">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </a>
                    <?php endif; ?>
                    <button id="ttsBtn" onclick="toggleTTS()" class="p-1.5 sm:p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors text-white/70 hover:text-white" title="ອ່ານອອກສຽງ">
                        <svg id="ttsIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M17.95 6.05a8 8 0 010 11.9M11 5L6 9H2v6h4l5 4V5z" />
                        </svg>
                    </button>
                    <div id="favoriteBtn" data-id="<?= $slug ?>-p<?= $page['page'] ?>" data-title="<?= addslashes($info['title']) ?> - ໜ້າ <?= $page['page'] ?>" data-url="<?= url('/search-books/' . $slug . '/page/' . $page['page']) . ($query ? '?q=' . urlencode($query) : '') ?>">
                        <button onclick="toggleFav()" class="p-1.5 sm:p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors text-white/50">
                            <svg id="favIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /> 
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div> 
 
        <!-- Content -->
        <div class="px-6 sm:px-16 md:px-24 py-8 sutra-content text-lg sm:text-xl md:text-2xl leading-loose text-gray-800 Lao-font min-h-[300px]" id="pageText">
            <?php if ($query): ?>
                <p class="text-sm text-gray-400 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    ຄົ້ນຫາ: "<strong><?= htmlspecialchars($query) ?></strong>"
                </p>
            <?php endif; ?>
            <?php
                $text = $page['text'];
                $isPdf = ($info['type'] ?? 'pdf') === 'pdf';
                $paragraphs = explode("\n", $text);
                $isToc = preg_match('/^(ສາລະບານ|สารบัญ)/mu', $text) === 1;
                if (!$isToc) {
                    $tocMatches = 0;
                    $totalLines = 0;
                    foreach ($paragraphs as $para) {
                        $para = trim(preg_replace('/\s+/', ' ', $para));
                        if (empty($para)) continue;
                        $totalLines++;
                        if (preg_match('/^(.*?)[\s\.…]+(\d+)$/u', $para)) $tocMatches++;
                    }
                    if ($totalLines > 0 && ($tocMatches / $totalLines) >= 0.7) $isToc = true;
                }
                if ($query) {
                    $escaped = preg_quote($query, '/');
                    $highlight = function ($t) use ($escaped) {
                        return preg_replace('/(' . $escaped . ')/iu', '<mark class="pdf-highlight-snippet">$1</mark>', $t);
                    };
                } else {
                    $highlight = function ($t) { return $t; };
                }
            ?>
            <div class="space-y-3 <?= $isToc ? 'toc-page' : '' ?>">
                <?php if ($isToc): ?>
                    <?php foreach ($paragraphs as $para): ?>
                        <?php $para = trim(preg_replace('/\s+/', ' ', $para)); if (empty($para)) continue; ?>
                        <?php if (preg_match('/^ສາລະບານ/u', $para) === 1 || preg_match('/^สารบัญ/u', $para) === 1): ?>
                            <h2 class="toc-title"><?= $highlight(htmlspecialchars($para)) ?></h2>
                        <?php else: ?>
                            <?php if (preg_match('/^(.*?)[\s\.…]+(\d+)$/u', $para, $m)): ?>
                                <?php
                                    $viewerPage = max(1, intval($m[2]) + $tocOffset);
                                    $fullTitle = trim($m[1]);
                                    $chapterNum = '';
                                    if (preg_match('/^(.+?)\s*\|\s*(\d+)$/u', $fullTitle, $parts)) {
                                        $chapterTitle = trim($parts[1]);
                                        $chapterNum = $parts[2];
                                    } else {
                                        $chapterTitle = $fullTitle;
                                    }
                                ?>
                                <a href="<?= url('/search-books/' . $slug . '/page/' . $viewerPage) . ($query ? '?q=' . urlencode($query) : '') ?>" class="toc-entry no-underline hover:bg-[#f5f0ea] rounded-lg transition-colors px-2 sm:px-3 -mx-2 sm:-mx-3 py-0.5">
                                    <span class="toc-chapter"><?= $highlight(htmlspecialchars($chapterTitle)) ?></span>
                                    <?php if ($chapterNum): ?>
                                    <span class="toc-badge"><?= htmlspecialchars($chapterNum) ?></span>
                                    <?php endif; ?>
                                    <span class="toc-dots"></span>
                                    <span class="toc-page-num"><?= $viewerPage ?></span>
                                </a>
                            <?php else: ?>
                                <p><?= $highlight(htmlspecialchars($para)) ?></p>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php foreach ($paragraphs as $para): ?>
                        <?php $para = trim(preg_replace('/\s+/', ' ', $para)); if (empty($para)) continue; ?>
                        <p><?= $highlight(htmlspecialchars($para)) ?></p>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Navigation -->
        <div id="navFooter" class="px-4 sm:px-6 py-4 flex justify-between items-center bg-gray-50/50 border-t border-gray-100">
            <div class="flex-1">
                <?php if ($prevPage): ?>
                    <a href="<?= url('/search-books/' . $slug . '/page/' . $prevPage) . ($query ? '?q=' . urlencode($query) : '') ?>" class="flex items-center gap-1 text-[#795548] font-bold Lao-font hover:underline group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        ກ່ອນໜ້າ
                    </a>
                <?php endif; ?>
            </div>
            <div id="pageNumDisplay" class="text-sm text-gray-400">ໜ້າ <?= $page['page'] ?></div>
            <div class="flex-1 flex justify-end">
                <?php if ($nextPage): ?>
                    <a href="<?= url('/search-books/' . $slug . '/page/' . $nextPage) . ($query ? '?q=' . urlencode($query) : '') ?>" class="flex items-center gap-1 text-[#795548] font-bold Lao-font hover:underline group text-right">
                        ຕໍ່ໄປ
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</article>
 
<div id="pageLoader"><div class="spinner"></div></div>

<script>
let currentFontSize = parseInt(localStorage.getItem('buddhaword_fontsize') || '20', 10);
let isLoading = false;
let ttsPlaying = false;
let ttsOrigHTML = null;
let ttsAudioCtx = null;
let ttsSource = null;
let ttsInterval = null;

function detectLanguage(text) {
    var laoCount = (text.match(/[\u{0E80}-\u{0EFF}]/gu) || []).length;
    var thaiCount = (text.match(/[\u{0E00}-\u{0E7F}]/gu) || []).length;
    var engCount = (text.match(/[a-zA-Z]/g) || []).length;
    if (laoCount > thaiCount && laoCount > engCount) return 'lo-LA';
    if (thaiCount > laoCount && thaiCount > engCount) return 'th-TH';
    return 'en-US';
}

function getPageText() {
    var el = document.getElementById('pageText');
    if (!el) return '';
    var text = el.innerText || el.textContent || '';
    return text.replace(/\s+/g, ' ').trim();
}

function stopTTS() {
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
        var el = document.getElementById('pageText');
        if (el) el.innerHTML = ttsOrigHTML;
        ttsOrigHTML = null;
    }
}

function toggleTTS() {
    if (ttsPlaying) {
        stopTTS();
        return;
    }
    var text = getPageText();
    if (!text) return;

    stopTTS();

    var lang = detectLanguage(text);
    var pageTextEl = document.getElementById('pageText');
    if (!pageTextEl) return;

    // Wrap words for highlighting
    ttsOrigHTML = pageTextEl.innerHTML;
    pageTextEl.innerHTML = pageTextEl.innerHTML.replace(/(<[^>]+>)|(\S+)|(\s+)/gi, function(m, tag) {
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

    // Truncate long text for faster synthesis on shared hosting
    if (text.length > 1800) {
        var idx = text.lastIndexOf('.', 1800);
        if (idx < 0) idx = text.lastIndexOf(' ', 1800);
        if (idx > 0) text = text.substring(0, idx + 1);
        else text = text.substring(0, 1800);
    }

    if (!ttsAudioCtx) ttsAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
    if (ttsAudioCtx.state === 'suspended') ttsAudioCtx.resume();

    // Start browser speech IMMEDIATELY (preserves user gesture for iOS speechSynthesis)
    speakBrowser({ text: text, lang: lang });

    var words = pageTextEl.querySelectorAll('.tts-w');

    var ttsStarted = false;
    function doPlay(buffer, timepoints) {
        if (!ttsPlaying || ttsStarted) return;
        ttsStarted = true;
        if (window.speechSynthesis) speechSynthesis.cancel();
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

    // 1. Try server TTS API first (Google Translate TTS fallback on InfinityFree)
    var apiUrl = document.getElementById('ttsApiUrl').value;
    fetch(apiUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ text: text, lang: lang })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.error) { console.warn('Server TTS failed:', data); return; }
        if (!data.audioContent) return;
        var binary = atob(data.audioContent);
        var len = binary.length;
        var bytes = new Uint8Array(len);
        for (var i = 0; i < len; i++) bytes[i] = binary.charCodeAt(i);
        doPlay(bytes.buffer, data.timepoints || []);
    })
    .catch(function(e) {
        console.warn('Server TTS failed, trying browser Edge TTS:', e);
        browserEdgeTTS(text, lang)
        .then(function(result) {
            doPlay(result.audio, result.timepoints || []);
        })
        .catch(function(err) { console.warn('Browser Edge TTS also failed:', err); });
    });


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

function toggleFav() {
    var btn = document.getElementById('favoriteBtn');
    if (!btn) return;
    var id = btn.getAttribute('data-id');
    var title = btn.getAttribute('data-title');
    var url = btn.getAttribute('data-url');
    var favorites = JSON.parse(localStorage.getItem('buddhaword_favorites') || '[]');
    var idx = favorites.findIndex(function(f) { return f.ID === id; });
    if (idx > -1) {
        favorites.splice(idx, 1);
    } else {
        favorites.push({ ID: id, title: title, url: url });
    }
    localStorage.setItem('buddhaword_favorites', JSON.stringify(favorites));
    updateFavIcon();
}

function updateFavIcon() {
    var btn = document.getElementById('favoriteBtn');
    if (!btn) return;
    var id = btn.getAttribute('data-id');
    var favorites = JSON.parse(localStorage.getItem('buddhaword_favorites') || '[]');
    var isFav = favorites.some(function(f) { return f.ID === id; });
    var icon = document.getElementById('favIcon');
    if (icon) icon.setAttribute('fill', isFav ? 'currentColor' : 'none');
    var button = btn.querySelector('button');
    if (button) {
        var base = 'p-1.5 sm:p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors';
        button.className = base + ' ' + (isFav ? 'text-red-400' : 'text-white/50');
    }
}

function changeFontSize(delta) {
    currentFontSize = Math.min(Math.max(12, currentFontSize + delta), 40);
    const textEl = document.getElementById('pageText');
    if (textEl) textEl.style.fontSize = currentFontSize + 'px';
    localStorage.setItem('buddhaword_fontsize', currentFontSize.toString());
}

(function() {
    const textEl = document.getElementById('pageText');
    if (textEl) textEl.style.fontSize = currentFontSize + 'px';
    updateFavIcon();
})();

function showLoader() {
    document.getElementById('pageLoader').classList.add('active');
    document.getElementById('pageText').classList.add('swapping');
}
function hideLoader() {
    document.getElementById('pageLoader').classList.remove('active');
    document.getElementById('pageText').classList.remove('swapping');
}

function navigateTo(url) {
    if (isLoading || !url) return;
    stopTTS();
    isLoading = true;
    showLoader();

    fetch(url)
        .then(function(r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.text();
        })
        .then(function(html) {
            var parser = new DOMParser();
            var doc = parser.parseFromString(html, 'text/html');

            var newContent = doc.getElementById('pageText');
            var oldContent = document.getElementById('pageText');
            if (!newContent || !oldContent) {
                window.location.href = url;
                return;
            }
            oldContent.innerHTML = newContent.innerHTML;
            oldContent.style.fontSize = currentFontSize + 'px';

            var newSel = doc.getElementById('pageSelector');
            var oldSel = document.getElementById('pageSelector');
            if (newSel && oldSel) {
                oldSel.innerHTML = newSel.innerHTML;
                oldSel.selectedIndex = newSel.selectedIndex;
            }

            var curNav = document.getElementById('navFooter');
            var nav = doc.getElementById('navFooter');
            if (curNav && nav) {
                curNav.innerHTML = nav.innerHTML;
                bindNavigation(curNav);
            }

            var newFav = doc.getElementById('favoriteBtn');
            var oldFav = document.getElementById('favoriteBtn');
            if (newFav && oldFav) {
                oldFav.setAttribute('data-id', newFav.getAttribute('data-id'));
                oldFav.setAttribute('data-title', newFav.getAttribute('data-title'));
                oldFav.setAttribute('data-url', newFav.getAttribute('data-url'));
                updateFavIcon();
            }

            history.pushState({}, '', url);

            bindNavigation(document.getElementById('pageText'));
            bindSwipe();
        })
        .catch(function() {
            window.location.href = url;
        })
        .finally(function() {
            isLoading = false;
            hideLoader();
        });
}

function bindNavigation(root) {
    root = root || document;
    root.querySelectorAll('a').forEach(function(a) {
        if (a.href && a.href.indexOf('/page/') > -1) {
            a.addEventListener('click', function(e) {
                if (e.ctrlKey || e.metaKey || e.shiftKey) return;
                e.preventDefault();
                navigateTo(this.href);
            });
        }
    });

    if (root === document) {
        var sel = document.getElementById('pageSelector');
        if (sel) {
            sel.addEventListener('change', function() {
                if (this.value) navigateTo(this.value);
            });
        }
    }
}

function bindSwipe() {
    var card = document.querySelector('article > div');
    if (!card || card.getAttribute('data-swipe-bound') === 'true') return;
    var startX = 0, startY = 0;

    var touchHandler = function(e) {
        if (e.type === 'touchstart') {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
        } else if (e.type === 'touchmove') {
            var dx = e.touches[0].clientX - startX;
            var dy = e.touches[0].clientY - startY;
            if (Math.abs(dx) > 20 && Math.abs(dx) > Math.abs(dy) * 2) {
                card.style.transform = 'scale(0.97)';
                card.style.transition = 'transform 0.15s ease';
            }
        } else if (e.type === 'touchend') {
            card.style.transform = '';
            card.style.transition = 'transform 0.25s ease';
            var dx = e.changedTouches[0].clientX - startX;
            var dy = e.changedTouches[0].clientY - startY;
            if (Math.abs(dx) >= 50 && Math.abs(dy) < Math.abs(dx) * 0.5) {
                var nav = document.getElementById('navFooter');
                if (!nav) return;
                var swipeLinks = nav.querySelectorAll('a');
                var swipePrev = null, swipeNext = null;
                swipeLinks.forEach(function(a) {
                    if (a.textContent.includes('ກ່ອນ')) swipePrev = a.href;
                    if (a.textContent.includes('ຕໍ່')) swipeNext = a.href;
                });
                if (dx > 0 && swipePrev) navigateTo(swipePrev);
                else if (dx < 0 && swipeNext) navigateTo(swipeNext);
            }
        }
    };

    card.addEventListener('touchstart', touchHandler, { passive: true });
    card.addEventListener('touchmove', touchHandler, { passive: true });
    card.addEventListener('touchend', touchHandler, { passive: true });
    card.setAttribute('data-swipe-bound', 'true');
}

window.addEventListener('popstate', function() {
    window.location.reload();
});

bindNavigation();
bindSwipe();
</script>
 