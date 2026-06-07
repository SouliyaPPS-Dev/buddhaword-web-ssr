<section class="flex flex-col items-center justify-center mb-4 p-2 sm:p-4">
    <nav class="w-full max-w-4xl mb-4" aria-label="Breadcrumb">
        <ol class="flex items-center gap-1 sm:gap-2 text-xs sm:text-sm text-white/70 Lao-font" itemscope itemtype="https://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a href="<?= url('/') ?>" itemprop="item" class="hover:text-white transition-colors"><span itemprop="name">ໜ້າຫຼັກ</span></a>
                <meta itemprop="position" content="1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 inline mx-1 text-white/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </li>
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a href="<?= url('/video') ?>" itemprop="item" class="hover:text-white transition-colors"><span itemprop="name">ວິດີໂອ</span></a>
                <meta itemprop="position" content="2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 inline mx-1 text-white/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </li>
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="text-white/90 truncate max-w-[200px]">
                <span itemprop="name"><?= htmlspecialchars($video['ຊື່ພຣະສູດ'] ?? '') ?></span>
                <meta itemprop="position" content="3">
            </li>
        </ol>
    </nav>

    <div class="w-full max-w-4xl bg-white/95 backdrop-blur-md rounded-2xl sm:rounded-3xl shadow-2xl overflow-hidden border border-white/20">
        <div class="relative w-full aspect-video bg-black">
            <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($ytId) ?>?autoplay=1&rel=0"
                    class="absolute inset-0 w-full h-full border-none"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
            </iframe>
        </div>

        <div class="p-4 sm:p-6">
            <h1 class="text-lg sm:text-2xl md:text-3xl font-bold text-gray-800 Lao-font"><?= htmlspecialchars($video['ຊື່ພຣະສູດ']) ?></h1>
            <p class="text-sm sm:text-base text-gray-500 mt-2 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                YouTube Video
            </p>

            <div class="mt-4 flex flex-wrap gap-3">
                <a href="<?= url('/video') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-sm hover:bg-gray-200 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    ທັງໝົດ
                </a>
                <a href="https://www.youtube.com/watch?v=<?= htmlspecialchars($ytId) ?>" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-xl font-bold text-sm hover:bg-red-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    ເປີດໃນ YouTube
                </a>
                <button onclick="shareVideo()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-sm hover:bg-gray-200 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                    ແຊລລິງ
                </button>
            </div>
        </div>
    </div>

    <?php if (!empty($otherVideos)): ?>
    <div class="w-full max-w-4xl mt-4 px-2">
        <h2 class="text-lg sm:text-xl font-bold text-white/90 mb-0 Lao-font">ວິດີໂອອື່ນໆ</h2>
        <div class="grid gap-4 grid-cols-2 mb-4">
            <?php foreach ($otherVideos as $v): ?>
            <?php
                preg_match('/(?:youtu\.be\/|youtube\.com\/(?:.*v=|.*\/|.*embed\/|.*shorts\/))([\w-]+)/', $v['link'], $m);
                $vYtId = $m[1] ?? '';
                $vThumb = $vYtId ? "https://img.youtube.com/vi/{$vYtId}/hqdefault.jpg" : '';
            ?>
            <div class="mt-4 flex flex-col items-center" style="margin-bottom: -2rem;">
                <a href="<?= url('/video/view/' . $vYtId) ?>"
                   class="z-10 flex flex-col justify-between items-center cursor-pointer group w-full">
                   <div class="mt-2 text-center px-1 w-full">
                        <p class="text-xs sm:text-sm font-bold text-[#DDCFBC] truncate Lao-font drop-shadow-sm group-hover:text-[#EEDDB6] transition-colors"><?= htmlspecialchars($v['ຊື່ພຣະສູດ']) ?></p>
                    </div>
                    <div class="mt-1 w-full aspect-video flex-shrink-0 shadow-xl transition-transform duration-300 transform group-hover:-translate-y-2 overflow-hidden relative"
                         style="border-radius: 0; background: transparent; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2), 0 4px 6px -2px rgba(0,0,0,0.1), inset 0 -3px 6px rgba(0,0,0,0.2);">

                        <img src="<?= htmlspecialchars($vThumb) ?>"
                             alt="<?= htmlspecialchars($v['ຊື່ພຣະສູດ']) ?>"
                             loading="lazy"
                             class="z-0 object-cover w-full h-full transition-transform duration-300 group-hover:scale-110"
                             style="border-radius: 0;"
                             onerror="this.src='<?= url('assets/images/logo.png') ?>'; this.style.objectFit='contain'; this.style.padding='8px'; this.style.backgroundColor='#6B553A'; this.style.opacity='0.6'">

                        <div class="absolute inset-0 flex items-center justify-center bg-black/10 group-hover:bg-black/30 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 sm:h-14 sm:w-14 text-white opacity-80 group-hover:opacity-100 transition-opacity drop-shadow-lg" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        </div>
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
    </div>
    <?php endif; ?>

    <!-- Toast for share -->
    <div id="shareToast" class="fixed bottom-20 left-1/2 -translate-x-1/2 z-50 px-4 py-2 bg-gray-900 text-white text-sm rounded-xl shadow-2xl transition-all duration-300 opacity-0 translate-y-4 pointer-events-none">
        ຄັດລອກລິ້ງແລ້ວ
    </div>
</section>

<script>
function shareVideo() {
    const url = window.location.href;
    const title = '<?= htmlspecialchars($video['ຊື່ພຣະສູດ'] ?? '', ENT_QUOTES) ?>';
    if (navigator.share) {
        navigator.share({ title: title, url: url }).catch(() => {});
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