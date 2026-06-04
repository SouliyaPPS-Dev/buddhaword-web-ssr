<section class="flex flex-col items-center justify-center mb-4 p-2 sm:p-4" x-data="{ favorites: JSON.parse(localStorage.getItem('buddhaword_favorites') || '[]'), init() { window.addEventListener('sync-complete', () => { const sutras = JSON.parse(localStorage.getItem('buddhaword_sutras') || '[]'); const favs = JSON.parse(localStorage.getItem('buddhaword_favorites') || '[]'); this.favorites = favs.map(fav => { const updated = sutras.find(s => s.ID === fav.ID); return updated || fav; }); }); } }">
    <h1 class="text-xl sm:text-2xl font-bold text-[#795548] mb-6 bg-white/80 px-4 py-2 rounded-xl shadow-sm Lao-font">ລາຍການທີ່ຖືກໃຈ</h1>

    <div class="flex flex-col gap-3 sm:gap-4 w-full max-w-4xl">
        <template x-if="favorites.length === 0">
            <div class="text-center py-16 sm:py-20 bg-white/50 backdrop-blur-sm rounded-2xl sm:rounded-3xl border border-white/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 sm:h-16 sm:w-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
                <p class="text-gray-500 text-base sm:text-lg Lao-font">ຍັງບໍ່ມີລາຍການທີ່ຖືກໃຈ</p>
                <a href="<?= url('/sutra') ?>" class="mt-4 inline-block px-6 py-2 bg-[#795548] text-white rounded-xl font-bold Lao-font transition-transform hover:scale-105 active:scale-95">ໄປເບິ່ງພຣະສູດ</a>
            </div>
        </template>

        <template x-for="item in favorites" :key="item.ID">
            <div class="bg-white rounded-xl sm:rounded-2xl shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <div class="p-3 sm:p-4 flex justify-between items-center gap-3 sm:gap-4">
                    <template x-if="item['ຊື່ພຣະສູດ']">
                        <a :href="'<?= url('/sutra/details/') ?>' + item.ID" class="flex-1 min-w-0">
                            <h3 class="text-base sm:text-lg font-bold text-gray-800 leading-tight Lao-font truncate sm:whitespace-normal" x-text="item['ຊື່ພຣະສູດ']"></h3>
                            <p class="text-[10px] sm:text-sm text-gray-500 mt-1 Lao-font" x-text="item['ໝວດທັມ']"></p>
                        </a>
                    </template>
                    <template x-if="!item['ຊື່ພຣະສູດ']">
                        <a :href="item.url" class="flex-1 min-w-0">
                            <h3 class="text-base sm:text-lg font-bold text-gray-800 leading-tight Lao-font truncate sm:whitespace-normal" x-text="item.title"></h3>
                            <p class="text-[10px] sm:text-sm text-gray-500 mt-1 Lao-font">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-xs">ປຶ້ມ</span>
                            </p>
                        </a>
                    </template>
                    
                    <div class="flex items-center gap-1 sm:gap-2 flex-shrink-0">
                        <button @click="favorites = favorites.filter(f => f.ID !== item.ID); localStorage.setItem('buddhaword_favorites', JSON.stringify(favorites))" class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.657 0L10 6.343l1.172-1.171a4 4 0 115.657 5.657L10 18.343l-8.686-8.686a4 4 0 010-5.657z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</section>
