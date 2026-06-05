<!DOCTYPE html>
<html lang="lo">
<head>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="<?= htmlspecialchars($seo['robots'] ?? 'index, follow, max-snippet:-1, max-image-preview:large') ?>">
    <meta name="google-site-verification" content="9vU2hmc33cx9n-TnpddN5QVNQA7rbC9mZEK3-l6Deck">
  
    <title><?= htmlspecialchars($seo['title'] ?? 'ຄຳສອນພຸດທະ') ?></title> 
    <meta name="title" content="<?= htmlspecialchars($seo['title'] ?? 'ຄຳສອນພຸດທະ') ?>">
    <meta name="description" content="<?= htmlspecialchars($seo['description'] ?? '') ?>">
    <meta name="keywords" content="<?= htmlspecialchars($seo['keywords'] ?? '') ?>">
    <link rel="canonical" href="<?= $seo['canonical'] ?? '' ?>">
    <link rel="alternate" hreflang="lo" href="<?= $seo['canonical'] ?? '' ?>">
    <link rel="alternate" hreflang="x-default" href="<?= $seo['canonical'] ?? '' ?>">
    <link rel="icon" type="image/png" href="<?= url('buddhaword.png') ?>">

    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= $seo['canonical'] ?? '' ?>">
    <meta property="og:title" content="<?= htmlspecialchars($seo['title'] ?? 'ຄຳສອນພຸດທະ') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($seo['description'] ?? '') ?>">
    <meta property="og:image" content="<?= $seo['image'] ?? '' ?>">
    <meta property="og:locale" content="lo_LA">
    <meta property="og:site_name" content="ຄຳສອນພຸດທະ">

    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= $seo['canonical'] ?? '' ?>">
    <meta property="twitter:title" content="<?= htmlspecialchars($seo['title'] ?? 'ຄຳສອນພຸດທະ') ?>">
    <meta property="twitter:description" content="<?= htmlspecialchars($seo['description'] ?? '') ?>">
    <meta property="twitter:image" content="<?= $seo['image'] ?? '' ?>">

    <?php if (isset($seo['json_ld'])): ?>
    <?php $jsonLdItems = is_array($seo['json_ld']) && isset($seo['json_ld']['@context']) ? [$seo['json_ld']] : (is_array($seo['json_ld']) ? $seo['json_ld'] : []); ?>
    <?php foreach ($jsonLdItems as $item): ?>
    <script type="application/ld+json">
        <?= json_encode($item, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
    </script>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Resource Hints -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//sheets.googleapis.com">

    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;700&display=swap" as="style">
    <link rel="preload" href="<?= url('assets/fonts/PhetsarathOT.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= url('assets/fonts/NotoSerifLao.woff2') ?>" as="font" type="font/woff2" crossorigin>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;700&display=swap" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="<?= url('css/style.css') ?>" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="<?= url('css/sweetalert2.min.css') ?>" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;700&display=swap">
        <link rel="stylesheet" href="<?= url('css/style.css') ?>">
        <link rel="stylesheet" href="<?= url('css/sweetalert2.min.css') ?>">
    </noscript>

    <style>
        *,::before,::after{box-sizing:border-box;border-width:0;border-style:solid}
        html{line-height:1.5;-webkit-text-size-adjust:100%;tab-size:4}
        body{margin:0;font-family:'Noto Sans Lao','Phetsarath',sans-serif;background-color:#795548;background-image:url('<?= url('assets/images/wooden_background.jpg') ?>');background-attachment:fixed;background-size:cover;min-height:100vh}
        [x-cloak]{display:none!important}
        .fixed{position:fixed}.absolute{position:absolute}.relative{position:relative}.sticky{position:sticky}
        .inset-0{inset:0}.top-0{top:0}.left-0{left:0}.right-0{right:0}.z-50{z-index:50}.z-40{z-index:40}.z-30{z-index:30}.z-20{z-index:20}
        .container{width:100%;margin-left:auto;margin-right:auto}.mx-auto{margin-left:auto;margin-right:auto}
        .flex{display:flex}.grid{display:grid}.hidden{display:none}.items-center{align-items:center}.items-start{align-items:flex-start}
        .justify-center{justify-content:center}.justify-between{justify-content:space-between}
        .flex-col{flex-direction:column}.flex-1{flex:1 1 0%}.flex-shrink-0{flex-shrink:0}
        .w-full{width:100%}.h-full{height:100%}.min-h-screen{min-height:100vh}
        .text-white{color:#fff}.text-center{text-align:center}
        .bg-white{background-color:#fff}.shadow-md{box-shadow:0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -2px rgba(0,0,0,.1)}.shadow-lg{box-shadow:0 10px 15px -3px rgba(0,0,0,.1),0 4px 6px -4px rgba(0,0,0,.1)}
        .rounded-full{border-radius:9999px}.rounded-2xl{border-radius:1rem}.rounded-xl{border-radius:.75rem}
        .overflow-hidden{overflow:hidden}.overflow-y-auto{overflow-y:auto}
        .transition-all{transition-property:all;transition-timing-function:cubic-bezier(.4,0,.2,1);transition-duration:.15s}.transition-transform{transition-property:transform;transition-timing-function:cubic-bezier(.4,0,.2,1);transition-duration:.3s}.transition-colors{transition-property:color,background-color,border-color,text-decoration-color,fill,stroke;transition-timing-function:cubic-bezier(.4,0,.2,1);transition-duration:.15s}
        .object-cover{object-fit:cover}.object-contain{object-fit:contain}
        .cursor-pointer{cursor:pointer}.p-2{padding:.5rem}.p-4{padding:1rem}
        .px-4{padding-left:1rem;padding-right:1rem}.py-2{padding-top:.5rem;padding-bottom:.5rem}
        .pb-24{padding-bottom:6rem}
        .font-bold{font-weight:700}.text-lg{font-size:1.125rem;line-height:1.75rem}
        .text-sm{font-size:.875rem;line-height:1.25rem}.text-xs{font-size:.75rem;line-height:1rem}
        .opacity-0{opacity:0}.opacity-100{opacity:1}.invisible{visibility:hidden}
        .-translate-y-full{transform:translateY(-100%)}.translate-y-0{transform:translateY(0)}.translate-y-4{transform:translateY(1rem)}
        .backdrop-blur-md{backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px)}.backdrop-blur-sm{backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px)}
        .border{border-width:1px}.border-b{border-bottom-width:1px}
        .truncate,.text-ellipsis{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .aspect-square{aspect-ratio:1/1}
        .page-enter{animation:fadeIn .3s ease-out}
        @keyframes fadeIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
        ::-webkit-scrollbar{width:6px}::-webkit-scrollbar-track{background:transparent}::-webkit-scrollbar-thumb{background:rgba(121,85,72,.3);border-radius:10px}::-webkit-scrollbar-thumb:hover{background:rgba(121,85,72,.5)}
        .loader{width:36px;height:36px;border:3px solid rgba(121,85,72,.2);border-top:3px solid #795548;border-radius:50%;animation:spin .8s linear infinite}.animate-spin{animation:spin 1s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}
        .font-lao,.Lao-font{font-family:'Noto Sans Lao','Phetsarath',sans-serif}.sutra-content{font-family:'Noto Serif Lao',serif}
        .line-clamp-2{overflow:hidden;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2}
        .splash-screen{position:fixed;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;z-index:10000;background:transparent;transition:opacity .3s ease}
        .splash-screen.fade-out{opacity:0;pointer-events:none}
        @media (orientation:landscape) and (max-height:500px){.pb-24{padding-bottom:5rem}.bottom-4{bottom:2px}.bottom-20{bottom:2.5rem}}
        .splash-logos{display:flex;flex-wrap:wrap;gap:12px;justify-content:center;align-items:center;max-width:92vw;padding:20px}
        @media (max-width:767px){#splash-logos{margin-top:-450px!important}}@media (min-width:768px){#splash-logos{margin-top:-520px!important}}
        .splash-logo{width:clamp(80px,20vw,140px);height:auto;object-fit:contain;filter:drop-shadow(0 1px 2px rgba(0,0,0,.08))}
        .bg-\[\#795548\]{background-color:#795548}.hover\:bg-white\/10:hover{background-color:rgba(255,255,255,.1)}
        .h-\[50px\]{height:50px}.md\:h-\[60px\]{height:60px}
        .h-5{height:1.25rem}.w-5{width:1.25rem}.md\:h-6{height:1.5rem}.md\:w-6{width:1.5rem}.h-7{height:1.75rem}.h-8{height:2rem}.md\:h-12{height:3rem}.w-auto{width:auto}
        .tracking-tight{letter-spacing:-.025em}.whitespace-nowrap{white-space:nowrap}.min-w-0{min-width:0}
        .gap-1{gap:.25rem}.gap-2{gap:.5rem}.gap-3{gap:.75rem}.gap-4{gap:1rem}
        .md\:gap-4{gap:1rem}.md\:text-xl{font-size:1.25rem;line-height:1.75rem}
        .bg-white\/80{background-color:rgba(255,255,255,.8)}.bg-white\/90{background-color:rgba(255,255,255,.9)}
    </style> 
  
    <link rel="manifest" href="<?= url('manifest.json') ?>">
    <meta name="theme-color" content="#795548">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="ຄຳສອນພຸດທະ">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= url('buddhaword.png') ?>">
    <link rel="apple-touch-icon" sizes="152x152" href="<?= url('icons/Icon-512.png') ?>">
    <link rel="apple-touch-icon" sizes="120x120" href="<?= url('images/Icon-192.png') ?>">
    <link rel="apple-touch-icon" sizes="76x76" href="<?= url('images/Icon-192.png') ?>">
    <link rel="apple-touch-startup-image" href="<?= url('buddhaword.png') ?>">
 
    <script>
    (function() {
        var d = document, t = d.createElement('script');
        t.src = 'https://cdn.tailwindcss.com';
        t.async = true;
        t.onload = function() {
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            primary: '#DDCFBC',
                            brown: { 500: '#795548', 600: '#5E412D' }
                        }
                    }
                }
            };
        };
        var s = d.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(t, s);
    })();
    </script>

    <script>
    var ttsWords = [];
    function speakBrowser(opt) {
        if (!('speechSynthesis' in window) || !opt || !opt.text) return;
        try {
            speechSynthesis.cancel();
            var text = opt.text;
            var lang = { 'lo-LA': 'lo-LA', 'th-TH': 'th-TH', 'en-US': 'en-US' }[opt.lang] || 'lo-LA';
            ttsWords = [].slice.call(document.querySelectorAll('.tts-w'));

            var doSpeak = function() {
                var u = new SpeechSynthesisUtterance(text);
                u.lang = lang;
                u.rate = 0.85;

                var voices = speechSynthesis.getVoices();
                var match = voices.find(function(v) { return v.lang.startsWith(lang.split('-')[0]); });
                if (match) {
                    u.voice = match;
                } else {
                    // No voice for this language — browser will default to wrong voice.
                    // Skip browser speech; server audio will play when it arrives.
                    return;
                }

                if (ttsWords.length > 0) {
                    var wordRanges = [];
                    var re = /\S+/g;
                    var m;
                    while ((m = re.exec(text)) !== null) {
                        wordRanges.push({ s: m.index, e: m.index + m[0].length });
                    }
                    var wordCount = wordRanges.length;
                    var lastIdx = -1;
                    var startTime = null;

                    // Build cumulative character-position array for time-based estimation
                    var charPositions = [];
                    var totalChars = 0;
                    for (var i = 0; i < wordCount; i++) {
                        charPositions.push(totalChars);
                        totalChars += wordRanges[i].e - wordRanges[i].s;
                    }

                    u.onstart = function() {
                        startTime = performance.now();
                    };

                    // Primary: time-based estimation (works for all languages)
                    var timer = setInterval(function() {
                        if (!startTime || wordCount === 0 || !ttsWords.length) return;
                        var elapsed = performance.now() - startTime;
                        // ~8.5 chars/sec for Lao at rate 0.85
                        var estimatedChars = elapsed * 0.0085;
                        if (estimatedChars >= totalChars) {
                            estimatedChars = totalChars - 1;
                        }
                        // Binary search: find word at this character position
                        var lo = 0, hi = wordCount - 1;
                        while (lo <= hi) {
                            var mid = (lo + hi) >>> 1;
                            var wordEnd = mid + 1 < wordCount ? charPositions[mid + 1] : totalChars;
                            if (estimatedChars < charPositions[mid]) { hi = mid - 1; }
                            else if (estimatedChars >= wordEnd) { lo = mid + 1; }
                            else {
                                if (mid !== lastIdx && mid < ttsWords.length) {
                                    if (lastIdx >= 0 && lastIdx < ttsWords.length) ttsWords[lastIdx].classList.remove('tts-active');
                                    ttsWords[mid].classList.add('tts-active');
                                    lastIdx = mid;
                                }
                                return;
                            }
                        }
                    }, 40);

                    // Corrector: onboundary fires when available (may be unreliable for Lao)
                    u.onboundary = function(e) {
                        if (e.name !== 'word' || wordCount === 0) return;
                        var ci = e.charIndex;
                        var lo = 0, hi = wordCount - 1;
                        while (lo <= hi) {
                            var mid = (lo + hi) >>> 1;
                            var r = wordRanges[mid];
                            if (ci < r.s) { hi = mid - 1; }
                            else if (ci >= r.e) { lo = mid + 1; }
                            else {
                                if (mid > lastIdx && mid < ttsWords.length) {
                                    if (lastIdx >= 0 && lastIdx < ttsWords.length) ttsWords[lastIdx].classList.remove('tts-active');
                                    ttsWords[mid].classList.add('tts-active');
                                    lastIdx = mid;
                                }
                                return;
                            }
                        }
                    };

                    u.addEventListener('end', function() {
                        clearInterval(timer);
                        if (lastIdx >= 0 && lastIdx < ttsWords.length) ttsWords[lastIdx].classList.remove('tts-active');
                        ttsWords = [];
                        _restoreBrowserDOM();
                    }, { once: true });
                } else {
                    u.addEventListener('end', function() {
                        _restoreBrowserDOM();
                    }, { once: true });
                }
                speechSynthesis.speak(u);
            };

            function _restoreBrowserDOM() {
                window.ttsPlaying = false;
                var icon = document.getElementById('ttsIcon');
                if (icon) { icon.classList.remove('fa-stop'); icon.classList.add('fa-play'); }
                var btn = document.getElementById('ttsBtn');
                if (btn) {
                    btn.classList.remove('text-green-300', 'bg-green-500/20');
                    btn.classList.add('text-white/70');
                }
            }

            // iOS Safari: getVoices() returns empty until 'voiceschanged' fires.
            // Without loaded voices, no lang match is found and English is used.
            if (speechSynthesis.getVoices().length === 0) {
                var handler = function() {
                    speechSynthesis.removeEventListener('voiceschanged', handler);
                    doSpeak();
                };
                speechSynthesis.addEventListener('voiceschanged', handler);
                return;
            }
            doSpeak();
        } catch (e) { console.warn('Browser TTS error:', e); }
    }

    /* Browser WebSocket Edge TTS – silently fail if WS unavailable (Microsoft undocumented API) */
    function getXTime() {
        var d = new Date();
        var days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return days[d.getUTCDay()] + ', ' + String(d.getUTCDate()).padStart(2,'0') + ' ' + months[d.getUTCMonth()] + ' ' + d.getUTCFullYear() + ' ' + String(d.getUTCHours()).padStart(2,'0') + ':' + String(d.getUTCMinutes()).padStart(2,'0') + ':' + String(d.getUTCSeconds()).padStart(2,'0') + ' UTC';
    }

    function _uuid() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0;
            return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
        });
    }

    var _browserEdgeTTSCache = {};
    function browserEdgeTTS(text, lang) {
        var cacheKey = text.length + '|' + lang;
        if (_browserEdgeTTSCache[cacheKey]) {
            return _browserEdgeTTSCache[cacheKey];
        }
        var TRUSTED_CLIENT_TOKEN = '6A5AA1D4EAFF4E9FB37E23D68491D6F4';
        var VOICE_MAP = { 'lo-LA': 'lo-LA-KeomanyNeural', 'th-TH': 'th-TH-NiwatNeural', 'en-US': 'en-US-GuyNeural' };
        var voice = VOICE_MAP[lang] || 'en-US-GuyNeural';
        var timeout = 15;
        var p = new Promise(function(resolve, reject) {
            var rejected = false;
            var tid = setTimeout(function() { if (!rejected) { rejected = true; reject('timeout'); } }, timeout * 1000);
            function generateGec(token) {
                try {
                    if (typeof crypto === 'undefined' || !crypto.subtle) return Promise.reject('no crypto');
                    var now = Math.floor(Date.now() / 1000);
                    var t = now + 11644473600;
                    var r = t - (t % 300);
                    var w = (typeof BigInt !== 'undefined') ? (BigInt(r) * BigInt(10000000)).toString() : String(r * 10000000);
                    return crypto.subtle.digest('SHA-256', new TextEncoder().encode(w + token)).then(function(hash) {
                        return Array.from(new Uint8Array(hash)).map(function(b) { return b.toString(16).padStart(2, '0'); }).join('').toUpperCase();
                    });
                } catch(e) { return Promise.reject(e); }
            }
            var connId = (typeof crypto !== 'undefined' && crypto.randomUUID) ? crypto.randomUUID() : _uuid();
            var reqId = (typeof crypto !== 'undefined' && crypto.randomUUID) ? crypto.randomUUID() : _uuid();
            var gecVersion = '1-147.0.3882.39';
            generateGec(TRUSTED_CLIENT_TOKEN).then(function(secMsGec) {
                if (rejected) return;
                var wsUrl = 'wss://speech.platform.bing.com/consumer/speech/synthesize/readaloud/edge/v1'
                    + '?TrustedClientToken=' + TRUSTED_CLIENT_TOKEN
                    + '&ConnectionId=' + connId
                    + '&Sec-MS-GEC=' + secMsGec
                    + '&Sec-MS-GEC-Version=' + gecVersion;
                var ws = new WebSocket(wsUrl, ['synthesize']);
                ws.binaryType = 'arraybuffer';
                var audioChunks = [];
                var boundaries = [];
                ws.onopen = function() {
                    var config = { context: { synthesis: { audio: { metadataoptions: { sentenceBoundaryEnabled: 'false', wordBoundaryEnabled: 'true' }, outputFormat: 'audio-24khz-48kbitrate-mono-mp3' } } } };
                    ws.send('X-Timestamp:' + getXTime() + '\r\nContent-Type:application/json; charset=utf-8\r\nPath:speech.config\r\n\r\n' + JSON.stringify(config) + '\r\n');
                    var ssml = '<speak version="1.0" xmlns="http://www.w3.org/2001/10/synthesis" xmlns:mstts="https://www.w3.org/2001/mstts" xml:lang="' + lang + '">'
                        + '<voice name="' + voice + '"><prosody pitch="+0Hz" rate="0%" volume="0%">'
                        + text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&apos;')
                        + '</prosody></voice></speak>';
                    ws.send('X-RequestId:' + reqId + '\r\nContent-Type:application/ssml+xml\r\nX-Timestamp:' + getXTime() + '\r\nPath:ssml\r\n\r\n' + ssml);
                };
                ws.onmessage = function(event) {
                    if (rejected) return;
                    if (typeof event.data === 'string') {
                        var s2 = event.data;
                        if (s2.indexOf('Path:audio.metadata') !== -1) {
                            var ms = s2.indexOf('\r\n\r\n') + 4;
                            if (ms > 4) {
                                try {
                                    var meta = JSON.parse(s2.substring(ms));
                                    if (meta.Metadata) {
                                        meta.Metadata.forEach(function(m) {
                                            if (m.Type === 'WordBoundary' && m.Data && m.Data.text) { boundaries.push({ offset: m.Data.Offset || 0, text: m.Data.text.Text || '' }); }
                                        });
                                    }
                                } catch(e) {}
                            }
                            return;
                        }
                        if (s2.indexOf('Path:turn.end') !== -1) { ws.close(); return; }
                    }
                    if (event.data instanceof ArrayBuffer || event.data instanceof Blob) {
                        var source = (event.data instanceof Blob) ? null : event.data;
                        if (event.data instanceof Blob) return;
                        var bytes = new Uint8Array(source);
                        var needle = new TextEncoder().encode('Path:audio\r\n');
                        for (var i = 0; i <= bytes.length - needle.length; i++) {
                            var ok = true;
                            for (var j = 0; j < needle.length; j++) { if (bytes[i + j] !== needle[j]) { ok = false; break; } }
                            if (ok) {
                                var audioStart = i + needle.length;
                                if (bytes[audioStart] === 13 && bytes[audioStart + 1] === 10) audioStart += 2;
                                audioChunks.push(bytes.slice(audioStart).buffer);
                                return;
                            }
                        }
                    }
                };
                var errTimer = setTimeout(function() {
                    if (ws.readyState === 0 || ws.readyState === 1) { try { ws.close(); } catch(e) {} }
                    if (!rejected) { rejected = true; reject('timeout'); }
                }, 20000);
                ws.onerror = function() { clearTimeout(errTimer); if (!rejected) { rejected = true; reject('ws error'); } };
                ws.onclose = function() {
                    clearTimeout(errTimer);
                    if (rejected) return;
                    if (audioChunks.length === 0) { rejected = true; reject('no audio'); return; }
                    clearTimeout(tid);
                    var totalLen = audioChunks.reduce(function(s, c) { return s + c.byteLength; }, 0);
                    if (totalLen < 100) { rejected = true; reject('audio too small'); return; }
                    var allAudio = new Uint8Array(totalLen);
                    var off = 0;
                    audioChunks.forEach(function(c) { allAudio.set(new Uint8Array(c), off); off += c.byteLength; });
                    var timepoints = [];
                    boundaries.forEach(function(b) { timepoints.push({ timeSeconds: b.offset / 10000000 }); });
                    resolve({ audio: allAudio.buffer, timepoints: timepoints });
                };
            }).catch(function(e) { if (!rejected) { rejected = true; reject(e); } });
        });
        p.catch(function() {}); // silence unhandled rejection
        _browserEdgeTTSCache[cacheKey] = p;
        return p;
    }

    /* Pre-load voices */
    if ('speechSynthesis' in window) {
        window.speechSynthesis.getVoices();
        window.addEventListener('load', function() { window.speechSynthesis.getVoices(); });
    }
    </script>
</head>
<body class="font-lao">
    <!-- Splash Screen -->
    <div id="splash-screen" class="splash-screen" aria-label="loading">
        <picture style="position:fixed;inset:0;z-index:0;width:100%;height:100%;">
            <source media="(max-width: 767px)" srcset="<?= url('images/loading/loading_mobile.jpg') ?>">
            <img src="<?= url('images/loading/loading_desktop_tablet.jpg') ?>" alt="" width="390" height="844" fetchpriority="high" style="width:100%;height:100%;object-fit:cover;display:block;">
        </picture>
        <!-- <div id="splash-logos" aria-label="partner logos" style="position:relative;z-index:1;background:rgba(255,255,255,0.75);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border-radius:24px;padding:14px;display:flex;flex-wrap:wrap;gap:12px;justify-content:center;align-items:center;max-width:92vw;">
            <img class="splash-logo" src="<?= url('logo_wutdarn.png') ?>" alt="logo_wutdarn">
            <img class="splash-logo" src="<?= url('dhammakonnon.png') ?>" alt="dhammakonnon">
            <img class="splash-logo" src="<?= url('ຮຸ່ງເເສງເເຫ່ງທັມ.png') ?>" alt="ຮຸ່ງແສງແຫ່ງທັມ">
            <img class="splash-logo" src="<?= url('ຕະຖາຄົຕພາສິຕ.png') ?>" alt="ຕະຖາຄົຕພາສິຕ">
            <img class="splash-logo" src="<?= url('ພຸທທະວົງສ໌.png') ?>" alt="ພຸທທະວົງສ໌">
            <img class="splash-logo" src="<?= url('ວິນັຍສຸຄົຕ.png') ?>" alt="ວິນັຍສຸຄົຕ">
            <img class="splash-logo" src="<?= url('ວັດບ້ານນາຈິກ.png') ?>" alt="ວັດບ້ານນາຈິກ">
            <img class="splash-logo" src="<?= url('buddhaword.png') ?>" alt="ຄຳສອນພຸດທະ">
        </div> -->
    </div> 
    <script>
    (function() {
        var splash = document.getElementById('splash-screen');
        if (!splash) return;
        function hideSplash() {
            if (!splash || splash.classList.contains('fade-out')) return;
            splash.classList.add('fade-out');
            setTimeout(function() { if (splash && splash.parentNode) splash.parentNode.removeChild(splash); }, 320);
        }
        window.addEventListener('app-data-ready', hideSplash);
        window.addEventListener('load', function() { setTimeout(hideSplash, 800); });
        setTimeout(hideSplash, 10000);
    })();
    </script>
    <div x-data="{ 
        isMenuOpen: false, 
        isSearchOpen: false,
        searchQuery: '',
        searchResults: [],
        isLoading: false,
        isSyncing: false,
        hasUpdate: false,
        cachedVersion: 0,
        isOnline: navigator.onLine,
        searchController: null,
        async performSearch() {
            if (this.searchQuery.trim().length < 2) {
                this.searchResults = [];
                return;
            }
            if (this.searchController) this.searchController.abort();
            const ac = new AbortController();
            this.searchController = ac;
            this.isLoading = true;
            try {
                const response = await fetch('<?= url('/api/search') ?>?q=' + encodeURIComponent(this.searchQuery), { signal: ac.signal });
                if (!ac.signal.aborted) {
                    this.searchResults = await response.json();
                }
            } catch (e) {
                if (e.name !== 'AbortError') console.error('Search failed', e);
            } finally {
                if (!ac.signal.aborted) this.isLoading = false;
            }
        },
        escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        },
        highlight(text) {
            if (!text) return '';
            const term = this.searchQuery;
            if (!term || term.trim().length < 2) return this.escapeHtml(text);
            const escaped = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const regex = new RegExp(`(${escaped})`, 'gi');
            return this.escapeHtml(text).replace(regex, '<span class=&quot;bg-yellow-200 font-bold text-black&quot;>$1</span>');
        },
        async syncData(isSilent = false) {
            this.isSyncing = true;

            if (!isSilent) {
                const result = await Swal.fire({
                    title: 'ອັບເດດຂໍ້ມູນ?',
                    text: 'ທ່ານຕ້ອງການດຶງຂໍ້ມູນໃໝ່ຈາກລະບົບ ຫຼື ບໍ່?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#795548',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'ຕົກລົງ',
                    cancelButtonText: 'ຍົກເລີກ'
                });
                if (!result.isConfirmed) {
                    this.isSyncing = false;
                    return;
                }
                
                Swal.fire({
                    title: 'ກຳລັງອັບເດດ...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
            }

            try {
                const signal = (ms) => {
                    const c = new AbortController();
                    setTimeout(() => c.abort(), ms);
                    return c.signal;
                };
                const [sutrasRes, booksRes, videosRes, calendarRes] = await Promise.all([
                    fetch('<?= url('/api/sync-sutras') ?>', { signal: signal(120000) }).then(r => r.ok ? r.json() : { success: false }),
                    fetch('<?= url('/api/sync-books') ?>', { signal: signal(120000) }).then(r => r.ok ? r.json() : { success: false }),
                    fetch('<?= url('/api/sync-videos') ?>', { signal: signal(120000) }).then(r => r.ok ? r.json() : { success: false }),
                    fetch('<?= url('/api/sync-calendar') ?>', { signal: signal(120000) }).then(r => r.ok ? r.json() : { success: false }),
                ]);

                let allOk = true;
                if (sutrasRes.success) {
                    localStorage.setItem('buddhaword_sutras', JSON.stringify(sutrasRes.data));
                    if (sutrasRes.version) {
                        localStorage.setItem('buddhaword_version', sutrasRes.version.toString());
                        this.cachedVersion = sutrasRes.version;
                        this.hasUpdate = false;
                    }
                } else { allOk = false; }

                if (booksRes.success) {
                    localStorage.setItem('buddhaword_books', JSON.stringify(booksRes.data));
                } else { allOk = false; }

                if (videosRes.success) {
                    localStorage.setItem('buddhaword_videos', JSON.stringify(videosRes.data));
                } else { allOk = false; }

                if (calendarRes.success) {
                    localStorage.setItem('buddhaword_calendar', JSON.stringify(calendarRes.data));
                } else { allOk = false; }

                if (!allOk) throw new Error('ບາງຂໍ້ມູນບໍ່ສາມາດອັບເດດໄດ້');

                if (navigator.serviceWorker.controller) {
                    navigator.serviceWorker.controller.postMessage({
                        type: 'CACHE_API_DATA',
                        payload: { url: '<?= url('/api/sync-sutras') ?>', data: sutrasRes }
                    });
                    navigator.serviceWorker.controller.postMessage({
                        type: 'CACHE_API_DATA',
                        payload: { url: '<?= url('/api/sync-books') ?>', data: booksRes }
                    });
                    navigator.serviceWorker.controller.postMessage({
                        type: 'CACHE_API_DATA',
                        payload: { url: '<?= url('/api/sync-videos') ?>', data: videosRes }
                    });
                    navigator.serviceWorker.controller.postMessage({
                        type: 'CACHE_API_DATA',
                        payload: { url: '<?= url('/api/sync-calendar') ?>', data: calendarRes }
                    });
                }

                window.dispatchEvent(new CustomEvent('sync-complete'));

                if ('caches' in window) {
                    try {
                        const keys = await caches.keys();
                        await Promise.all(keys.map(k => caches.delete(k)));
                    } catch (_) {}
                }
                if (typeof bwdb !== 'undefined') {
                    try { await bwdb.clearAll(); } catch (_) {}
                }

                sessionStorage.clear();

                if (!isSilent) {
                    Swal.fire({
                        title: 'ສຳເລັດ',
                        text: 'ອັບເດດຂໍ້ມູນຮຽບຮ້ອຍແລ້ວ',
                        icon: 'success',
                        confirmButtonColor: '#795548',
                    });
                }
            } catch (e) {
                console.error('Background sync failed', e);
                if (!isSilent) {
                    let msg = 'ບໍ່ສາມາດອັບເດດຂໍ້ມູນໄດ້';
                    if (e.name === 'AbortError' || e.name === 'TimeoutError') {
                        msg = 'ເຊີເວີບໍ່ຕອບສະໜອງ, ກະລຸນາລອງໃໝ່ພາຍຫຼັງ';
                    } else if (e instanceof SyntaxError) {
                        msg = 'ຂໍ້ມູນບໍ່ຖືກຕ້ອງ, ກະລຸນາລອງໃໝ່ພາຍຫຼັງ';
                    }
                    Swal.fire('ຜິດພາດ', msg, 'error');
                }
            } finally {
                this.isSyncing = false;
            }
        },

        async checkForUpdates() {
            if (this.cachedVersion === 0 && !localStorage.getItem('buddhaword_sutras')) return;
            try {
                const res = await fetch('<?= url('/api/check-update') ?>', { cache: 'no-store' });
                const data = await res.json();
                if (data.version > this.cachedVersion) {
                    this.hasUpdate = true;
                }
            } catch (e) {
                /* silently fail */
            }
        },
        init() {
            /* Listen for online/offline status */
            window.addEventListener('online', () => { this.isOnline = true; });
            window.addEventListener('offline', () => { this.isOnline = false; });
            this.cachedVersion = parseInt(localStorage.getItem('buddhaword_version') || '0', 10);
            this.checkForUpdates();
        }
    }">
        <div id="navbarWrapper" class="fixed top-0 left-0 right-0 z-50 transition-transform duration-300">
            <nav class="bg-[#795548] text-white px-4 py-2 flex items-center justify-between shadow-md h-[50px] md:h-[60px]">
            <div class="flex items-center gap-2 md:gap-4">
                <?php 
                $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                $isHome = ($currentUri === url('/'));
                ?>
                <?php if (!$isHome): ?>
                    <button onclick="history.back()" class="text-white p-2 hover:bg-white/10 rounded-full transition-colors" aria-label="Back">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                <?php endif; ?>
                <a href="<?= url('/') ?>" class="flex items-center gap-1 sm:gap-2 min-w-0" aria-label="ໜ້າຫຼັກ">
                    <img src="<?= url('assets/images/logo.png') ?>" alt="Logo" width="48" height="48" class="h-7 sm:h-8 md:h-12 w-auto object-contain flex-shrink-0">
                    <span class="font-bold text-xs sm:text-base md:text-xl tracking-tight whitespace-nowrap overflow-hidden text-ellipsis">ຄຳສອນພຸດທະ</span>
                </a>
            </div>
            <div class="flex items-center gap-1 md:gap-3">
                <button @click="syncData()" class="text-white p-2 hover:bg-white/10 rounded-full transition-colors relative" title="ອັບເດດຂໍ້ມູນ" aria-label="Sync data">
                    <div x-show="hasUpdate"
                         class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 border-2 border-white rounded-full flex items-center justify-center z-10">
                        <span class="text-[8px] text-white font-bold leading-none">!</span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" :class="isSyncing ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
                <button @click="isSearchOpen = true; $nextTick(() => $refs.searchInput.focus())" class="text-white p-2 hover:bg-white/10 rounded-full transition-colors" aria-label="Search">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
                <button @click="isMenuOpen = !isMenuOpen" class="text-white p-2 hover:bg-white/10 rounded-full transition-colors" aria-label="Menu">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                </button>
            </div>
        </nav>
        </div>

        <!-- Search Modal -->
        <div x-show="isSearchOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[60] flex items-start justify-center p-4 bg-black/60 backdrop-blur-sm"
             style="display: none;"
             @keydown.escape.window="isSearchOpen = false"
             role="dialog"
             aria-modal="true"
             aria-label="Search">
            <div class="bg-white rounded-2xl sm:rounded-3xl shadow-2xl w-full max-w-2xl mt-4 sm:mt-10 overflow-hidden flex flex-col max-h-[85vh]"
                 @click.away="isSearchOpen = false">
                <div class="p-3 sm:p-4 border-b flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input x-ref="searchInput"
                           x-model="searchQuery" 
                           @input.debounce.300ms="performSearch()"
                           type="search" 
                           placeholder="ຄົ້ນຫາ..." 
                           class="flex-1 text-base sm:text-xl border-none outline-none font-lao"
                           autofocus>
                    <button @click="isSearchOpen = false" class="p-2 text-gray-400 hover:text-gray-600" aria-label="Close search">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="flex-1 overflow-y-auto p-2">
                    <div x-show="isLoading" class="flex justify-center p-6 sm:p-8">
                        <div class="loader"></div>
                    </div>

                    <div x-show="!isLoading && searchQuery.length >= 2 && searchResults.length === 0" class="p-6 sm:p-8 text-center text-gray-500 font-lao text-sm sm:text-base">
                        ບໍ່ພົບຂໍ້ມູນສໍາລັບ "<span x-text="searchQuery"></span>"
                    </div>

                    <div x-show="!isLoading && searchQuery.length < 2" class="p-6 sm:p-8 text-center text-gray-400 font-lao text-sm sm:text-base">
                        ພິມຢ່າງໜ້ອຍ 2 ຕົວອັກສອນເພື່ອຄົ້ນຫາ...
                    </div>

                    <div class="flex flex-col gap-1 sm:gap-2">
                        <template x-for="result in searchResults" :key="result.url + result.title">
                            <a :href="result.url" class="p-3 sm:p-4 hover:bg-gray-50 rounded-xl sm:rounded-2xl flex flex-col gap-1 transition-colors border border-transparent hover:border-brown-100 overflow-hidden max-w-full">
                                <div class="flex items-start gap-2 min-w-0">
                                    <span class="px-1.5 py-0.5 rounded text-[9px] sm:text-[10px] font-bold uppercase tracking-wider flex-shrink-0"
                                          :class="{
                                              'bg-blue-100 text-blue-700': result.type === 'sutra',
                                              'bg-green-100 text-green-700': result.type === 'book',
                                              'bg-red-100 text-red-700': result.type === 'video',
                                              'bg-purple-100 text-purple-700': result.type === 'calendar',
                                              'bg-amber-100 text-amber-700': result.type === 'book-page'
                                          }"
                                          x-html="highlight(result.category)"></span>
                                    <h4 class="text-base sm:text-lg font-bold text-gray-800 font-lao break-words min-w-0" x-html="highlight(result.title)"></h4>
                                </div>
                                <p class="text-xs sm:text-sm text-gray-500 line-clamp-2 font-lao break-words" x-html="highlight(result.detail)"></p>
                            </a>
                        </template>
                    </div>
                </div>
            </div>
        </div> 

        <!-- Mobile Menu -->
        <div x-show="isMenuOpen" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform -translate-y-full"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform -translate-y-full"
             class="fixed inset-0 z-40 bg-white pt-[50px] md:pt-[60px] overflow-y-auto"
             style="display: none;"
             role="dialog"
             aria-modal="true"
             aria-label="Navigation menu">
            <div class="p-4 sm:p-6 md:p-10 max-w-4xl mx-auto">
                <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-4 sm:gap-6 mb-8 md:mb-12">
                    <?php
                    $logos = [
                        ['src' => 'logo_wutdarn.png', 'href' => 'https://web.facebook.com/watdanpra'],
                        ['src' => 'dhammakonnon.png', 'href' => 'https://web.facebook.com/dhammakonnon'],
                        ['src' => 'ຮຸ່ງເເສງເເຫ່ງທັມ.png', 'href' => 'https://www.facebook.com/Sumittosumittabounsong'],
                        ['src' => 'ຕະຖາຄົຕພາສິຕ.png', 'href' => 'https://web.facebook.com/watpavimokkhavanaram.la'],
                        ['src' => 'ພຸທທະວົງສ໌.png', 'href' => 'https://www.facebook.com/dhammalife.laos'],
                        ['src' => 'ວິນັຍສຸຄົຕ.png', 'href' => 'https://www.facebook.com/profile.php?id=100091798479187'],
                        ['src' => 'ວັດບ້ານນາຈິກ.png', 'href' => 'https://www.facebook.com/phouhuck.phousamnieng.7'],
                        ['src' => 'buddhaword.png', 'href' => 'https://web.facebook.com/profile.php?id=100077638042542'],
                    ];
                    foreach ($logos as $logo): ?>
                        <a href="<?= $logo['href'] ?>" target="_blank" class="flex justify-center group" rel="noopener">
                            <img src="<?= url($logo['src']) ?>" alt="" class="w-12 h-12 sm:w-16 sm:h-16 object-contain transition-transform group-hover:scale-110" loading="lazy" width="64" height="64" decoding="async">
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php
                $mobileMenuItems = [
                    ['href' => '/', 'icon' => 'sutra.png', 'label' => 'ພຣະສູດ'],
                    ['href' => '/search-books', 'icon' => 'book.png', 'label' => 'ຄົ້ນຫາປຶ້ມ'],
                    ['href' => '/favorites', 'icon' => 'favorites.png', 'label' => 'ຖືກໃຈ'],
                    ['href' => '/book', 'icon' => 'book.png', 'label' => 'ປື້ມ'],
                    ['href' => '/video', 'icon' => 'vdo.png', 'label' => 'Video'],
                    ['href' => '/calendar', 'icon' => 'calendar.png', 'label' => 'ປະຕິທິນ'],
                    ['href' => 'https://buddhaword.notion.site/4d1689680be74b6f96071c8dda16db9e', 'icon' => 'dhamma.png', 'label' => 'ພຣະທັມ', 'external' => true],
                    // ['href' => '/upload', 'icon' => 'book.png', 'label' => 'ຈັດການປຶ້ມ'],
                ['href' => '/about', 'icon' => 'about.png', 'label' => 'ຕິດຕໍ່'],
                ];
                foreach ($mobileMenuItems as $item):
                    $mIsActive = ($item['external'] ?? false) ? false : ($item['href'] === '/'
                        ? ($currentUri === url('/') || $currentUri === url('') || strpos($currentUri, url('/sutra/')) === 0)
                        : strpos($currentUri, url($item['href'])) !== false);
                ?>
                    <a href="<?= $item['external'] ?? false ? $item['href'] : url($item['href']) ?>" <?= ($item['external'] ?? false) ? 'target="_blank" rel="noopener noreferrer"' : '' ?> @click="<?= ($item['external'] ?? false) ? '' : 'isMenuOpen = false' ?>" class="flex items-center gap-4 py-3 sm:py-4 border-b px-2 rounded-xl transition-colors <?= $mIsActive ? 'bg-[#795548] text-white' : 'hover:bg-gray-50 text-gray-800' ?>">
                        <?php if ($item['href'] === '/search-books'): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 sm:w-10 sm:h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <?php else: ?>
                        <img src="<?= url('assets/icons/' . $item['icon']) ?>" alt="" class="w-8 h-8 sm:w-10 sm:h-10" loading="lazy" width="40" height="40" decoding="async">
                        <?php endif; ?>
                        <?= $item['label'] ?>
                    </a>
                <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="min-h-screen pb-24 container mx-auto max-w-7xl px-2 sm:px-4 pt-[50px] md:pt-[60px]">
            <input type="hidden" id="ttsApiUrl" value="<?= url('/api/tts/synthesize') ?>">
            <?= $content ?>
        </main>

        <!-- Offline Indicator (subtle dot only, no intrusive banner) -->
        <div x-show="!isOnline"
             class="fixed top-1 right-1 z-30 w-2 h-2 rounded-full bg-yellow-500/60"
             style="display: none;"
             title="ກຳລັງໃຊ້ງານແບບອອບລາຍ"></div>

        <!-- Bottom Navigation -->
        <div id="bottomNav" class="fixed bottom-4 left-1/2 transform -translate-x-1/2 bg-[#DDCFBC]/95 backdrop-blur-md rounded-2xl shadow-xl flex items-center p-1.5 gap-1 z-30 border border-white/20 transition-all duration-300 opacity-100 visible translate-y-0">
            <?php
            $navItems = [
                ['href' => '/', 'icon' => 'sutra.png', 'label' => 'ພຣະສູດ'],
                ['href' => '/search-books', 'icon' => 'book.png', 'label' => 'ຄົ້ນຫາປຶ້ມ'],
                ['href' => '/favorites', 'icon' => 'favorites.png', 'label' => 'ຖືກໃຈ'],
                ['href' => '/book', 'icon' => 'book.png', 'label' => 'ປື້ມ'],
                ['href' => '/video', 'icon' => 'vdo.png', 'label' => 'Video'],
                ['href' => '/calendar', 'icon' => 'calendar.png', 'label' => 'ປະຕິທິນ'],
                ['href' => 'https://buddhaword.notion.site/4d1689680be74b6f96071c8dda16db9e', 'icon' => 'dhamma.png', 'label' => 'ພຣະທັມ', 'external' => true],
                // ['href' => '/about', 'icon' => 'about.png', 'label' => 'ຕິດຕໍ່'],
            ];
            foreach ($navItems as $item): 
                $isActive = ($item['external'] ?? false) ? false : ($item['href'] === '/'
                    ? ($currentUri === url('/') || $currentUri === url('') || strpos($currentUri, url('/sutra/')) === 0)
                    : strpos($currentUri, url($item['href'])) !== false);
            ?>
                <a href="<?= $item['external'] ?? false ? $item['href'] : url($item['href']) ?>" <?= ($item['external'] ?? false) ? 'target="_blank" rel="noopener noreferrer"' : '' ?> class="flex flex-col items-center justify-center p-1.5 sm:p-2 rounded-xl transition-all duration-200 min-w-[46px] sm:min-w-[65px] <?= $isActive ? 'bg-[#795548] text-white shadow-md scale-105' : 'text-[#795548] hover:bg-[#c9bba7]' ?>" aria-label="<?= $item['label'] ?>">
                    <?php if ($item['href'] === '/search-books'): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    <?php else: ?>
                    <img src="<?= url('assets/icons/' . $item['icon']) ?>" alt="" class="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8" loading="lazy" width="32" height="32" decoding="async">
                    <?php endif; ?>
                    <span class="text-[9px] sm:text-[10px] font-bold mt-0.5"><?= $item['label'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Scroll to Top -->
        <button id="scrollTopBtn" onclick="window.scrollTo({top:0,behavior:'smooth'})" class="fixed bottom-20 right-4 z-40 w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center rounded-full bg-[#795548] text-white shadow-lg hover:bg-[#5E412D] transition-all duration-300 opacity-0 invisible translate-y-4" aria-label="Scroll to top">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18" />
            </svg>
        </button>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js" crossorigin="anonymous"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11.15.10/dist/sweetalert2.min.js" crossorigin="anonymous"></script>
    <script>
    /* Service worker registration with update handling */
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('<?= url('sw.js') ?>').then(function(reg) {
                reg.addEventListener('updatefound', function() {
                    var newSW = reg.installing;
                    newSW.addEventListener('statechange', function() {
                        if (newSW.state === 'installed' && navigator.serviceWorker.controller) {
                            /* New version available — notify the user */
                            if (confirm('ເວີຊັ່ນໃໝ່ພ້ອມໃຊ້ງານແລ້ວ. ຣີເຟຣດເພື່ອອັບເດດ?')) {
                                newSW.postMessage({ type: 'SKIP_WAITING' });
                                window.location.reload();
                            }
                        }
                    });
                });
            });
        });
    }

    /* Clear 'updated' parameter from URL */
    window.addEventListener('load', function() {
        const url = new URL(window.location);
        if (url.searchParams.has('updated')) {
            url.searchParams.delete('updated');
            window.history.replaceState({}, document.title, url.pathname + url.search);
        }
    });
 
    /* Auto-hide navbar & bottom nav on scroll */
    (function() {
        const navbar = document.getElementById('navbarWrapper');
        const bottomNav = document.getElementById('bottomNav');
        const scrollTopBtn = document.getElementById('scrollTopBtn');
        if (!navbar || !bottomNav || !scrollTopBtn) return;

        let lastScrollY = 0;
        let ticking = false;
        let scrollTimer = null;

        function updateUI() {
            const currentScrollY = window.scrollY;
            const docHeight = document.documentElement.scrollHeight;
            const winHeight = window.innerHeight;
            const maxScroll = Math.max(docHeight - winHeight, 1);

            if (currentScrollY > maxScroll * 0.7) {
                scrollTopBtn.classList.remove('opacity-0', 'invisible', 'translate-y-4');
                scrollTopBtn.classList.add('opacity-100', 'visible', 'translate-y-0');
            } else {
                scrollTopBtn.classList.remove('opacity-100', 'visible', 'translate-y-0');
                scrollTopBtn.classList.add('opacity-0', 'invisible', 'translate-y-4');
            }

            if (currentScrollY > 80) {
                if (currentScrollY > lastScrollY) {
                    navbar.classList.add('-translate-y-full');
                    bottomNav.classList.remove('opacity-100', 'visible', 'translate-y-0');
                    bottomNav.classList.add('opacity-0', 'invisible', 'translate-y-4');
                } else if (currentScrollY < lastScrollY) {
                    navbar.classList.remove('-translate-y-full');
                    bottomNav.classList.remove('opacity-0', 'invisible', 'translate-y-4');
                    bottomNav.classList.add('opacity-100', 'visible', 'translate-y-0');
                }
            } else {
                navbar.classList.remove('-translate-y-full');
                bottomNav.classList.remove('opacity-0', 'invisible', 'translate-y-4');
                bottomNav.classList.add('opacity-100', 'visible', 'translate-y-0');
            }

            lastScrollY = currentScrollY;
            ticking = false;
        }

        window.addEventListener('scroll', function() {
            if (!ticking) {
                requestAnimationFrame(function() { updateUI(); });
                ticking = true;
            }
            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(function() {
                if (window.scrollY > 80) {
                    navbar.classList.remove('-translate-y-full');
                    bottomNav.classList.remove('opacity-0', 'invisible', 'translate-y-4');
                    bottomNav.classList.add('opacity-100', 'visible', 'translate-y-0');
                }
            }, 200);
        }, { passive: true });

        updateUI();
    })();
    </script>

    <script>
    /* PWA Install - Add to Home Screen (floating banner pattern) */
    (function() {
        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) return;
        if (localStorage.getItem('buddhaword_install_dismissed') === 'true') return;
        if (localStorage.getItem('hasShownA2HS') === 'true') return;

        var dPrompt = null;
        var banner = null;
        var autoTimer = null;

        function createBanner() {
            if (banner) return;
            banner = document.createElement('div');
            banner.id = 'pwa-install-banner';
            banner.style.cssText = 'position:fixed;bottom:85px;left:50%;transform:translateX(-50%);z-index:9999;background:#fff;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.15);padding:16px 20px;display:flex;align-items:center;gap:12px;width:calc(100% - 32px);max-width:400px;font-family:sans-serif;';
            banner.innerHTML =
                '<img src="<?= url('assets/images/logo.png') ?>" alt="" width="44" height="44" style="border-radius:8px;flex-shrink:0;">' +
                '<div style="flex:1;min-width:0;">' +
                    '<p style="margin:0;font-size:15px;font-weight:bold;color:#333;">\u0e95\u0eb4\u0e94\u0e95\u0eb1\u0ec9\u0e87 App ຄຳສອນພຸດທະ</p>' +
                    '<p style="margin:0;font-size:13px;color:#666;">\u0e95\u0eb4\u0e94\u0e95\u0eb1\u0ec9\u0e87\u0ec1\u0ead\u0eb1\u0e9a\u0ec0\u0e9e\u0eb7\u0ec8\u0ead\u0ec3\u0e8a\u0ec9\u0e87\u0eb2\u0e99\u0ec4\u0e94\u0ec9\u0e87\u0ec8\u0eb2\u0e22\u0e82\u0eb6\u0ec9\u0e99</p>' +
                '</div>' +
                '<button id="pwa-install-btn" style="background:#795548;color:#fff;border:none;padding:8px 16px;border-radius:10px;font-size:14px;font-weight:bold;cursor:pointer;white-space:nowrap;flex-shrink:0;">\u0e95\u0eb4\u0e94\u0e95\u0eb1\u0ec9\u0e87</button>' +
                '<button id="pwa-close-btn" style="background:none;border:none;color:#999;cursor:pointer;padding:4px;flex-shrink:0;" aria-label="\u0e9b\u0eb4\u0e94">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>' +
                '</button>';
 
            document.body.appendChild(banner);

            autoTimer = setTimeout(function() {
                if (banner && banner.parentNode) banner.parentNode.removeChild(banner);
                banner = null;
            }, 15000);

            document.getElementById('pwa-install-btn').addEventListener('click', function() {
                clearTimeout(autoTimer);
                if (dPrompt) {
                    dPrompt.prompt();
                    dPrompt.userChoice.then(function(choice) {
                        if (choice.outcome === 'accepted') {
                            localStorage.setItem('hasShownA2HS', 'true');
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: '\u0e95\u0eb4\u0e94\u0e95\u0eb1\u0ec9\u0e87 App ຄຳສອນພຸດທະ',
                        text: '\u0e81\u0ebb\u0e94 \u22ee (\u0e40\u0ea1\u0e99\u0eb9) \u0e41\u0ea5\u0ec9\u0ea7\u0e81\u0ebb\u0e94 \u0e95\u0eb4\u0e94\u0e95\u0eb1\u0ec9\u0e87\u0ec1\u0ead\u0eb1\u0e9a',
                        confirmButtonText: '\u0e95\u0ebb\u0e81\u0ea5\u0ebb\u0e87',
                        confirmButtonColor: '#795548'
                    });
                }
                if (banner && banner.parentNode) banner.parentNode.removeChild(banner);
                banner = null;
            });

            document.getElementById('pwa-close-btn').addEventListener('click', function() {
                clearTimeout(autoTimer);
                if (banner && banner.parentNode) banner.parentNode.removeChild(banner);
                banner = null;
                localStorage.setItem('buddhaword_install_dismissed', 'true');
            });
        }

        // Try native install prompt
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            dPrompt = e;
            if (!banner) {
                createBanner();
            }
        });

        // Fallback: if beforeinstallprompt doesn't fire within 3s, show banner anyway
        setTimeout(function() {
            if (banner) return;
            if (!('serviceWorker' in navigator)) return;
            createBanner();
        }, 3000);
    })();
    </script>

    <script>
    (function() {
        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) return;
        if (localStorage.getItem('buddhaword_store_prompt_dismissed') === 'true') return;
        if (localStorage.getItem('buddhaword_app_installed') === 'true') return;

        var ua = navigator.userAgent || navigator.vendor || window.opera;
        var isAndroid = /android/i.test(ua);
        var isIOS = /iPad|iPhone|iPod/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

        if (!isAndroid && !isIOS) return;

        var storeUrl = isAndroid
            ? 'https://play.google.com/store/apps/details?id=com.buddha.lao_tipitaka&pcampaignid=web_share'
            : 'https://apps.apple.com/la/app/buddhaword-lao/id6751720204';

        var badgeImg = isAndroid
            ? '<?= url('assets/images/play_store.png.webp') ?>'
            : '<?= url('assets/images/app_store.svg') ?>';

        var badgeAlt = isAndroid ? 'Google Play' : 'App Store';

        setTimeout(function() {
            Swal.fire({
                html: '<div style="text-align:center;padding:8px;">' +
                    '<p style="font-size:18px;font-weight:bold;color:#333;margin-bottom:12px;font-family:\'Noto Sans Lao\',sans-serif;">ຕິດຕັ້ງແອັບ ຄຳສອນພຸດທະ</p>' +
                    '<p style="font-size:14px;color:#666;margin-bottom:20px;font-family:\'Noto Sans Lao\',sans-serif;">ໃຊ້ງານສະດວກຂຶ້ນ ດ້ວຍການຕິດຕັ້ງແອັບພລິເຄຊັນ</p>' +
                    '<a id="store-install-link" href="' + storeUrl + '" target="_blank" rel="noopener noreferrer" style="display:inline-block;text-decoration:none;">' +
                        '<img src="' + badgeImg + '" alt="' + badgeAlt + '" style="height:56px;width:auto;border-radius:8px;">' +
                    '</a>' +
                    '</div>',
                showConfirmButton: false,
                showCloseButton: true,
                closeButtonHtml: '&times;',
                customClass: {
                    closeButton: 'swal-close-btn',
                    popup: 'swal-store-popup'
                },
                didOpen: function() {
                    var link = document.getElementById('store-install-link');
                    if (link) {
                        link.addEventListener('click', function() {
                            localStorage.setItem('buddhaword_app_installed', 'true');
                        });
                    }
                },
                didClose: function() {
                    localStorage.setItem('buddhaword_store_prompt_dismissed', 'true');
                }
            });
        }, 2000);
    })();
    </script>

    <style>
    .swal-store-popup {
        border-radius: 20px !important;
        padding: 24px 20px 16px !important;
        max-width: 380px !important;
    }
    .swal-store-popup .swal2-close {
        color: #999 !important;
        font-size: 28px !important;
        font-weight: 300 !important;
    }
    .swal-store-popup .swal2-close:hover {
        color: #333 !important;
    }
    </style>

</body>
</html> 
                
         
     
    