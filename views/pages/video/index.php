<?php
function getThumbnailUrl($link) {
    if (strpos($link, 'youtube.com') !== false || strpos($link, 'youtu.be') !== false) {
        preg_match('/(?:youtu\.be\/|youtube\.com\/(?:.*v=|.*\/|.*embed\/|.*shorts\/))([\w-]+)/', $link, $matches);
        $videoId = $matches[1] ?? null;
        return $videoId ? "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg" : '';
    } elseif (strpos($link, 'drive.google.com') !== false) {
        preg_match('/(?:drive\.google\.com\/(?:.*\/d\/|file\/d\/))([a-zA-Z0-9_-]+)/', $link, $matches);
        $fileId = $matches[1] ?? null;
        return $fileId ? "https://lh3.googleusercontent.com/d/{$fileId}=s320?authuser=0" : '';
    }
    return '';
}

$videosWithThumbs = array_map(function($v) {
    $v['_thumbnail'] = getThumbnailUrl($v['link'] ?? '');
    preg_match('/(?:youtu\.be\/|youtube\.com\/(?:.*v=|.*\/|.*embed\/|.*shorts\/))([\w-]+)/', $v['link'] ?? '', $m);
    $v['_ytId'] = $m[1] ?? '';
    return $v;
}, $videos);

$dharmaCategories = array_values(array_unique(array_filter(array_map(fn($v) => $v['ໝວດທັມ'] ?? '', $videos))));
sort($dharmaCategories);
?>

<section x-data="{
    searchTerm: '',
    selectedDharma: '',
    navbarHidden: false,
    videos: [],
    _timer: null,
    escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    },
    highlight(text) {
        if (!text) return '';
        const term = this.searchTerm;
        if (!term || term.trim().length < 2) return this.escapeHtml(text);
        const escaped = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp(`(${escaped})`, 'gi');
        return this.escapeHtml(text).replace(regex, '<span class=&quot;bg-yellow-200 font-bold text-black&quot;>$1</span>');
    },
    async fetchVideos() {
        const params = new URLSearchParams();
        if (this.searchTerm.trim()) params.set('search', this.searchTerm.trim());
        if (this.selectedDharma) params.set('category', this.selectedDharma);
        try {
            const res = await fetch(`<?= url('/api/videos') ?>?${params}`);
            const data = await res.json();
            this.videos = data.data || [];
        } catch (e) {
            console.error('Video search failed', e);
            this.videos = [];
        }
    },
    onSearchInput() {
        clearTimeout(this._timer);
        if (!this.searchTerm.trim() && !this.selectedDharma) return;
        this._timer = setTimeout(() => this.fetchVideos(), 300);
    },
    onCategoryChange() {
        if (!this.searchTerm.trim() && !this.selectedDharma) {
            this.videos = [];
            return;
        }
        this.fetchVideos();
    },
    init() {
        window.dispatchEvent(new CustomEvent('app-data-ready'));

        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbarWrapper');
            this.navbarHidden = navbar?.classList.contains('-translate-y-full') ?? false;
        }, { passive: true });
    }
}" class="flex flex-col items-center justify-center mb-5 page-enter">
    
    <!-- Video Data -->
    <script id="videos-data" type="application/json">
        <?= json_encode(array_values($videosWithThumbs), JSON_UNESCAPED_UNICODE) ?>
    </script>

    <!-- Sticky Filter Controls (below navbar, moves to top-0 when navbar hides) -->
    <div class="sticky z-20 px-1 py-1 w-full max-w-lg mx-auto" :class="navbarHidden ? 'top-0' : 'top-[60px]'">
        <div class="flex flex-row items-center gap-2">
            <div class="w-[70%]">
                <div class="relative">
                    <input type="search"
                           x-model="searchTerm"
                           x-on:input="onSearchInput"
                           placeholder="ຄົ້ນຫາວິດີໂອ..."
                           class="w-full bg-white/90 backdrop-blur-md border-none rounded-lg py-2.5 pl-9 pr-3 text-sm shadow-lg focus:ring-2 focus:ring-brown-500 outline-none transition-all Lao-font">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="w-[30%]">
                <select x-model="selectedDharma"
                        x-on:change="onCategoryChange"
                        class="w-full bg-white/90 backdrop-blur-md border-none rounded-lg py-2.5 px-3 text-sm shadow-lg focus:ring-2 focus:ring-brown-500 outline-none transition-all Lao-font cursor-pointer appearance-none">
                    <option value="">ໝວດທັມ</option>
                    <?php foreach ($dharmaCategories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Spacer for sticky filters -->
    <div class="h-2"></div>

    <!-- Server-rendered Videos Grid (shown by default, hidden when search/filter active) -->
    <div x-show="searchTerm === '' && selectedDharma === ''" class="grid gap-3 sm:gap-4 grid-cols-2 sm:grid-cols-3 md:grid-cols-4 mb-20 w-full max-w-5xl px-2">
        <?php foreach ($videosWithThumbs as $video): ?>
            <div class="flex flex-col items-center" style="margin-bottom: -2rem;">
                <a href="<?= url('/video/view/' . htmlspecialchars($video['_ytId'])) ?>"
                   class="z-10 flex flex-col justify-between items-center cursor-pointer group w-full">
                    <div class="mt-6 text-center px-1 w-full">
                        <p class="text-xs sm:text-sm font-bold text-[#DDCFBC] truncate Lao-font drop-shadow-sm group-hover:text-[#EEDDB6] transition-colors"><?= htmlspecialchars($video['ຊື່ພຣະສູດ'] ?? '') ?></p>
                    </div>
                    <div class="mt-1 w-full aspect-video flex-shrink-0 shadow-xl transition-transform duration-300 transform group-hover:-translate-y-2 overflow-hidden relative"
                         style="border-radius: 0; background: transparent; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2), 0 4px 6px -2px rgba(0,0,0,0.1), inset 0 -3px 6px rgba(0,0,0,0.2);">
                        <img src="<?= htmlspecialchars($video['_thumbnail'] ?? url('assets/images/logo.png')) ?>"
                             alt="<?= htmlspecialchars($video['ຊື່ພຣະສູດ'] ?? '') ?>"
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

    <!-- Alpine-filtered Videos Grid (hidden by default, shown when search/filter active) -->
    <div x-show="searchTerm !== '' || selectedDharma !== ''" style="display: none;" class="grid gap-3 sm:gap-4 grid-cols-2 sm:grid-cols-3 md:grid-cols-4 mb-20 w-full max-w-5xl px-2">
        <template x-for="video in videos" :key="video.ID">
            <div class="flex flex-col items-center" style="margin-bottom: -2rem;">
                <a :href="'<?= url('/video/view/') ?>' + video._ytId"
                   class="z-10 flex flex-col justify-between items-center cursor-pointer group w-full">
                    <div class="mt-6 text-center px-1 w-full">
                        <p class="text-xs sm:text-sm font-bold text-[#DDCFBC] truncate Lao-font drop-shadow-sm group-hover:text-[#EEDDB6] transition-colors" x-html="highlight(video['ຊື່ພຣະສູດ'])"></p>
                    </div>
                    <div class="mt-1 w-full aspect-video flex-shrink-0 shadow-xl transition-transform duration-300 transform group-hover:-translate-y-2 overflow-hidden relative"
                         style="border-radius: 0; background: transparent; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2), 0 4px 6px -2px rgba(0,0,0,0.1), inset 0 -3px 6px rgba(0,0,0,0.2);">
                        <img :src="video._thumbnail || '<?= url('assets/images/logo.png') ?>'"
                             :alt="video['ຊື່ພຣະສູດ']"
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
        </template>
    </div> 

    <!-- Empty State (only shows when search/filter is active and no results) -->
    <div x-show="videos.length === 0 && (searchTerm !== '' || selectedDharma !== '')" style="display: none;" class="text-center py-16 px-4">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-white/10 backdrop-blur-sm mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-[#DDCFBC]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
            </svg>
        </div>
        <p class="text-[#DDCFBC]/80 text-xl font-bold Lao-font">ບໍ່ພົບວິດີໂອທີ່ກົງກັບການຄົ້ນຫາ</p>
    </div>
</section>

       