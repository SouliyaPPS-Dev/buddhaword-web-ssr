<section x-data="{ 
    searchTerm: '',
    allSutras: [],
    searchResults: [],
    isLoading: true,
    isSearching: false,
    navbarHidden: false,
    searchController: null,
    init() {
        const cached = localStorage.getItem('buddhaword_sutras');
        if (cached) {
            try {
                this.allSutras = JSON.parse(cached);
                if (!Array.isArray(this.allSutras)) this.allSutras = Object.values(this.allSutras);
            } catch (e) {
                console.error('Failed to parse cached sutras', e);
            }
        }
        this.isLoading = false;
        window.dispatchEvent(new CustomEvent('app-data-ready'));

        if (!cached) {
            fetch('<?= url('/api/sync-sutras') ?>')
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(res => {
                    if (res.success && res.data) {
                        localStorage.setItem('buddhaword_sutras', JSON.stringify(res.data));
                        this.allSutras = res.data;
                        if (!Array.isArray(this.allSutras)) this.allSutras = Object.values(this.allSutras);
                    }
                })
                .catch(() => {});
        }

        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbarWrapper');
            this.navbarHidden = navbar?.classList.contains('-translate-y-full') ?? false;
        }, { passive: true });

        window.addEventListener('sync-complete', () => {
            const freshData = localStorage.getItem('buddhaword_sutras');
            if (freshData) {
                this.allSutras = JSON.parse(freshData);
                if (!Array.isArray(this.allSutras)) this.allSutras = Object.values(this.allSutras);
                window.dispatchEvent(new CustomEvent('app-data-ready'));
            }
        });
    },
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
    async performSearch() {
        if (this.searchTerm.trim().length < 2) {
            this.searchResults = [];
            return;
        }
        if (this.searchController) this.searchController.abort();
        const ac = new AbortController();
        this.searchController = ac;
        this.isSearching = true;
        try {
            const response = await fetch('<?= url('/api/search') ?>?q=' + encodeURIComponent(this.searchTerm), { signal: ac.signal });
            if (!ac.signal.aborted) {
                this.searchResults = await response.json();
            }
        } catch (e) {
            if (e.name !== 'AbortError') console.error('Search failed', e);
        } finally {
            if (!ac.signal.aborted) this.isSearching = false;
        }
    }
}" class="flex flex-col items-center justify-center mb-4 px-4 sm:px-6 lg:px-8 page-enter">
    
    <!-- Search Bar -->
    <div class="mt-3 sticky z-20 w-full max-w-lg mx-auto px-4 mb-4" :class="navbarHidden ? 'top-0' : 'top-[60px]'">
        <div class="relative">
            <input type="search" 
                   x-model="searchTerm"
                   @input.debounce.300ms="performSearch()"
                   placeholder="ຄົ້ນຫາທຸກຢ່າງ..." 
                   class="w-full bg-white/90 backdrop-blur-md border-none rounded-lg py-2.5 pl-9 pr-3 text-sm shadow-lg focus:ring-2 focus:ring-brown-500 outline-none transition-all Lao-font">
            <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Category Render (shown when search is empty) -->
    <div x-show="searchTerm.trim() === ''" class="grid gap-3 sm:gap-4 md:gap-5 lg:gap-6 grid-cols-3 lg:grid-cols-5 w-full max-w-5xl mb-20">
        <?php foreach ($categories as $category): ?>
            <?php 
                $categoryUrl = url('/sutra/' . urlencode($category));
                $imageUrl = url('images/sutra/' . $category . '.jpg');
            ?>
            <a href="<?= $categoryUrl ?>" class="flex justify-center items-center cursor-pointer">
                <div class="w-full max-w-[280px] h-auto rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 hover:-translate-y-1 hover:scale-[1.02]">
                    <img src="<?= $imageUrl ?>" 
                         alt="<?= htmlspecialchars($category) ?>" 
                         loading="lazy"
                         width="280" height="280"
                         class="w-full h-full object-contain bg-white/5 transition-all duration-300" 
                         onerror="this.src='<?= url('assets/images/logo.png') ?>'; this.className='w-full h-full object-contain p-4 sm:p-8 bg-gray-50 opacity-50'">
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Search Results Render -->
    <div x-show="searchTerm.trim() !== ''" style="display: none;" class="flex flex-col gap-3 w-full max-w-2xl mb-20">
        <div x-show="isSearching" class="flex justify-center p-6 sm:p-8">
            <div class="loader"></div>
        </div>
        <div x-show="!isSearching && searchTerm.length >= 2 && searchResults.length === 0" class="text-center py-10">
            <p class="text-white text-xl font-bold Lao-font">ບໍ່ພົບຂໍ້ມູນ</p>
        </div>
        <div x-show="!isSearching && searchTerm.length < 2" class="text-center py-10">
            <p class="text-white/70 text-lg Lao-font">ພິມຢ່າງໜ້ອຍ 2 ຕົວອັກສອນເພື່ອຄົ້ນຫາ...</p>
        </div>
        <template x-for="result in searchResults" :key="result.url + result.title">
            <a :href="result.url" 
               class="bg-white p-4 rounded-2xl shadow-md flex flex-col gap-1 transition-all hover:shadow-lg active:scale-[0.98]">
                <div class="flex items-start gap-2">
                    <span class="px-1.5 py-0.5 rounded text-[9px] sm:text-[10px] font-bold uppercase tracking-wider flex-shrink-0"
                          :class="{
                              'bg-blue-100 text-blue-700': result.type === 'sutra',
                              'bg-green-100 text-green-700': result.type === 'book',
                              'bg-red-100 text-red-700': result.type === 'video',
                              'bg-purple-100 text-purple-700': result.type === 'calendar',
                              'bg-amber-100 text-amber-700': result.type === 'book-page'
                          }"
                          x-html="highlight(result.category)"></span>
                    <h3 class="text-lg font-bold text-gray-800 Lao-font" x-html="highlight(result.title)"></h3>
                </div>
                <p class="text-sm text-gray-500 line-clamp-2 Lao-font" x-html="highlight(result.detail)"></p>
            </a>
        </template>
    </div>

</section>


  
             