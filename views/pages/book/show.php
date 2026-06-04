<section class="w-full h-screen flex flex-col items-center justify-center bg-black/20 backdrop-blur-sm">
    <div class="w-full h-full md:w-5/6 lg:w-4/5 xl:w-3/4 relative bg-white shadow-2xl overflow-hidden rounded-none md:rounded-xl">
        <!-- Share, Fullscreen & Download Buttons -->
        <div class="absolute top-4 right-4 z-50 flex items-center gap-2">
            <?php if ($pdfDownloadUrl): ?>
            <a href="<?= htmlspecialchars($pdfDownloadUrl) ?>" download
               class="w-10 h-10 bg-white/80 rounded-full flex items-center justify-center shadow-md hover:bg-white transition-colors" title="ດາວໂຫລດ">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </a>
            <?php endif; ?>
            <button onclick="toggleFullscreen()" id="fullscreenBtn" class="w-10 h-10 bg-white/80 rounded-full flex items-center justify-center shadow-md hover:bg-white transition-colors" title="ເຕັມຈໍ">
                <svg xmlns="http://www.w3.org/2000/svg" id="fullscreenIcon" class="h-5 w-5 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                </svg>
            </button>
            <button onclick="shareBook()" class="w-10 h-10 bg-white/80 rounded-full flex items-center justify-center shadow-md hover:bg-white transition-colors" title="ແຊລລິງ">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                </svg>
            </button>
        </div>

        <?php if ($pdfEmbedLink): ?>
            <iframe src="<?= $pdfEmbedLink ?>" class="w-full h-full border-none" title="<?= htmlspecialchars($book['ຊື່']) ?>"></iframe>
        <?php else: ?>
            <div class="flex flex-col items-center justify-center h-full p-8 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h2 class="text-xl font-bold text-gray-800">ບໍ່ສາມາດເປີດປື້ມໄດ້</h2>
                <p class="text-gray-500 mt-2">ຂໍອະໄພ, ບໍ່ພົບລິ້ງສຳລັບອ່ານປື້ມຫົວນີ້.</p>
                <a href="<?= url('/book') ?>" class="mt-6 px-6 py-2 bg-[#795548] text-white rounded-xl shadow-md">ກັບຄືນ</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Toast for share -->
    <div id="shareToast" class="fixed bottom-20 left-1/2 -translate-x-1/2 z-50 px-4 py-2 bg-gray-900 text-white text-sm rounded-xl shadow-2xl transition-all duration-300 opacity-0 translate-y-4 pointer-events-none">
        ຄັດລອກລິ້ງແລ້ວ
    </div>
</section> 

<style>
section:fullscreen,
section:-webkit-full-screen,
section:-moz-full-screen {
    background: #000 !important;
    padding: 0 !important;
    width: 100% !important;
    height: 100% !important;
}
section:fullscreen > div,
section:-webkit-full-screen > div,
section:-moz-full-screen > div {
    width: 100% !important;
    height: 100% !important;
    max-width: 100% !important;
    border-radius: 0 !important;
}
</style>

<script>
function getFullscreenElement() {
    return document.fullscreenElement ||
        document.webkitFullscreenElement ||
        document.mozFullScreenElement ||
        document.msFullscreenElement;
}
 
function requestFullscreen(el) {
    if (el.requestFullscreen) return el.requestFullscreen();
    if (el.webkitRequestFullscreen) { el.webkitRequestFullscreen(); return Promise.resolve(); }
    if (el.mozRequestFullScreen) { el.mozRequestFullScreen(); return Promise.resolve(); }
    if (el.msRequestFullscreen) { el.msRequestFullscreen(); return Promise.resolve(); }
    return Promise.reject(new Error('Fullscreen not supported'));
}
 
function exitFullscreen() {
    if (document.exitFullscreen) return document.exitFullscreen();
    if (document.webkitExitFullscreen) { document.webkitExitFullscreen(); return Promise.resolve(); }
    if (document.mozCancelFullScreen) { document.mozCancelFullScreen(); return Promise.resolve(); }
    if (document.msExitFullscreen) { document.msExitFullscreen(); return Promise.resolve(); }
    return Promise.reject(new Error('Fullscreen not supported'));
}

function toggleFullscreen() {
    const container = document.querySelector('section');
    const icon = document.getElementById('fullscreenIcon');
    if (!getFullscreenElement()) {
        requestFullscreen(container).then(() => {
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />';
        }).catch(() => {});
    } else {
        exitFullscreen().then(() => {
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />';
        }).catch(() => {});
    }
}

function onFullscreenChange() {
    const icon = document.getElementById('fullscreenIcon');
    if (!getFullscreenElement()) {
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />';
    }
}
document.addEventListener('fullscreenchange', onFullscreenChange);
document.addEventListener('webkitfullscreenchange', onFullscreenChange);
document.addEventListener('mozfullscreenchange', onFullscreenChange);
document.addEventListener('MSFullscreenChange', onFullscreenChange);

function shareBook() {
    const url = window.location.href;
    if (navigator.share) {
        navigator.share({
            title: '<?= htmlspecialchars($book['ຊື່'], ENT_QUOTES) ?>',
            url: url
        }).catch(() => {});
    } else {
        navigator.clipboard.writeText(url).then(() => {
            const toast = document.getElementById('shareToast');
            toast.classList.remove('opacity-0', 'translate-y-4');
            toast.classList.add('opacity-100', 'translate-y-0');
            setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', 'translate-y-4');
            }, 2000);
        }).catch(() => {});
    }
}
</script>
