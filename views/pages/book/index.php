<section x-data="{
    searchTerm: '',
    selectedDharma: '',
    navbarHidden: false,
    books: [],
    get filteredBooks() {
        let result = this.books;
        if (this.searchTerm.trim()) {
            const term = this.searchTerm.toLowerCase();
            result = result.filter(b =>
                (b['ຊື່'] || '').toLowerCase().includes(term) ||
                (b['ໝວດທັມ'] || '').toLowerCase().includes(term)
            );
        }
        if (this.selectedDharma) {
            result = result.filter(b => b['ໝວດຟາຍ'] === this.selectedDharma);
        }
        return result;
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
    init() {
        const cached = localStorage.getItem('buddhaword_books');
        if (cached) {
            try { this.books = JSON.parse(cached); } catch (e) {}
        }
        if (!this.books || this.books.length === 0) {
            const dataEl = document.getElementById('books-data');
            if (dataEl) {
                try { this.books = JSON.parse(dataEl.textContent); } catch (e) { console.error('Failed to parse books data', e); }
            }
        }
        window.dispatchEvent(new CustomEvent('app-data-ready'));

        window.addEventListener('sync-complete', () => {
            const fresh = localStorage.getItem('buddhaword_books');
            if (fresh) { try { this.books = JSON.parse(fresh); } catch (e) {} }
        });

        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbarWrapper');
            this.navbarHidden = navbar?.classList.contains('-translate-y-full') ?? false;
        }, { passive: true });
    }
}" class="flex flex-col items-center justify-center mb-5 page-enter">
    
    <!-- Books Data -->
    <script id="books-data" type="application/json">
        <?= json_encode(array_values($books), JSON_UNESCAPED_UNICODE) ?>
    </script>
    <!-- Sticky Filter Controls (below navbar, moves to top-0 when navbar hides) -->
    <div class="sticky z-20 px-4 py-2 w-full max-w-lg mx-auto" :class="navbarHidden ? 'top-0' : 'top-[60px]'">
        <div class="flex flex-row items-center gap-2">
            <div class="w-[70%]">
                <div class="relative">
                    <input type="search"
                           x-model="searchTerm"
                           placeholder="ຄົ້ນຫາ..."
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
                        class="w-full bg-white/90 backdrop-blur-md border-none rounded-lg py-2.5 px-3 text-sm shadow-lg focus:ring-2 focus:ring-brown-500 outline-none transition-all Lao-font cursor-pointer appearance-none">
                    <option value="">ທຸກໝວດ</option>
                    <?php foreach ($dharmaCategories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Spacer for sticky filters -->
    <div class="h-2"></div>

    <!-- Server-rendered Books Grid (shown by default, hidden when search/filter active) -->
    <div x-show="searchTerm === '' && selectedDharma === ''" class="grid gap-4 grid-cols-3 sm:grid-cols-5 md:grid-cols-5 lg:grid-cols-5 mb-20 w-full max-w-5xl px-2">
        <?php foreach ($books as $book): ?>
            <div class="flex flex-col items-center" style="margin-bottom: -2rem;">
                <a href="<?= url('/book/view/' . htmlspecialchars($book['ID'])) ?>"
                   class="z-10 flex flex-col justify-between items-center cursor-pointer group">
                    <div class="w-[115px] sm:w-[130px] md:w-[140px] lg:w-[185px] h-[205px] sm:h-[205px] md:h-[255px] lg:h-[305px] flex-shrink-0 mx-2 shadow-xl transition-transform duration-300 transform group-hover:-translate-y-2 overflow-hidden flex flex-col relative"
                         style="border-radius: 0; background: transparent; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2), 0 4px 6px -2px rgba(0,0,0,0.1), inset 0 -3px 6px rgba(0,0,0,0.2);">
                        <div class="flex-grow" style="margin-top: 1rem;">
                            <p class="text-white text-center text-xs sm:text-sm Lao-font leading-tight px-1 truncate"><?= htmlspecialchars($book['ຊື່'] ?? '') ?></p>
                        </div>
                        <img src="<?= htmlspecialchars($book['imageURL'] ?? url('assets/images/logo.png')) ?>"
                             alt="<?= htmlspecialchars($book['ຊື່'] ?? '') ?>"
                             loading="lazy"
                             class="z-0 object-fill transition-opacity duration-300 mt-1 h-[165px] sm:h-[200px] md:h-[220px] lg:h-[270px] w-full"
                             style="border-radius: 0;"
                             onerror="this.src='<?= url('assets/images/logo.png') ?>'; this.style.objectFit='contain'; this.style.padding='8px'; this.style.backgroundColor='#6B553A'; this.style.opacity='0.6'">
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

    <!-- Alpine-filtered Books Grid (hidden by default, shown when search/filter active) -->
    <div x-show="searchTerm !== '' || selectedDharma !== ''" style="display: none;" class="grid gap-4 grid-cols-3 sm:grid-cols-5 md:grid-cols-5 lg:grid-cols-5 mb-20 w-full max-w-5xl px-2">
        <template x-for="book in filteredBooks" :key="book.ID">
            <div class="flex flex-col items-center" style="margin-bottom: -2rem;">
                <a :href="'<?= url('/book/view/') ?>' + book.ID"
                   class="z-10 flex flex-col justify-between items-center cursor-pointer group">

                    <!-- Card -->
                    <div class="w-[115px] sm:w-[130px] md:w-[140px] lg:w-[185px] h-[205px] sm:h-[205px] md:h-[255px] lg:h-[305px] flex-shrink-0 mx-2 shadow-xl transition-transform duration-300 transform group-hover:-translate-y-2 overflow-hidden flex flex-col relative"
                         style="border-radius: 0; background: transparent; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2), 0 4px 6px -2px rgba(0,0,0,0.1), inset 0 -3px 6px rgba(0,0,0,0.2);">

                        <!-- Book Title -->
                        <div class="flex-grow" style="margin-top: 1rem;">
                            <p class="text-white text-center text-xs sm:text-sm Lao-font leading-tight px-1 truncate" x-html="highlight(book['ຊື່'])"></p>
                        </div>

                        <!-- Cover Image -->
                        <img :src="book.imageURL || '<?= url('assets/images/logo.png') ?>'"
                             :alt="book['ຊື່']"
                             loading="lazy"
                             class="z-0 object-fill transition-opacity duration-300 mt-1 h-[165px] sm:h-[200px] md:h-[220px] lg:h-[270px] w-full"
                             style="border-radius: 0;"
                             onerror="this.src='<?= url('assets/images/logo.png') ?>'; this.style.objectFit='contain'; this.style.padding='8px'; this.style.backgroundColor='#6B553A'; this.style.opacity='0.6'">
                    </div>

                    <!-- Bottom Shadow on Shelf -->
                    <div class="w-[90%] h-4 mt-[-1.5rem] bg-gray-900 opacity-25 blur-md"></div>
                </a>

                <!-- 3D Wooden Shelf Divider -->
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
    <div x-show="filteredBooks.length === 0 && (searchTerm !== '' || selectedDharma !== '')" style="display: none;" class="text-center py-16 px-4">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-white/10 backdrop-blur-sm mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-[#DDCFBC]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        </div>
        <p class="text-[#DDCFBC]/80 text-xl font-bold Lao-font">ບໍ່ພົບປື້ມທີ່ກົງກັບການຄົ້ນຫາ</p>
    </div>
</section>

       